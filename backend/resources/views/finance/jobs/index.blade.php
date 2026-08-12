@extends('admin.layout')

@section('title', 'Finance Jobs | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        @include('finance.partials.nav')

        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-950">Jobs</h1>
                <p class="mt-1 text-sm text-gray-600">Open a job to view expenses or add a new job expense.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('finance.jobs.index') }}" class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <div class="grid grid-cols-1 gap-3 lg:grid-cols-[minmax(0,1fr)_220px_auto] lg:items-end">
                <div>
                    <label for="search" class="block text-sm font-bold text-gray-800">Search jobs</label>
                    <input
                        id="search"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        type="search"
                        placeholder="Client, company, or job title"
                        class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                    >
                </div>
                <div>
                    <label for="status" class="block text-sm font-bold text-gray-800">Status</label>
                    <select id="status" name="status" class="mt-1 w-full rounded-md border border-gray-300 px-3 py-2.5 text-sm text-gray-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        <option value="">All statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                                {{ str_replace('_', ' ', Illuminate\Support\Str::title($status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex min-h-[42px] flex-1 items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-sm font-bold text-white transition hover:bg-blue-700 lg:flex-none">
                        Search
                    </button>
                    @if(($filters['search'] ?? null) || ($filters['status'] ?? null))
                        <a href="{{ route('finance.jobs.index') }}" class="inline-flex min-h-[42px] items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-bold text-gray-700 transition hover:bg-gray-50">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-5 py-4">
                <h2 class="text-lg font-extrabold text-gray-950">Existing Jobs</h2>
                <p class="mt-1 text-sm text-gray-600">{{ $jobs->total() }} {{ Illuminate\Support\Str::plural('job', $jobs->total()) }} found</p>
            </div>

            @if($jobs->isEmpty())
                <div class="px-5 py-12 text-center">
                    <div class="text-lg font-extrabold text-gray-950">No jobs found</div>
                    <p class="mt-2 text-sm text-gray-600">Try a different search term or clear the status filter.</p>
                </div>
            @else
                <div class="hidden lg:block">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Job</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Client</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Assigned to</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Status</th>
                                <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wide text-gray-500">Date</th>
                                <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-gray-500">Expenses</th>
                                <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-gray-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @foreach($jobs as $job)
                                @php
                                    $client = $job->jobRequest?->client;
                                    $location = trim(collect([$client?->address, $client?->city_state])->filter()->implode(', '));
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-4">
                                        <div class="font-extrabold text-gray-950">{{ $job->title ?: $job->jobRequest?->title }}</div>
                                        <div class="mt-1 text-sm text-gray-600">{{ $job->serviceCategory?->name ?? 'General job' }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="font-bold text-gray-900">{{ $client?->company_name ?: $client?->client_name ?: 'Client unavailable' }}</div>
                                        <div class="mt-1 text-sm text-gray-600">{{ $location !== '' ? $location : ($client?->contact_person ?? 'No location recorded') }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-700">{{ $job->claimer?->name ?? 'Unassigned' }}</td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-md bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-800">
                                            {{ str_replace('_', ' ', Illuminate\Support\Str::title($job->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-700">{{ $job->created_at?->format('M j, Y') ?? '-' }}</td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="font-extrabold text-gray-950">{{ $financeMoney($job->approved_expenses_total ?? 0) }}</div>
                                        <div class="mt-1 text-xs text-gray-500">{{ $job->financial_expenses_count }} records</div>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <a href="{{ route('finance.jobs.show', $job) }}" class="inline-flex items-center justify-center rounded-md bg-gray-900 px-3 py-2 text-sm font-bold text-white transition hover:bg-gray-800">
                                            Open
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="divide-y divide-gray-100 lg:hidden">
                    @foreach($jobs as $job)
                        @php
                            $client = $job->jobRequest?->client;
                            $location = trim(collect([$client?->address, $client?->city_state])->filter()->implode(', '));
                        @endphp
                        <a href="{{ route('finance.jobs.show', $job) }}" class="block px-5 py-4 hover:bg-gray-50">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="font-extrabold text-gray-950">{{ $job->title ?: $job->jobRequest?->title }}</div>
                                    <div class="mt-1 text-sm text-gray-600">{{ $client?->company_name ?: $client?->client_name ?: 'Client unavailable' }}</div>
                                    <div class="mt-1 text-xs text-gray-500">{{ $location !== '' ? $location : 'No location recorded' }}</div>
                                    <div class="mt-2 flex flex-wrap gap-2 text-xs">
                                        <span class="rounded-md bg-gray-100 px-2 py-1 font-bold text-gray-800">{{ str_replace('_', ' ', Illuminate\Support\Str::title($job->status)) }}</span>
                                        <span class="rounded-md bg-gray-100 px-2 py-1 font-bold text-gray-800">{{ $job->claimer?->name ?? 'Unassigned' }}</span>
                                    </div>
                                </div>
                                <div class="shrink-0 text-right">
                                    <div class="font-extrabold text-gray-950">{{ $financeMoney($job->approved_expenses_total ?? 0) }}</div>
                                    <div class="mt-1 text-xs text-gray-500">{{ $job->financial_expenses_count }} records</div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="mt-6">
            {{ $jobs->links() }}
        </div>
    </div>
</div>
@endsection
