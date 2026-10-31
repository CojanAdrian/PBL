<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\Inboxes\InboxResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

class PanelAccessTest extends TestCase
{
    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_inactive_users_are_forbidden(): void
    {
        $this->actingAs(User::factory()->admin()->inactive()->create())
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_non_admins_see_the_dashboard_and_inbox_only(): void
    {
        $editor = Role::create(['label' => 'Editor', 'role' => 'editor']);
        $this->actingAs(User::factory()->create(['role_id' => $editor->id]));

        $this->get('/admin')->assertOk();
        $this->get(InboxResource::getUrl())->assertOk();
        $this->get(UserResource::getUrl())->assertForbidden();
    }

    public function test_admins_can_manage_users(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(UserResource::getUrl())
            ->assertOk();
    }
}
