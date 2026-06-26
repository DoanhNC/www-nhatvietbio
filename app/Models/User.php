<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'name',
        'email',
        'password',
        'is_root',
        'status',
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
            'is_root' => 'boolean',
            'status' => 'boolean',
        ];
    }

    /**
     * Get the groups that the user belongs to.
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'e_group_user', 'user_id', 'group_id');
    }

    /**
     * Check if user is root admin.
     */
    public function isRoot(): bool
    {
        return (bool) $this->is_root;
    }

    /**
     * Check if user has a specific permission through their groups.
     */
    public function hasPermission(string $permission): bool
    {
        // Root has all permissions
        if ($this->isRoot()) {
            return true;
        }

        // Check through groups
        foreach ($this->groups as $group) {
            if ($group->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all permissions from all groups.
     */
    public function getAllPermissions(): array
    {
        if ($this->isRoot()) {
            return array_keys(config('permissions', []));
        }

        $permissions = [];
        foreach ($this->groups as $group) {
            $permissions = array_merge($permissions, $group->permissions ?? []);
        }

        return array_unique($permissions);
    }
}
