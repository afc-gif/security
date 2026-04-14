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

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if ($approvedUsers->count() > 0)
        <div class="bg-white rounded-lg shadow overflow-hidden">
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
                                @if ($user->isAdmin())
                                    <span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-semibold">👑 Admin</span>
                                @elseif ($user->isManager())
                                    <span class="inline-block bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-xs font-semibold">Manager</span>
                                @elseif ($user->isFieldStaff())
                                    <span class="inline-block bg-teal-100 text-teal-800 px-3 py-1 rounded-full text-xs font-semibold">Field Staff</span>
                                @elseif ($user->isPos())
                                    <span class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded-full text-xs font-semibold">🛒 POS</span>
                                @else
                                    <span class="inline-block bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-semibold">None</span>
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
