<?php

namespace App\Models;

use Illuminate\Support\Collection;

class UserWithTenantDefaults extends User
{
    protected $table = 'users';

    public ?Collection $tenantDefaultsOverride = null;

    public function getNotificationPreferenceTenantDefaults(): Collection
    {
        return $this->tenantDefaultsOverride ?? collect();
    }
}
