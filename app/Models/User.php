<?php

namespace App\Models;

use App\Enum\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
        'role',
        'image',
        'phone',
        'address',
        'marital_status',
        'gender',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'image_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function imageUrl(): Attribute
    {
        return new Attribute(
            get: fn () => 'https://ui-avatars.com/api/?name='.$this->name,
        );
    }

    public function isRole(Role $role): bool
    {
        return Role::tryFrom($this->role) === $role;
    }

    public function scopeRole(Builder $query, Role $role)
    {
        return $query->where('role', $role);
    }
}
