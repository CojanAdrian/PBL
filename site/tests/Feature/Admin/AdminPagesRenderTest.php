<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Filament\Facades\Filament;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Smoke test: every resource's list and create pages render for an admin.
 */
class AdminPagesRenderTest extends TestCase
{
    public static function resources(): array
    {
        return collect(glob(__DIR__.'/../../../app/Filament/Resources/*/*Resource.php'))
            ->mapWithKeys(function (string $path) {
                $folder = basename(dirname($path));
                $class = 'App\\Filament\\Resources\\'.$folder.'\\'.basename($path, '.php');

                return [$folder => [$class]];
            })
            ->all();
    }

    public function test_dashboard_renders(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(Filament::getPanel('admin')->getUrl())
            ->assertOk()
            ->assertSee('CA.web');
    }

    #[DataProvider('resources')]
    public function test_resource_pages_render(string $resource): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->get($resource::getUrl('index'))->assertOk();

        if ($resource::hasPage('create')) {
            // Some sections can't be added to (pages, languages): their create page is forbidden.
            $this->get($resource::getUrl('create'))->assertStatus($resource::canCreate() ? 200 : 403);
        }
    }
}
