@extends('admin.layout')

@section('content')
<div class="container mx-auto py-8 px-4">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Users Management</h2>
            <p class="text-gray-600">Manage approved users and their roles</p>
        </div>
        @if ($pendingCount > 0)
            <a href="{{ route('admin.users.pending') }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded font-semibold transition flex items-center gap-2">
                ⚠️ {{ $pendingCount }} Pending Approval
            </a>
        @endif
    </div>

    <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 text-blue-900">
        <div class="font-bold">Finance is a separate role.</div>
        <div class="text-sm mt-1">Only users approved as Finance can open the Finance panel and see financial records. Admin, Manager, Field, Coordinator, and POS users do not enter Finance.</div>
    </div>

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    @if ($approvedUsers->count() > 0)
        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Name</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Email</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Role</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Joined</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($approvedUsers as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-gray-900 font-medium">{{ $user->name }}</span>
                                @if ($user->id === auth()->id())
                                    <span class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">You</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $user->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($user->id !== auth()->id() && (! $user->isSuperAdmin() || auth()->user()?->isSuperAdmin()))
                                    @php
                                        $roleOptions = ['admin' => 'Admin', 'manager' => 'Manager', 'finance' => 'Finance', 'field_staff' => 'Field Staff', 'field_coordinator' => 'Field Coordinator', 'pos' => 'POS', 'user' => 'User'];
                                        if (auth()->user()?->isSuperAdmin()) {
                                            $roleOptions = ['super_admin' => 'Super Admin'] + $roleOptions;
                                        }
                                    @endphp
                                    <form action="{{ route('admin.users.approve', ['user' => $user, 'role' => '__role__']) }}" method="POST" class="flex flex-col gap-2" onsubmit="this.action = this.action.replace('__role__', this.elements.role.value);">
                                        @csrf
                                        @method('PATCH')
                                        <select name="role" class="border border-gray-300 rounded px-2 py-1 text-xs">
                                            @foreach ($roleOptions as $role => $label)
                                                <option value="{{ $role }}" @selected($user->role === $role)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white px-3 py-1 rounded text-xs font-semibold transition">
                                            Save Role
                                        </button>
                                    </form>
                                @else
                                    @if ($user->isSuperAdmin())
                                        <span class="inline-block bg-amber-100 text-amber-900 px-3 py-1 rounded-full text-xs font-semibold">⚡ Super Admin</span>
                                    @elseif ($user->role === 'admin')
                                        <span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">👑 Admin</span>
                                    @elseif ($user->isManager())
                                        <span class="inline-block bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-xs font-semibold">Manager</span>
                                    @elseif ($user->isFinance())
                                        <span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-semibold">Finance</span>
                                    @elseif ($user->isFieldStaff())
                                        <span class="inline-block bg-teal-100 text-teal-800 px-3 py-1 rounded-full text-xs font-semibold">Field Staff</span>
                                    @elseif ($user->isFieldCoordinator())
                                        <span class="inline-block bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-xs font-semibold">Field Coordinator</span>
                                    @elseif ($user->isPos())
                                        <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">🛒 POS</span>
                                    @else
                                        <span class="inline-block bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-semibold">None</span>
                                    @endif
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded text-xs font-semibold">✓ Approved</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @if ($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.delete', $user) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs font-semibold transition" onclick="return confirm('Delete user {{ $user->name }}?');">
                                            Delete
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-500">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $approvedUsers->links() }}
        </div>
    @else
        <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded">
            <p class="text-blue-700 font-semibold">No approved users yet</p>
        </div>
    @endif
</div>
@endsection
