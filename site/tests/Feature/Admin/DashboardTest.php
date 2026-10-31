<?php

namespace Tests\Feature\Admin;

use App\Filament\Pages\Dashboard;
use App\Filament\Widgets\Devices;
use App\Filament\Widgets\LatestMessages;
use App\Filament\Widgets\Metrics;
use App\Filament\Widgets\RecentVisitors;
use App\Filament\Widgets\TopLocations;
use App\Filament\Widgets\TopPages;
use App\Filament\Widgets\TopSources;
use App\Filament\Widgets\TrafficChart;
use App\Filament\Widgets\WebsiteCard;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    public function test_dashboard_widgets_render_with_data(): void
    {
        Storage::fake('news');
        Storage::fake('service');
        $this->seed(DemoSeeder::class);
        $this->actingAs(User::factory()->admin()->create(['name' => 'Adrian Cojan']));

        $this->get('/admin')->assertOk()->assertSee('Overview')->assertSee('Welcome back, Adrian');

        Livewire::test(WebsiteCard::class)->assertSee('Your website')->assertSee('online')->assertSee('Invite');
        Livewire::test(Metrics::class)->assertSee('Visitors')->assertSee('Page views')->assertSee('Unread');
        Livewire::test(TrafficChart::class, ['pageFilters' => ['period' => '7']])->assertSee('last 7 days');
        Livewire::test(TopLocations::class)->assertSee('Chișinău')->assertSee('Moldova');
        Livewire::test(TopPages::class)->assertSee('/en');
        Livewire::test(TopSources::class)->assertSee('google.com');
        Livewire::test(Devices::class)->assertSee('Mobile')->assertSee('mostly');
        Livewire::test(RecentVisitors::class)->assertDontSee('Visits to your website will show up here.');
        Livewire::test(LatestMessages::class)->assertSee('View all');
    }

    public function test_widgets_render_without_any_visits(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Metrics::class)->assertSee('No visits yet');
        Livewire::test(TopLocations::class)->assertSee('No visits in this period yet.');
        Livewire::test(RecentVisitors::class)->assertSee('Visits to your website will show up here.');
    }

    public function test_period_switch_updates_filters(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(Dashboard::class)
            ->call('setPeriod', '7')
            ->assertSet('filters.period', '7');
    }

    public function test_editors_do_not_get_admin_shortcuts(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(WebsiteCard::class)->assertSee('Inbox')->assertDontSee('Invite');
    }
}
