@extends('admin.layout')

@section('title', 'Project Finance | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Project Finance</h1>
                <p class="text-sm text-gray-600 mt-1">Private project financial profiles, costs, and profitability tracking.</p>
            </div>
            <a href="{{ route('finance.dashboard') }}" class="inline-flex items-center justify-center bg-gray-200 hover:bg-gray-300 text-gray-800 px-5 py-2.5 rounded-lg font-semibold transition">
                Finance Dashboard
            </a>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('finance.projects.index') }}" class="bg-white rounded-lg border border-gray-200 shadow-sm p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-gray-500 mb-1">Project Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">All</option>
                        @foreach(['not_started', 'ongoing', 'on_hold', 'ready_for_review', 'completed'] as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ str_replace('_', ' ', \Illuminate\Support\Str::title($status)) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-4 flex gap-2">
                <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-semibold">Apply</button>
                <a href="{{ route('finance.projects.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-4 py-2 rounded-lg font-semibold">Clear</a>
            </div>
        </form>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            @if($projects->count() === 0)
                <div class="p-10 text-center text-gray-600">No projects match the selected filters.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Project</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Client</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">Contract</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">Budget</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">Approved Cost</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">Est. Profit</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-600">Finance Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($projects as $project)
                                @php($summary = $summaries[$project->id])
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-gray-900">{{ $project->project_code }}</div>
                                        <div class="text-xs text-gray-500 max-w-[260px] truncate">{{ $project->title }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-800 whitespace-nowrap">{{ $project->client?->client_name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 font-semibold text-right whitespace-nowrap">{{ $summary['contract_value'] === null ? '—' : $financeMoney($summary['contract_value']) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 font-semibold text-right whitespace-nowrap">{{ $summary['approved_budget'] === null ? '—' : $financeMoney($summary['approved_budget']) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 font-semibold text-right whitespace-nowrap">{{ $financeMoney($summary['approved_cost']) }}</td>
                                    <td class="px-4 py-3 text-sm text-right whitespace-nowrap font-semibold {{ ($summary['estimated_profit'] ?? 0) < 0 ? 'text-red-700' : 'text-gray-900' }}">{{ $summary['estimated_profit'] === null ? '—' : $financeMoney($summary['estimated_profit']) }}</td>
                                    <td class="px-4 py-3">
                                        @if($summary['is_over_budget'])
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-red-100 text-red-800">Over budget</span>
                                        @elseif($project->financial)
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800">Profile active</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-gray-100 text-gray-700">No profile</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex justify-end">
                                            <a href="{{ route('finance.projects.show', $project) }}" class="inline-flex items-center px-3 py-1.5 rounded-md bg-blue-50 text-blue-700 hover:bg-blue-100 text-sm font-semibold">View</a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 border-t border-gray-200">{{ $projects->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
