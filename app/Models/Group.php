<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Group extends Model
{
    protected $table = 'e_groups';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    /**
     * Boot method to auto-generate slug.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($group) {
            if (empty($group->slug)) {
                $group->slug = Str::slug($group->name);
            }
        });

        static::updating(function ($group) {
            if ($group->isDirty('name') && !$group->isDirty('slug')) {
                $group->slug = Str::slug($group->name);
            }
        });
    }

    /**
     * Get the users that belong to this group.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'e_group_user', 'group_id', 'user_id');
    }

    /**
     * Check if group has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Add a permission to the group.
     */
    public function addPermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->permissions = $permissions;
        }
    }

    /**
     * Remove a permission from the group.
     */
    public function removePermission(string $permission): void
    {
        $permissions = $this->permissions ?? [];
        $permissions = array_diff($permissions, [$permission]);
        $this->permissions = array_values($permissions);
    }
}
