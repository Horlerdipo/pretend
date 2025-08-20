<?php

namespace Horlerdipo\Pretend\Models;

use Carbon\Unit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $impersonator_type
 * @property string $impersonator_id
 * @property string $impersonated_type
 * @property string $impersonated_id
 * @property string $token
 * @property int $expires_in
 * @property Unit $duration
 * @property bool $used
 * @property string[] $abilities
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class Impersonation extends Model
{
    protected $fillable = [
        'impersonator_type',
        'impersonator_id',
        'impersonated_type',
        'impersonated_id',
        'token',
        'used',
        'expires_in',
        'duration',
        'abilities',
    ];

    protected function casts(): array
    {
        return [
            'used' => 'boolean',
            'expires_at' => 'datetime',
            'abilities' => 'array',
            'duration' => Unit::class,
            'expires_in' => 'integer',
        ];
    }
}
