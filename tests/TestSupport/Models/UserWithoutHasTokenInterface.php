<?php

namespace Horlerdipo\Pretend\Tests\TestSupport\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\Contracts\HasApiTokens;
use Laravel\Sanctum\HasApiTokens as HasApiTokensTrait;

class UserWithoutHasTokenInterface extends Model implements Authenticatable{

    use \Illuminate\Auth\Authenticatable;

    protected $table = 'users';

    protected $guarded = [];
}
