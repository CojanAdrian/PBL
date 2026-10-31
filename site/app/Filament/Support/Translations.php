<?php

namespace App\Filament\Support;

use App\Models\Lang;
use Closure;
use Filament\Forms\Components\Field;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

/**
 * Bridges the `*_trans` tables (one row per language) and Filament forms.
 *
 * Form state shape:
 *   trans   => [lang => [field => value]]          (record's `text()` rows)
 *   meta    => [img => ?, trans => [lang => [...]]] (record's `meta()` + its `text()` rows)
 *   gallery => [path, path, ...]                    (record's `gallery()` rows, in order)
 */
class Translations
{
    public const STATE_KEYS = ['trans', 'meta', 'gallery'];

    /**
     * One tab per active language. `$fields` receives the `required` rule to apply:
     * `true` in every language tab (when `$requireAll`), otherwise a closure that
     * only requires a field once any field in that language tab has been filled in.
     *
     * @param  Closure(bool|Closure): array<\Filament\Schemas\Components\Component>  $fields
     */
    public static function tabs(Closure $fields, string $statePath = 'trans', bool $requireAll = true): Group
    {
        $tabs = static::languages()->map(function (Lang $lang) use ($fields, $requireAll, $statePath) {
            $names = [];
            $required = $requireAll
                ? true
                : function (Get $get) use (&$names): bool {
                    foreach ($names as $name) {
                        if (static::isFilled($get($name))) {
                            return true;
                        }
                    }

                    return false;
                };

            $components = $fields($required);
            $names = collect($components)
                ->filter(fn ($component) => $component instanceof Field)
                ->map(fn (Field $field) => $field->getName())
                ->all();

            return Tab::make($lang->name)
                ->id("{$lang->code}-{$lang->id}")
                ->badge(fn (Component $livewire): ?int => static::errorCount($livewire, "data.{$statePath}.{$lang->code}.") ?: null)
                ->badgeColor('danger')
                ->schema([Group::make($components)->statePath($lang->code)]);
        });

        return Group::make([
            Tabs::make('Translations')->tabs($tabs->all())->persistTabInQueryString(),
        ])->statePath($statePath)->columnSpanFull();
    }

    /**
     * Tells the user which language tabs still have empty required fields,
     * since errors in a tab that isn't open are easy to miss.
     */
    public static function notifyMissingLanguages(ValidationException $exception): void
    {
        $codes = collect($exception->errors())
            ->keys()
            ->map(fn (string $key) => preg_match('~^data\.(?:meta\.)?trans\.([^.]+)\.~', $key, $match) ? $match[1] : null)
            ->filter()
            ->unique();

        if ($codes->isEmpty()) {
            return;
        }

        Notification::make()
            ->danger()
            ->title(__('Not saved yet'))
            ->body(__('Fill in every language before saving. Missing: :languages.', [
                'languages' => static::languages()->whereIn('code', $codes)->pluck('name')->join(', '),
            ]))
            ->send();
    }

    protected static function errorCount(Component $livewire, string $prefix): int
    {
        return collect($livewire->getErrorBag()->keys())
            ->filter(fn (string $key) => str_starts_with($key, $prefix))
            ->count();
    }

    /**
     * A table column showing a translated attribute in the current admin locale, searchable across languages.
     */
    public static function column(string $attribute, ?string $label = null): TextColumn
    {
        return TextColumn::make("translated_{$attribute}")
            ->label($label ?? str($attribute)->headline())
            ->state(fn (Model $record) => $record->translation()?->{$attribute})
            ->placeholder('—')
            ->searchable(query: fn (Builder $query, string $search) => $query->whereHas(
                'text',
                fn (Builder $query) => $query->where($attribute, 'like', "%{$search}%"),
            ));
    }

    /**
     * Like filled(), but an empty rich-editor document or tag-only HTML counts as empty.
     */
    public static function isFilled(mixed $value): bool
    {
        if (is_array($value)) {
            if (isset($value['type'])) {
                return filled($value['text'] ?? null)
                    || in_array($value['type'], ['image', 'horizontalRule'], true)
                    || static::isFilled($value['content'] ?? []);
            }

            return collect($value)->contains(fn ($item) => static::isFilled($item));
        }

        if (is_string($value) && str_contains($value, '<')) {
            return filled(trim(html_entity_decode(strip_tags($value, '<img><iframe><video>'))));
        }

        return filled($value);
    }

    /** @return Collection<int, Lang> */
    public static function languages(): Collection
    {
        return once(fn () => Lang::where('active', true)
            ->orderByRaw('code = ? desc', [config('app.locale')])
            ->orderBy('id')
            ->get());
    }

    /**
     * Strip translation/meta/gallery state, leaving the record's own attributes.
     */
    public static function baseAttributes(array $data): array
    {
        return Arr::except($data, self::STATE_KEYS);
    }

    /**
     * Form state for a record's translations, meta and gallery.
     */
    public static function hydrate(Model $record): array
    {
        $data = [];

        if (method_exists($record, 'text')) {
            $data['trans'] = static::hydrateRows($record->text());
        }

        if (method_exists($record, 'meta')) {
            $meta = $record->meta;
            $data['meta'] = [
                'img' => $meta?->img,
                'trans' => $meta ? static::hydrateRows($meta->text()) : [],
            ];
        }

        if (method_exists($record, 'gallery')) {
            $data['gallery'] = $record->gallery()->pluck('img')->all();
        }

        return $data;
    }

    /**
     * Save translations, meta and gallery from form state onto a persisted record.
     */
    public static function persist(Model $record, array $data, ?string $disk = null): void
    {
        if (array_key_exists('trans', $data)) {
            static::persistRows($record, 'text', $data['trans'] ?? []);
        }

        if (array_key_exists('meta', $data)) {
            static::persistMeta($record, $data['meta'] ?? []);
        }

        if (array_key_exists('gallery', $data)) {
            static::persistGallery($record, array_values($data['gallery'] ?? []), $disk);
        }
    }

    protected static function hydrateRows(HasMany $relation): array
    {
        $fields = static::translatableFields($relation);

        return $relation->get()
            ->mapWithKeys(fn (Model $row) => [$row->lang => $row->only($fields)])
            ->all();
    }

    /**
     * Upsert one row per language; a language whose fields are all empty is removed.
     */
    protected static function persistRows(Model $owner, string $relation, array $rows): void
    {
        $fields = static::translatableFields($owner->{$relation}());

        foreach ($rows as $lang => $values) {
            $values = Arr::only($values ?? [], $fields);

            if (! static::isFilled($values)) {
                $owner->{$relation}()->where('lang', $lang)->delete();

                continue;
            }

            // A fresh relation per call: updateOrCreate() adds constraints to the query it is given.
            $owner->{$relation}()->updateOrCreate(['lang' => $lang], $values);
        }
    }

    protected static function persistMeta(Model $record, array $meta): void
    {
        $trans = array_filter($meta['trans'] ?? [], fn ($values) => static::isFilled($values));
        $existing = $record->meta;

        if (! $existing && blank($meta['img'] ?? null) && empty($trans)) {
            return;
        }

        $model = $existing ?? $record->meta()->make();
        // Some meta tables require an image; fall back to the record's own.
        $model->img = ($meta['img'] ?? null) ?: ($model->img ?: ($record->img ?? ''));
        $model->save();

        static::persistRows($model, 'text', $meta['trans'] ?? []);
    }

    protected static function persistGallery(Model $record, array $paths, ?string $disk): void
    {
        foreach ($record->gallery()->get() as $item) {
            if (! in_array($item->img, $paths, true)) {
                if ($disk) {
                    Storage::disk($disk)->delete($item->img);
                }
                $item->delete();
            }
        }

        foreach ($paths as $index => $path) {
            $record->gallery()->updateOrCreate(['img' => $path], ['order_column' => $index + 1]);
        }
    }

    /**
     * Fillable columns of a translation row, minus keys managed here or by the model.
     */
    protected static function translatableFields(HasMany $relation): array
    {
        return array_values(array_diff(
            $relation->getRelated()->getFillable(),
            ['lang', 'slug', $relation->getForeignKeyName()],
        ));
    }
}
