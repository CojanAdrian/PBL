<?php

namespace App\Filament\Widgets;

use App\Filament\Support\Shortcuts;
use App\Filament\Widgets\Concerns\HasPeriod;
use App\Models\Visit;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\Widget;

class WebsiteCard extends Widget implements HasActions, HasSchemas
{
    use HasPeriod;
    use InteractsWithActions;
    use InteractsWithSchemas {
        InteractsWithSchemas::__get insteadof HasPeriod;
    }

    protected static ?int $sort = 1;

    protected int|string|array $columnSpan = ['md' => 2, 'xl' => 4];

    protected string $view = 'filament.widgets.website-card';

    protected function getViewData(): array
    {
        return [
            'host' => parse_url(config('app.url'), PHP_URL_HOST) ?: config('app.url'),
            'url' => url('/'),
            'online' => Visit::since(now()->subMinutes(5))->distinct()->count('visitor'),
            'visitors' => Visit::since($this->periodStart())->distinct()->count('visitor'),
            'views' => Visit::since($this->periodStart())->count(),
            'days' => $this->days(),
            'shortcuts' => Shortcuts::resolve(auth()->user()),
        ];
    }

    public function editShortcutsAction(): Action
    {
        return Action::make('editShortcuts')
            ->label(__('Edit shortcuts'))
            ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
            ->modalHeading(__('Shortcuts'))
            ->modalDescription(__('Pick up to :max buttons for the things you do most. Drag to reorder.', ['max' => Shortcuts::MAX]))
            ->modalWidth(Width::ThreeExtraLarge)
            ->modalSubmitActionLabel(__('Save'))
            ->fillForm(fn (): array => ['shortcuts' => Shortcuts::stored(auth()->user())])
            ->schema([
                Repeater::make('shortcuts')
                    ->hiddenLabel()
                    ->schema([
                        TextInput::make('label')->required()->maxLength(16),
                        Select::make('icon')->options(Shortcuts::iconOptions())->allowHtml()->searchable()->required(),
                        Select::make('target')->label(__('Opens'))->options(Shortcuts::destinations())->searchable()->required()->live(),
                        TextInput::make('url')
                            ->label(__('Link'))
                            ->url()
                            ->required()
                            ->placeholder('https://')
                            ->visible(fn (Get $get): bool => $get('target') === 'custom')
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->defaultItems(0)
                    ->maxItems(Shortcuts::MAX)
                    ->reorderableWithDragAndDrop()
                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? null)
                    ->addActionLabel(__('Add shortcut')),
            ])
            ->extraModalFooterActions(fn (Action $action): array => [
                $action->makeModalSubmitAction('reset', arguments: ['reset' => true])
                    ->label(__('Reset to default'))
                    ->color('gray'),
            ])
            ->action(function (array $data, array $arguments): void {
                $shortcuts = ($arguments['reset'] ?? false) ? null : array_values($data['shortcuts'] ?? []);

                auth()->user()->forceFill(['shortcuts' => $shortcuts])->save();

                Notification::make()->success()->title(__('Shortcuts saved'))->send();
            });
    }
}
