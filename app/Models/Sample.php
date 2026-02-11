<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sample extends Model
{
    use SoftDeletes;

    protected $table = 'samples';

    protected $casts = [
        'company_id' => 'int',
        'user_id' => 'int',
        'sent_at' => 'datetime',
        'received_at' => 'datetime',
        'analyzed_at' => 'datetime',
        'sample_taken_at' => 'datetime',
        'results_at' => 'datetime',
    ];

    protected $fillable = [
        'external_id',
        'internal_id',
        'type',
        'description',
        'shipping_type',
        'category',
        'status',
        'sent_at',
        'received_at',
        'analyzed_at',
        'company_id',
        'user_id',
        'document',
        'reception_id',
        'sample_taken_at',
        'results_at',
        'deleted_at',
    ];

    /**
     * Relación con Company
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relación con User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con Documents
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Relación con CharacteristicSample
     */
    public function characteristic(): HasOne
    {
        return $this->hasOne(CharacteristicSample::class);
    }

    /**
     * Scope para filtrar por tipo
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope para filtrar por estado
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para búsqueda global
     */
    public function scopeSearch($query, ?string $search)
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('external_id', 'like', "%{$search}%")
                ->orWhere('internal_id', 'like', "%{$search}%");
        });
    }
}
