@extends('admin.layout')

@section('title', 'Finance Projects | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        @include('finance.partials.nav')

        <div class="mb-6">
            <h1 class="text-3xl font-extrabold text-gray-950">Projects</h1>
            <p class="mt-1 text-sm text-gray-600">Open a project to review value, costs, materials, and expenses.</p>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('finance.projects.index') }}" class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-[240px_auto] sm:items-end">
                <div>
                    <label for="status" class="block text-sm font-bold text-gray-800">Status</label>
                    <select id="status" name="status" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="">All statuses</option>
                        @foreach(['not_started', 'ongoing', 'on_hold', 'ready_for_review', 'completed'] as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                                {{ str_replace('_', ' ', Illuminate\Support\Str::title($status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex min-h-[42px] items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-blue-700">
                        Apply
                    </button>
                    @if($filters['status'] ?? null)
                        <a href="{{ route('finance.projects.index') }}" class="inline-flex min-h-[42px] items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 transition hover:bg-gray-50">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-5 py-4">
                <h2 class="text-lg font-extrabold text-gray-950">All Projects</h2>
                <p class="mt-1 text-sm text-gray-600">{{ $projects->total() }} {{ Illuminate\Support\Str::plural('project', $projects->total()) }} found</p>
            </div>

            @if($projects->isEmpty())
                <div class="px-5 py-12 text-center">
                    <div class="text-lg font-extrabold text-gray-950">No projects found</div>
                    <p class="mt-2 text-sm text-gray-600">Try clearing the status filter.</p>
                </div>
            @else
                <div class="hidden lg:block">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Project</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Client</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Status</th>
                                <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-gray-500">Project Value</th>
                                <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-gray-500">Approved Costs</th>
                                <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-gray-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach($projects as $project)
                                @php($summary = $summaries[$project->id])
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-4">
                                        <div class="font-extrabold text-gray-950">{{ $project->title ?: $project->project_code }}</div>
                                        <div class="mt-1 text-sm text-gray-600">{{ $project->project_code }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-sm font-bold text-gray-800">{{ $project->client?->company_name ?: $project->client?->client_name ?: 'Client unavailable' }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-md bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-800">
                                            {{ str_replace('_', ' ', Illuminate\Support\Str::title($project->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right font-extrabold text-gray-950">{{ $summary['contract_value'] === null ? '-' : $financeMoney($summary['contract_value']) }}</td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="font-extrabold text-gray-950">{{ $financeMoney($summary['approved_cost']) }}</div>
                                        <div class="mt-1 text-xs text-gray-500">Expenses {{ $financeMoney($summary['approved_expenses']) }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <a href="{{ route('finance.projects.show', $project) }}" class="inline-flex items-center justify-center rounded-md bg-gray-900 px-3 py-2 text-sm font-bold text-white transition hover:bg-gray-800">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="divide-y divide-gray-100 lg:hidden">
                    @foreach($projects as $project)
                        @php($summary = $summaries[$project->id])
                        <a href="{{ route('finance.projects.show', $project) }}" class="block px-5 py-4 hover:bg-gray-50">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="font-extrabold text-gray-950">{{ $project->title ?: $project->project_code }}</div>
                                    <div class="mt-1 text-sm text-gray-600">{{ $project->client?->company_name ?: $project->client?->client_name ?: 'Client unavailable' }}</div>
                                    <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                        <span class="rounded-md bg-gray-100 px-2 py-1 font-bold text-gray-800">{{ str_replace('_', ' ', Illuminate\Support\Str::title($project->status)) }}</span>
                                        <span class="rounded-md bg-gray-100 px-2 py-1 font-bold text-gray-800">{{ $project->project_code }}</span>
                                    </div>
                                </div>
                                <div class="shrink-0 text-right">
                                    <div class="font-extrabold text-gray-950">{{ $summary['contract_value'] === null ? '-' : $financeMoney($summary['contract_value']) }}</div>
                                    <div class="mt-1 text-xs text-gray-500">Costs {{ $financeMoney($summary['approved_cost']) }}</div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="mt-6">
            {{ $projects->links() }}
        </div>
    </div>
</div>
@endsection
