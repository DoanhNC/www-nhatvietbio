<?php

namespace App\Http\Controllers\Rest;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GroupsRestController extends Controller
{
    /**
     * Get all groups with user count.
     */
    public function index(Request $request)
    {
        $query = Group::withCount('users');

        // Search by name
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $groups = $query->orderBy('name', 'asc')->get();

        return ApiResponse::success($groups);
    }

    /**
     * Get available permissions list.
     */
    public function permissions()
    {
        return ApiResponse::success(config('permissions', []));
    }

    /**
     * Create a new group.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        // Validate permissions against config
        $validPermissions = array_keys(config('permissions', []));
        $permissions = array_intersect($data['permissions'] ?? [], $validPermissions);

        $group = Group::create([
            'name' => $data['name'],
            'slug' => Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'permissions' => $permissions,
        ]);

        return ApiResponse::success($group->loadCount('users'), 201, 'Tạo nhóm thành công!');
    }

    /**
     * Get a single group with its users.
     */
    public function show($id)
    {
        $group = Group::with('users')->withCount('users')->findOrFail($id);

        return ApiResponse::success($group);
    }

    /**
     * Update a group.
     */
    public function update(Request $request, $id)
    {
        $group = Group::findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string'],
        ]);

        // Validate permissions against config
        $validPermissions = array_keys(config('permissions', []));
        $permissions = array_intersect($data['permissions'] ?? [], $validPermissions);

        $group->name = $data['name'];
        $group->slug = Str::slug($data['name']);
        $group->description = $data['description'] ?? null;
        $group->permissions = $permissions;
        $group->save();

        return ApiResponse::success($group->loadCount('users'), 200, 'Cập nhật nhóm thành công!');
    }

    /**
     * Delete a group.
     */
    public function destroy($id)
    {
        $group = Group::findOrFail($id);

        // Detach all users before deleting
        $group->users()->detach();
        $group->delete();

        return ApiResponse::success(null, 200, 'Xóa nhóm thành công!');
    }
}
