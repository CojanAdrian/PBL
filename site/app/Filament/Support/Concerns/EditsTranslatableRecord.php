<?php

namespace App\Filament\Support\Concerns;

use App\Filament\Support\Translations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * For EditRecord pages whose form uses Translations::tabs() (and optional meta/gallery).
 */
trait EditsTranslatableRecord
{
    use CancelsToList;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return [...$data, ...Translations::hydrate($this->getRecord())];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            $record->update(Translations::baseAttributes($data));

            Translations::persist($record, $data, static::getResource()::getDisk());

            return $record;
        });
    }

    protected function onValidationError(ValidationException $exception): void
    {
        Translations::notifyMissingLanguages($exception);
    }
}
