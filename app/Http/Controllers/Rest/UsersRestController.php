<?php

namespace App\Http\Controllers\Rest;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Group;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UsersRestController extends Controller
{
    /**
     * Get all users with their groups.
     */
    public function index(Request $request)
    {
        $query = User::with('groups');

        // Search by name, username, or email
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status (only when explicitly 0 or 1)
        if ($request->filled('status')) {
            $query->where('status', (bool) $request->status);
        }

        $users = $query->orderBy('id', 'asc')->get();

        return ApiResponse::success($users);
    }

    /**
     * Format group_ids to valid integer array.
     * Handles: strings, mixed arrays, etc.
     */
    private function formatGroupIds(Request $request): void
    {
        if (!$request->has('group_ids')) {
            return;
        }

        $groupIds = $request->group_ids;

        // If not array, try to make it one
        if (!is_array($groupIds)) {
            $groupIds = $groupIds ? [$groupIds] : [];
        }

        // Extract valid positive integers
        $formatted = [];
        foreach ($groupIds as $id) {
            // Handle numeric strings or integers
            if (is_numeric($id) && (int)$id > 0) {
                $formatted[] = (int)$id;
            }
        }

        $request->merge(['group_ids' => array_unique($formatted)]);
    }

    /**
     * Create a new user.
     */
    public function store(Request $request)
    {
        // Format group_ids to valid integers
        $this->formatGroupIds($request);

        $data = $request->validate([
            'username' => ['required', 'string', 'max:50', 'unique:users,username', 'regex:/^[a-zA-Z0-9_]+$/'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'status' => ['required', 'boolean'],
            'group_ids' => ['nullable', 'array'],
            'group_ids.*' => ['integer', 'exists:e_groups,id'],
        ]);

        $user = User::create([
            'username' => $data['username'],
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'status' => $data['status'],
            'is_root' => false,
        ]);

        // Assign groups
        if (!empty($data['group_ids'])) {
            $user->groups()->sync($data['group_ids']);
        }

        return ApiResponse::success($user->load('groups'), 201, 'Tạo người dùng thành công!');
    }

    /**
     * Get a single user.
     */
    public function show($id)
    {
        $user = User::with('groups')->findOrFail($id);

        return ApiResponse::success($user);
    }

    /**
     * Update a user.
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Prevent editing root user by non-root
        if ($user->is_root && !auth()->guard('admin')->user()->is_root) {
            return ApiResponse::error('Không thể sửa tài khoản root.');
        }

        // Format group_ids to valid integers
        $this->formatGroupIds($request);

        $data = $request->validate([
            'username' => ['required', 'string', 'max:50', 'unique:users,username,' . $id, 'regex:/^[a-zA-Z0-9_]+$/'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email,' . $id],
            'password' => ['nullable', 'string', 'min:6'],
            'status' => ['required', 'boolean'],
            'group_ids' => ['nullable', 'array'],
            'group_ids.*' => ['integer', 'exists:e_groups,id'],
        ]);

        $user->username = $data['username'];
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->status = $data['status'];

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        // Sync groups (but not for root user)
        if (!$user->is_root) {
            $user->groups()->sync($data['group_ids'] ?? []);
        }

        return ApiResponse::success($user->load('groups'), 200, 'Cập nhật người dùng thành công!');
    }

    /**
     * Delete a user.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Prevent deleting root user
        if ($user->is_root) {
            return ApiResponse::error('Không thể xóa tài khoản root.');
        }

        // Prevent self-deletion
        if ($user->id === auth()->guard('admin')->user()->id) {
            return ApiResponse::error('Không thể xóa tài khoản của chính mình.');
        }

        $user->groups()->detach();
        $user->delete();

        return ApiResponse::success(null, 200, 'Xóa người dùng thành công!');
    }
}
