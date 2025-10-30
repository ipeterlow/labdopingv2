<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Document
 *
 * @property int $id
 * @property string $document_archive
 * @property string|null $status
 * @property int $sample_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Sample $sample
 */
class Document extends Model
{
    protected $table = 'documents';

    protected $casts = [
        'sample_id' => 'int',
    ];

    protected $fillable = [
        'document_archive',
        'status',
        'sample_id',
    ];

    public function sample()
    {
        return $this->belongsTo(Sample::class);
    }
}
