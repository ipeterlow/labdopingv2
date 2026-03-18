<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = ['name', 'number', 'email'];

    /**
     * Relación con Samples
     */
    public function samples(): HasMany
    {
        return $this->hasMany(Sample::class);
    }

    /**
     * Relación con contactos de correo
     */
    public function emailContacts(): HasMany
    {
        return $this->hasMany(CompanyEmailContact::class);
    }
}
