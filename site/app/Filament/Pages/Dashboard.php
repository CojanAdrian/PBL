<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFilters;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;

class Dashboard extends BaseDashboard
{
    use HasFilters;

    public function getColumns(): int|array
    {
        return ['md' => 2, 'xl' => 12];
    }

    public function getTitle(): string|Htmlable
    {
        return __('Overview');
    }

    public function getHeader(): ?View
    {
        return view('filament.pages.dashboard-header', [
            'periods' => ['7' => __('Week'), '30' => __('Month'), '90' => __('Quarter')],
            'period' => (string) ($this->filters['period'] ?? '30'),
        ]);
    }

    public function setPeriod(string $period): void
    {
        $this->filters = ['period' => $period];
        $this->updatedFilters();
    }
}
