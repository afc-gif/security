@extends('admin.layout')

@section('title', 'Inspections | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Inspections</h1>
                <p class="text-sm text-gray-600 mt-1">Create and assign field inspections for client sites.</p>
            </div>
            <a href="{{ route('admin.inspections.create') }}" class="inline-flex items-center justify-center bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold transition">
                + Add Inspection
            </a>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            @if($inspections->count() === 0)
                <div class="p-10 text-center text-gray-600">
                    No inspections yet. Create the first inspection when a client site needs field review.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Code</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Client</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Assigned To</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Date</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($inspections as $inspection)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-gray-900 whitespace-nowrap">{{ $inspection->inspection_code }}</div>
                                        <div class="text-xs text-gray-500 max-w-[220px] truncate">{{ $inspection->title }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $inspection->client?->client_name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $inspection->assignedUser?->name ?? 'Unassigned' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold {{ $inspection->status === 'completed' ? 'bg-green-100 text-green-800' : ($inspection->status === 'assigned' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ ucfirst($inspection->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                        {{ $inspection->scheduled_date?->format('d M Y H:i') ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end gap-2">
                                            <a href="{{ route('admin.inspections.show', $inspection) }}" class="inline-flex items-center px-3 py-1.5 rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 text-sm font-semibold">
                                                View
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $inspections->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
