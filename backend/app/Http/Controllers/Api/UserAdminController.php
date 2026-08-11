<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserAdminController extends Controller
{
    public function index()
    {
        return User::orderBy('created_at', 'desc')
            ->get()
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_active' => $user->status === 'approved',
                    'role' => $user->role ?? 'user',
                    'created_at' => $user->created_at,
                ];
            });
    }

    public function update(Request $request, User $user)
    {
        $validRoles = ['admin', 'manager', 'finance', 'field_staff', 'field_coordinator', 'pos', 'user'];
        $data = $request->validate([
            'role' => ['sometimes', 'required', Rule::in($validRoles)],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('is_active', $data)) {
            $user->status = $data['is_active'] ? 'approved' : 'pending';
        }

        if (array_key_exists('role', $data)) {
            $user->role = $data['role'];
            if ($user->role && $user->status !== 'approved') {
                $user->status = 'approved';
            }
        }

        $user->save();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_active' => $user->status === 'approved',
            'role' => $user->role ?? 'user',
            'created_at' => $user->created_at,
        ]);
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        $user->delete();

        return response()->noContent();
    }
}
