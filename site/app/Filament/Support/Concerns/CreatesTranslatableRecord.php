<?php

namespace App\Filament\Support\Concerns;

use App\Filament\Support\Translations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * For CreateRecord pages whose form uses Translations::tabs() (and optional meta/gallery).
 */
trait CreatesTranslatableRecord
{
    use CancelsToList;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $record = static::getModel()::create(Translations::baseAttributes($data));

            Translations::persist($record, $data, static::getResource()::getDisk());

            return $record;
        });
    }

    protected function onValidationError(ValidationException $exception): void
    {
        Translations::notifyMissingLanguages($exception);
    }
}
