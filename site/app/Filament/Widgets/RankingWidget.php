<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasPeriod;
use Filament\Widgets\Widget;

/**
 * A card listing the top items of the period, each row backed by a bar
 * proportional to its share. Subclasses provide the rows.
 */
abstract class RankingWidget extends Widget
{
    use HasPeriod;

    protected int|string|array $columnSpan = ['md' => 2, 'xl' => 4];

    protected string $view = 'filament.widgets.ranking';

    protected int $limit = 6;

    abstract protected function heading(): string;

    abstract protected function description(): string;

    /**
     * @return iterable<array{label: string, sub?: ?string, value: int, flag?: ?string, icon?: ?string}>
     */
    abstract protected function rows(): iterable;

    protected function valueLabel(): string
    {
        return __('Visitors');
    }

    protected function getViewData(): array
    {
        $rows = collect($this->rows());
        $max = max(1, $rows->max('value') ?? 0);

        return [
            'heading' => $this->heading(),
            'description' => $this->description(),
            'valueLabel' => $this->valueLabel(),
            'rows' => $rows->map(fn (array $row) => $row + ['share' => round($row['value'] / $max * 100)]),
        ];
    }
}
