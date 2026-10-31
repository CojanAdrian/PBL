<?php

namespace App\Filament\Support;

use Filament\Resources\Resource as FilamentResource;

abstract class Resource extends FilamentResource
{
    /** Restrict the resource to users with the admin role. */
    protected static bool $adminOnly = true;

    /** Storage disk for this resource's uploads (see config/filesystems.php). */
    protected static ?string $disk = null;

    public static function canAccess(): bool
    {
        return ! static::$adminOnly || (auth()->user()?->isAdmin() ?? false);
    }

    public static function getDisk(): ?string
    {
        return static::$disk;
    }
}
