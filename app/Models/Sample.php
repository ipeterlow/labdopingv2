<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sample extends Model
{
    use SoftDeletes;

    protected $table = 'samples';

    protected $casts = [
        'company_id' => 'int',
        'user_id' => 'int',
    ];

    protected $dates = [
        'sent_at',
        'received_at',
        'analyzed_at',
        'sample_taken_at',
        'results_at',
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
}
