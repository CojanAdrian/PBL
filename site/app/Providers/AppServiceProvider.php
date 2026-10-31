<?php

namespace App\Providers;

use App\Support\Cms\Cms;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(Cms::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Editable content for the public site, see App\Support\Cms\Cms.
        Blade::directive('text', fn (string $arguments) => "<?php echo app(\\App\\Support\\Cms\\Cms::class)->text({$arguments}); ?>");
        Blade::directive('image', fn (string $arguments) => "<?php echo app(\\App\\Support\\Cms\\Cms::class)->imageAttributes({$arguments}); ?>");
    }
}
