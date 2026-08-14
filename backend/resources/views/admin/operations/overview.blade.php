@extends('admin.layout')

@section('title', 'Operations — Overview | ARTSCI Admin Console')

@section('content')
@push('head')
<style>
    .text-muted-inline { color: var(--brand-muted); }
</style>
@endpush
@php
    $statusLabel = fn (?string $s) => match ($s) {
        'pending_assignment'  => 'Needs Assignment',
        'open'                => 'Available to Claim',
        'claimed'             => 'In Progress',
        'submitted'           => 'With Coordinator',
        'pending_admin_review'=> 'Ready for Review',
        'returned'            => 'Returned to Field',
        'approved'            => 'Approved',
        'rejected'            => 'Rejected',
        'reopened'            => 'Reopened',
        'overdue'             => 'Overdue',
        'closed'              => 'Closed',
        default               => ucwords(str_replace('_', ' ', $s ?? '')),
    };

    $statusBadge = fn (?string $s) => match ($s) {
        'pending_assignment'  => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800',
        'open','reopened'     => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800',
        'claimed'             => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-100 text-sky-800',
        'submitted'           => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800',
        'pending_admin_review'=> 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-900',
        'returned'            => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-800',
        'approved'            => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800',
        'overdue'             => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800',
        'rejected'            => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800',
        'closed'              => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-700',
        default               => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-700',
    };
@endphp

<div class="min-h-screen" style="background: var(--brand-soft);">
    <div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">

        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-bold" style="color: var(--brand-ink);">Operations Overview</h1>
                <p class="text-sm mt-1" style="color: var(--brand-muted);">Monitor the operations pipeline and take action on items that need your attention.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.job-inbox.index') }}"
                   class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold border transition"
                   style="background: white; border-color: var(--brand-border); color: var(--brand-ink);">
                    Job Inbox
                </a>
                <a href="{{ route('admin.job-requests.create') }}"
                   class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition"
                   style="background: var(--brand-dark);">
                    + New Job Request
                </a>
            </div>
        </div>

        {{-- Summary Cards (clickable filters into Job Inbox) --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
            @php
                $cards = [
                    [
                        'label'  => 'Ready for Review',
                        'count'  => $summary['ready_for_review'],
                        'color'  => '#d97706',   // amber
                        'bg'     => '#fffbeb',
                        'border' => '#fcd34d',
                        'filter' => 'pending_admin_review',
                        'urgent' => $summary['ready_for_review'] > 0,
                    ],
                    [
                        'label'  => 'Overdue',
                        'count'  => $summary['overdue'],
                        'color'  => '#dc2626',
                        'bg'     => '#fef2f2',
                        'border' => '#fca5a5',
                        'filter' => 'overdue',
                        'urgent' => $summary['overdue'] > 0,
                    ],
                    [
                        'label'  => 'Needs Assignment',
                        'count'  => $summary['needs_assignment'],
                        'color'  => '#7c3aed',
                        'bg'     => '#f5f3ff',
                        'border' => '#c4b5fd',
                        'filter' => 'pending_assignment',
                        'urgent' => false,
                    ],
                    [
                        'label'  => 'In Progress',
                        'count'  => $summary['in_progress'],
                        'color'  => '#0369a1',
                        'bg'     => '#f0f9ff',
                        'border' => '#7dd3fc',
                        'filter' => 'claimed',
                        'urgent' => false,
                    ],
                    [
                        'label'  => 'Returned',
                        'count'  => $summary['returned'],
                        'color'  => '#ea580c',
                        'bg'     => '#fff7ed',
                        'border' => '#fdba74',
                        'filter' => 'returned',
                        'urgent' => $summary['returned'] > 0,
                    ],
                    [
                        'label'  => 'Approved',
                        'count'  => $summary['approved'],
                        'color'  => '#16a34a',
                        'bg'     => '#f0fdf4',
                        'border' => '#86efac',
                        'filter' => 'approved',
                        'urgent' => false,
                    ],
                ];
            @endphp

            @foreach($cards as $card)
                <a href="{{ route('admin.job-inbox.index', ['status' => $card['filter']]) }}"
                   class="rounded-2xl p-4 flex flex-col gap-1 transition hover:shadow-lg border summary-card"
                   data-bg="{{ $card['bg'] }}" data-border="{{ $card['border'] }}" data-color="{{ $card['color'] }}">
                    <div class="text-xs font-semibold">{{ $card['label'] }}</div>
                    <div class="text-3xl font-bold">{{ $card['count'] }}</div>
                    @if($card['urgent'] && $card['count'] > 0)
                        <div class="text-xs font-medium" style="opacity: 0.75;">Action needed</div>
                    @endif
                </a>
            @endforeach
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

            {{-- Requires Attention --}}
            <section class="rounded-2xl border overflow-hidden" style="background: white; border-color: var(--brand-border);">
                <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color: var(--brand-border);">
                    <div>
                        <h2 class="text-lg font-bold" style="color: var(--brand-ink);">Requires Attention</h2>
                        <p class="text-xs mt-0.5" style="color: var(--brand-muted);">Items pending your review or action</p>
                    </div>
                    <a href="{{ route('admin.job-inbox.index', ['status' => 'pending_admin_review']) }}"
                       class="text-sm font-semibold" style="color: var(--brand-dark);">View all →</a>
                </div>

                @if($requiresAttention->count() === 0)
                    <div class="px-5 py-10 text-center" style="color: var(--brand-muted);">
                        <div class="text-4xl mb-3">✓</div>
                        <div class="text-sm font-semibold">Nothing requires attention right now.</div>
                    </div>
                @else
                    <div class="divide-y" style="border-color: var(--brand-border);">
                        @foreach($requiresAttention as $item)
                            <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="font-semibold text-sm truncate" style="color: var(--brand-ink);">
                                        {{ $item->serviceCategory?->name ?? $item->title ?? 'Category item' }}
                                    </div>
                                    <div class="text-xs mt-0.5 {{ $item->isOverdue() ? 'text-red-600 font-semibold' : 'text-muted-inline' }}">
                                        {{ $item->jobRequest?->client?->client_name ?? 'Client unavailable' }}
                                        @if($item->jobRequest?->title)
                                            · {{ $item->jobRequest->title }}
                                        @endif
                                    </div>
                                    @if($item->claimer)
                                        <div class="text-xs mt-0.5" style="color: var(--brand-muted);">
                                            Assigned: {{ $item->claimer->name }}
                                        </div>
                                    @endif
                                </div>
                                <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
                                    <span class="{{ $statusBadge($item->status) }}">
                                        {{ $statusLabel($item->status) }}
                                    </span>
                                    @if($item->status === 'pending_admin_review')
                                        <a href="{{ route('admin.job-items.show', $item) }}"
                                           class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold text-white"
                                           style="background: #d97706;">
                                            Review Report
                                        </a>
                                    @elseif($item->status === 'overdue')
                                        <form method="POST" action="{{ route('admin.job-items.reopen', $item) }}" class="inline">
                                            @csrf
                                            <button type="submit"
                                                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold text-white"
                                                    style="background: #dc2626;"
                                                    onclick="return confirm('Reopen this overdue job?')">
                                                Reopen
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('admin.job-items.show', $item) }}"
                                           class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold"
                                           style="background: #f1f5f9; color: var(--brand-ink);">
                                            View
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Pipeline — Recently Active --}}
            <section class="rounded-2xl border overflow-hidden" style="background: white; border-color: var(--brand-border);">
                <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color: var(--brand-border);">
                    <div>
                        <h2 class="text-lg font-bold" style="color: var(--brand-ink);">Active Pipeline</h2>
                        <p class="text-xs mt-0.5" style="color: var(--brand-muted);">Items in progress or pending assignment</p>
                    </div>
                    <a href="{{ route('admin.job-inbox.index') }}"
                       class="text-sm font-semibold" style="color: var(--brand-dark);">Full Inbox →</a>
                </div>

                @if($recentlyActive->count() === 0)
                    <div class="px-5 py-10 text-center" style="color: var(--brand-muted);">
                        <div class="text-sm font-semibold">No active pipeline items.</div>
                    </div>
                @else
                    <div class="divide-y" style="border-color: var(--brand-border);">
                        @foreach($recentlyActive as $item)
                            <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="font-semibold text-sm truncate" style="color: var(--brand-ink);">
                                        {{ $item->serviceCategory?->name ?? $item->title ?? 'Category item' }}
                                    </div>
                                    <div class="text-xs mt-0.5" style="color: var(--brand-muted);">
                                        {{ $item->jobRequest?->client?->client_name ?? 'Client unavailable' }}
                                        @if($item->claimer)
                                            · {{ $item->claimer->name }}
                                        @endif
                                    </div>
                                    @if($item->due_date)
                                        <div class="text-xs mt-0.5 {{ $item->isOverdue() ? 'text-red-600 font-semibold' : 'text-muted-inline' }}">
                                            Due: {{ $item->due_date->format('d M Y') }}
                                            @if($item->isOverdue()) (overdue) @elseif($item->due_date->isToday()) (due today) @endif
                                        </div>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="{{ $statusBadge($item->status) }}">
                                        {{ $statusLabel($item->status) }}
                                    </span>
                                    <a href="{{ route('admin.job-items.show', $item) }}"
                                       class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold"
                                       style="background: #f1f5f9; color: var(--brand-ink);">
                                        View
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

        </div>

        {{-- Job Requests Summary --}}
        <div class="mt-6 rounded-2xl border p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
             style="background: white; border-color: var(--brand-border);">
            <div>
                <h3 class="text-base font-bold" style="color: var(--brand-ink);">Job Requests</h3>
                <p class="text-sm mt-0.5" style="color: var(--brand-muted);">
                    {{ $totalJobRequests }} total · {{ $openJobRequests }} open
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.job-requests.index') }}"
                   class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold border transition"
                   style="background: white; border-color: var(--brand-border); color: var(--brand-ink);">
                    View All Requests
                </a>
                <a href="{{ route('admin.job-requests.create') }}"
                   class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold text-white transition"
                   style="background: var(--brand-dark);">
                    New Request
                </a>
            </div>
        </div>

    </div>
</div>
    <script>
        // Apply dynamic summary-card styles set via data-attributes
        document.querySelectorAll('.summary-card[data-bg]').forEach(function(el) {
            el.style.background = el.getAttribute('data-bg') || '';
            el.style.borderColor = el.getAttribute('data-border') || '';
            el.style.color = el.getAttribute('data-color') || '';
        });
    </script>
    @endsection
