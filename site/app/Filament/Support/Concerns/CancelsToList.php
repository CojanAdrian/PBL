<?php

namespace App\Filament\Support\Concerns;

use Filament\Actions\Action;

/**
 * For CreateRecord / EditRecord pages: "Cancel" opens the resource's list page.
 *
 * Filament's default goes back in browser history, which in SPA mode shows a
 * cached copy of the list without records saved via "Create & create another".
 */
trait CancelsToList
{
    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->alpineClickHandler(null)
            ->url($this->getResourceUrl());
    }
}
