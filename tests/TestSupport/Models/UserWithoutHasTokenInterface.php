<?php

namespace Horlerdipo\Pretend\Tests\TestSupport\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class UserWithoutHasTokenInterface extends Model implements Authenticatable
{
    use \Illuminate\Auth\Authenticatable;

    protected $table = 'users';

    protected $guarded = [];
}
