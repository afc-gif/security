<?php

namespace App\Http\Controllers\Auth;

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
        /** @var User|null $authUser */
        $authUser = $request->user();

        $validRoles = ['super_admin', 'executive', 'admin', 'manager', 'finance', 'field_staff', 'field_coordinator', 'pos', 'user'];
        $data = $request->validate([
            'role' => ['sometimes', 'required', Rule::in($validRoles)],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('role', $data)) {
            $newRole = $data['role'];

            // Self-role modification / self-promotion protection
            if ($authUser && $authUser->id === $user->id && $user->role !== $newRole) {
                return response()->json(['message' => 'You cannot modify your own role.'], 403);
            }

            // Super Admin assignment & target protection
            if (($newRole === 'super_admin' || $user->role === 'super_admin') && (!$authUser || !$authUser->isSuperAdmin())) {
                return response()->json(['message' => 'Only a Super Admin can assign the Super Admin role or modify a Super Admin user.'], 403);
            }

            // Last Super Admin demotion protection
            if ($user->role === 'super_admin' && $newRole !== 'super_admin') {
                $superAdminCount = User::where('role', 'super_admin')->where('status', 'approved')->count();
                if ($superAdminCount <= 1) {
                    return response()->json(['message' => 'The last remaining Super Admin cannot be demoted.'], 403);
                }
            }

            $user->role = $newRole;
            if ($user->role && $user->status !== 'approved') {
                $user->status = 'approved';
            }
        }

        if (array_key_exists('is_active', $data)) {
            // Last Super Admin deactivation protection
            if (!$data['is_active'] && $user->role === 'super_admin') {
                $superAdminCount = User::where('role', 'super_admin')->where('status', 'approved')->count();
                if ($superAdminCount <= 1) {
                    return response()->json(['message' => 'The last remaining Super Admin cannot be deactivated.'], 403);
                }
            }
            $user->status = $data['is_active'] ? 'approved' : 'pending';
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
        /** @var User|null $authUser */
        $authUser = $request->user();

        if ($authUser && $authUser->id === $user->id) {
            return response()->json(['message' => 'You cannot delete your own account.'], 422);
        }

        // Only Super Admin can delete a Super Admin user
        if ($user->role === 'super_admin' && (!$authUser || !$authUser->isSuperAdmin())) {
            return response()->json(['message' => 'Only a Super Admin can delete a Super Admin user.'], 403);
        }

        // Last Super Admin deletion protection
        if ($user->role === 'super_admin') {
            $superAdminCount = User::where('role', 'super_admin')->where('status', 'approved')->count();
            if ($superAdminCount <= 1) {
                return response()->json(['message' => 'The last remaining Super Admin cannot be deleted.'], 403);
            }
        }

        $user->delete();

        return response()->noContent();
    }
}
