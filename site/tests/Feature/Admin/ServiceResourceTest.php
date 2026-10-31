<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Services\Pages\CreateService;
use App\Filament\Resources\Services\Pages\EditService;
use App\Filament\Resources\Services\ServiceResource;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceResourceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('service');
        $this->actingAs(User::factory()->admin()->create());
    }

    /** A fake image upload that doesn't need the GD extension. */
    protected function image(string $name): UploadedFile
    {
        return UploadedFile::fake()->create($name, 10, 'image/jpeg');
    }

    public function test_it_creates_a_service_with_meta_and_gallery(): void
    {
        Livewire::test(CreateService::class)
            ->fillForm([
                'img' => $this->image('main.jpg'),
                'gallery' => [$this->image('a.jpg'), $this->image('b.jpg')],
                'trans' => [
                    'en' => ['title' => 'Web design', 'sub_title' => 'Sites', 'text' => '<p>We build sites</p>'],
                    'ro' => ['title' => 'Web design', 'sub_title' => 'Site-uri', 'text' => '<p>Construim site-uri</p>'],
                ],
                'meta' => ['trans' => ['en' => ['title' => 'SEO title', 'text' => 'SEO description', 'keywords' => 'web']]],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $service = Service::with(['text', 'meta.text', 'gallery'])->sole();

        $this->assertSame('Web design', $service->translation('en')->title);
        $this->assertSame('web-design', $service->translation('en')->slug);
        $this->assertSame('SEO title', $service->meta->translation('en')->title);
        $this->assertSame($service->img, $service->meta->img, 'Meta image falls back to the main image');
        $this->assertSame([1, 2], $service->gallery->pluck('order_column')->all());
        Storage::disk('service')->assertExists($service->gallery->pluck('img')->all());
    }

    public function test_seo_is_optional(): void
    {
        Livewire::test(CreateService::class)
            ->fillForm([
                'img' => $this->image('main.jpg'),
                'trans' => ['en' => ['title' => 'Hosting'], 'ro' => ['title' => 'Găzduire']],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertNull(Service::sole()->meta);
    }

    public function test_list_and_edit_pages_render_existing_data_and_gallery_can_be_trimmed(): void
    {
        $service = Service::create(['img' => 'main.jpg', 'active' => true, 'order_column' => 1]);
        $service->text()->create(['lang' => 'en', 'title' => 'Consulting']);
        $service->text()->create(['lang' => 'ro', 'title' => 'Consultanță']);
        $service->gallery()->createMany([
            ['img' => 'one.jpg', 'order_column' => 1],
            ['img' => 'two.jpg', 'order_column' => 2],
        ]);
        Storage::disk('service')->put('main.jpg', 'x');
        Storage::disk('service')->put('one.jpg', 'x');
        Storage::disk('service')->put('two.jpg', 'x');

        $this->get(ServiceResource::getUrl())->assertOk()->assertSee('Consulting');
        $this->get(ServiceResource::getUrl('edit', ['record' => $service]))->assertOk();

        Livewire::test(EditService::class, ['record' => $service->getRouteKey()])
            ->assertSchemaStateSet(['trans.en.title' => 'Consulting'])
            ->fillForm(['gallery' => ['two.jpg']])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(['two.jpg'], $service->gallery()->pluck('img')->all());
        Storage::disk('service')->assertMissing('one.jpg');
    }
}
