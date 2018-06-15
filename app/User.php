<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

/**
 * User model.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $picture
 * @property string $role
 * @property int $login_fail_count
 * @property string $remember_token
 * @property Carbon|string|null $created_at
 * @property Carbon|string|null $updated_at
 */
class User extends Authenticatable
{
    use Notifiable;

    const ROLE_STANDARD = 'standard';
    const ROLE_MANAGER  = 'manager';
    const ROLE_ADMIN    = 'admin';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'picture', 'role', 'login_fail_count'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = Hash::make($password);
    }
}
