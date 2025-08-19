<?php

namespace Horlerdipo\Pretend\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $impersonator_type
 * @property string $impersonator_id
 * @property string $impersonated_type
 * @property string $impersonated_id
 * @property string $key
 * @property Carbon $expires_at
 * @property bool $used
 * @property string[] $abilities
 */
class Impersonation extends Model
{
    protected $fillable = [
        'impersonator_type',
        'impersonator_id',
        'impersonated_type',
        'impersonated_id',
        'key',
        'used',
        'expires_at',
        'abilities',
    ];

    protected function casts(): array
    {
        return [
            'used' => 'boolean',
            'expires_at' => 'datetime',
            'abilities' => 'array',
        ];
    }
}
