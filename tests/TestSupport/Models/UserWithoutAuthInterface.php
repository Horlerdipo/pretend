<?php

namespace Horlerdipo\Pretend\Tests\TestSupport\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\Contracts\HasApiTokens;
use Laravel\Sanctum\HasApiTokens as HasApiTokensTrait;

class UserWithoutAuthInterface extends Model implements HasApiTokens
{
    use HasApiTokensTrait;

    protected $table = 'users';

    protected $guarded = [];
}
