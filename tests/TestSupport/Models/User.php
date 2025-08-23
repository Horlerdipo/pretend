<?php

namespace Horlerdipo\Pretend\Tests\TestSupport\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\Contracts\HasApiTokens;
use Laravel\Sanctum\HasApiTokens as HasApiTokensTrait;

class User extends Model implements Authenticatable, HasApiTokens
{
    use HasApiTokensTrait, \Illuminate\Auth\Authenticatable;

    protected $guarded = [];
}
