<?php

namespace App\Models\Spatie\Permission\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $appends = ['created_at_readable'];

    public function getCreatedAtReadableAttribute(): ?string
    {
        return $this->created_at
            ? $this->created_at->timezone(config('app.timezone', 'America/Santiago'))->format('d-m-Y H:i')
            : null;
    }
}
