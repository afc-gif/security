@extends('admin.layout')

@section('title', 'Finance Overview | ARTSCI Admin Console')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        @include('finance.partials.nav')

        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-gray-950">Finance</h1>
            <p class="mt-1 text-base text-gray-600">Manage job expenses and project finances.</p>
        </div>

        @if (session('success'))
            <div class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-8 grid grid-cols-1 divide-y divide-gray-200 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm sm:grid-cols-3 sm:divide-x sm:divide-y-0">
            <div class="px-5 py-4">
                <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Active Jobs</div>
                <div class="mt-2 text-3xl font-extrabold text-gray-950">{{ $activeJobs }}</div>
            </div>
            <div class="px-5 py-4">
                <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Active Projects</div>
                <div class="mt-2 text-3xl font-extrabold text-gray-950">{{ $activeProjects }}</div>
            </div>
            <div class="px-5 py-4">
                <div class="text-xs font-bold uppercase tracking-wide text-gray-500">Pending Review</div>
                <div class="mt-2 text-3xl font-extrabold text-gray-950">{{ $pendingReviewCount }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-5 py-4">
                    <h2 class="text-lg font-extrabold text-gray-950">Recent Jobs</h2>
                    <a href="{{ route('finance.jobs.index') }}" class="inline-flex items-center justify-center rounded-md bg-gray-900 px-3 py-2 text-sm font-bold text-white transition hover:bg-gray-800">
                        View All Jobs
                    </a>
                </div>

                @if($recentJobs->isEmpty())
                    <div class="px-5 py-10 text-center text-gray-600">No jobs available yet.</div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($recentJobs as $job)
                            @php
                                $client = $job->jobRequest?->client;
                            @endphp
                            <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <div class="truncate font-extrabold text-gray-950">{{ $job->title ?: $job->jobRequest?->title ?: 'Untitled job' }}</div>
                                    <div class="mt-1 truncate text-sm text-gray-600">{{ $client?->company_name ?: $client?->client_name ?: 'Client unavailable' }}</div>
                                </div>
                                <div class="flex shrink-0 items-center justify-between gap-3 sm:justify-end">
                                    <span class="text-sm font-bold text-gray-700">{{ str_replace('_', ' ', Illuminate\Support\Str::title($job->status)) }}</span>
                                    <a href="{{ route('finance.jobs.show', $job) }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-bold text-gray-900 transition hover:bg-gray-50">
                                        View
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-5 py-4">
                    <h2 class="text-lg font-extrabold text-gray-950">Recent Projects</h2>
                    <a href="{{ route('finance.projects.index') }}" class="inline-flex items-center justify-center rounded-md bg-gray-900 px-3 py-2 text-sm font-bold text-white transition hover:bg-gray-800">
                        View All Projects
                    </a>
                </div>

                @if($recentProjects->isEmpty())
                    <div class="px-5 py-10 text-center text-gray-600">No projects available yet.</div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($recentProjects as $project)
                            <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <div class="truncate font-extrabold text-gray-950">{{ $project->title ?: $project->project_code }}</div>
                                    <div class="mt-1 truncate text-sm text-gray-600">{{ $project->client?->company_name ?: $project->client?->client_name ?: 'Client unavailable' }}</div>
                                </div>
                                <div class="flex shrink-0 items-center justify-between gap-3 sm:justify-end">
                                    <span class="text-sm font-bold text-gray-700">{{ str_replace('_', ' ', Illuminate\Support\Str::title($project->status)) }}</span>
                                    <a href="{{ route('finance.projects.show', $project) }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-bold text-gray-900 transition hover:bg-gray-50">
                                        View
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection
