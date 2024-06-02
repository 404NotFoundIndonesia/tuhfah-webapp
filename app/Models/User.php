<?php

namespace App\Models;

use App\Enum\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

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

    protected static function boot(): void
    {
        parent::boot();
        static::deleting(function (User $user) {
            if ($user->image) {
                Storage::delete("public/$user->image");
            }
        });
    }

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
            get: function () {
                if ($this->image) {
                    return asset('storage/'.$this->image);
                }

                return asset('404_Black.jpg');
            }
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
