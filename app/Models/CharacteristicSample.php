<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class CharacteristicSample
 *
 * @property int $id_characteristic_samples
 * @property string|null $ph
 * @property string|null $densidad
 * @property string|null $volumen
 * @property string|null $largo
 * @property string|null $screening
 * @property string|null $confirmacion
 * @property string|null $color
 * @property string|null $observaciones
 * @property int|null $cantidad_droga
 * @property string|null $encargado_ingreso
 * @property Carbon|null $fecha_ingreso
 * @property int $sample_id
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property string|null $deleted_at
 * @property Sample $sample
 */
class CharacteristicSample extends Model
{
    use SoftDeletes;

    protected $table = 'characteristic_samples';

    protected $primaryKey = 'id_characteristic_samples';

    protected $casts = [
        'cantidad_droga' => 'int',
        'sample_id' => 'int',
    ];

    protected $dates = [
        'fecha_ingreso',
    ];

    protected $fillable = [
        'ph',
        'densidad',
        'volumen',
        'largo',
        'screening',
        'confirmacion',
        'color',
        'observaciones',
        'cantidad_droga',
        'encargado_ingreso',
        'fecha_ingreso',
        'sample_id',
        'result_gcms',
        'result_cobas'
    ];

    public function sample()
    {
        return $this->belongsTo(Sample::class);
    }
}
