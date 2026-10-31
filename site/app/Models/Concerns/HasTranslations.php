<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * For models whose translated fields live in a `text()` HasMany relation
 * of `*_trans` rows keyed by a `lang` code.
 */
trait HasTranslations
{
    public function translation(?string $locale = null): ?Model
    {
        $rows = $this->text;

        return $rows->firstWhere('lang', $locale ?? app()->getLocale())
            ?? $rows->firstWhere('lang', config('app.fallback_locale'))
            ?? $rows->first();
    }
}
