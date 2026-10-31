<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasPeriod;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class TrafficChart extends ChartWidget
{
    use HasPeriod;

    protected static ?int $sort = 3;

    protected ?string $heading = 'Traffic';

    protected ?string $maxHeight = '280px';

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = ['md' => 2, 'xl' => 8];

    public function getDescription(): ?string
    {
        return __('Visitors and page views, last :days days', ['days' => $this->days()]);
    }

    protected function getData(): array
    {
        $traffic = $this->trafficPerDay();

        return [
            'datasets' => [
                ['label' => __('Visitors'), 'data' => $traffic['visitors'], 'order' => 2],
                [
                    'type' => 'line',
                    'label' => __('Page views'),
                    'data' => $traffic['views'],
                    'order' => 1,
                    'borderColor' => '#f97316',
                    'backgroundColor' => '#f97316',
                    'borderWidth' => 2,
                    'tension' => 0.4,
                    'pointRadius' => 0,
                    'pointHoverRadius' => 4,
                ],
            ],
            'labels' => $this->dates()->map(fn (Carbon $date) => $date->format('M j'))->all(),
        ];
    }

    protected function getOptions(): RawJs
    {
        return RawJs::make(<<<'JS'
            {
                interaction: { mode: 'index', intersect: false },
                aspectRatio: window.innerWidth < 640 ? 1.1 : 2,
                datasets: {
                    bar: {
                        borderWidth: 0,
                        borderRadius: 999,
                        borderSkipped: false,
                        maxBarThickness: 14,
                        backgroundColor: (context) => {
                            const { ctx, chartArea } = context.chart
                            if (! chartArea) return 'rgba(59, 130, 246, 0.7)'
                            const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top)
                            gradient.addColorStop(0, 'rgba(125, 211, 252, 0.45)')
                            gradient.addColorStop(1, 'rgba(37, 99, 235, 0.95)')
                            return gradient
                        },
                    },
                },
                plugins: {
                    legend: { display: true, position: 'bottom', labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 6, boxHeight: 6, padding: 16 } },
                    tooltip: { padding: 10, cornerRadius: 12, usePointStyle: true },
                },
                scales: {
                    y: { beginAtZero: true, border: { display: false }, grid: { drawTicks: false }, ticks: { precision: 0, maxTicksLimit: 5, padding: 8 } },
                    x: { border: { display: false }, grid: { display: false }, ticks: { maxTicksLimit: 7, maxRotation: 0 } },
                },
            }
        JS);
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
