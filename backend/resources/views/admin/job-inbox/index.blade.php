@extends('admin.layout')

@section('title', 'Job Inbox | ARTSCI Admin Console')

@section('content')
{{-- Status labels, badge classes and sort options are provided by the controller. --}}

@push('head')
<style>
    .inbox-badge {
        display: inline-flex;
        align-items: center;
        padding: 2px 10px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
        letter-spacing: 0.01em;
    }
    .badge-purple { background: #ede9fe; color: #6d28d9; }
    .badge-blue   { background: #dbeafe; color: #1d4ed8; }
    .badge-yellow { background: #fef9c3; color: #a16207; }
    .badge-amber  { background: #fef3c7; color: #92400e; }
    .badge-orange { background: #ffedd5; color: #c2410c; }
    .badge-green  { background: #dcfce7; color: #166534; }
    .badge-red    { background: #fee2e2; color: #991b1b; }
    .badge-gray   { background: #f3f4f6; color: #4b5563; }

    .summary-card {
        display: flex;
        flex-direction: column;
        gap: 2px;
        padding: 14px 16px;
        border-radius: 14px;
        border: 1.5px solid transparent;
        cursor: pointer;
        text-decoration: none;
        transition: box-shadow 0.15s, transform 0.1s;
    }
    .summary-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.1); transform: translateY(-1px); }
    .summary-card.active { box-shadow: 0 0 0 2.5px currentColor; }
    .summary-card-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; opacity: 0.75; }
    .summary-card-count { font-size: 28px; font-weight: 800; line-height: 1; }
    .summary-card-sub   { font-size: 11px; opacity: 0.65; }

    .inbox-table th {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 10px 16px;
        white-space: nowrap;
        color: var(--brand-muted);
        background: #f8fafc;
        border-bottom: 1.5px solid var(--brand-border);
    }
    .inbox-table td {
        padding: 12px 16px;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }
    .inbox-table tr:last-child td { border-bottom: none; }
    .inbox-table tr:hover td { background: #fafbfc; }

    .action-btn-primary {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 5px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: opacity 0.15s;
    }
    .action-btn-primary:hover { opacity: 0.85; }
    .action-btn-review  { background: #d97706; color: white; }
    .action-btn-assign  { background: #7c3aed; color: white; }
    .action-btn-reopen  { background: #dc2626; color: white; }
    .action-btn-view    { background: #f1f5f9; color: #0f172a; }
    .action-btn-convert { background: #16a34a; color: white; }

    .filter-chip {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
        border: 1.5px solid var(--brand-border);
        text-decoration: none;
        transition: all 0.12s;
        cursor: pointer;
        background: white;
        color: var(--brand-ink);
    }
    .filter-chip:hover, .filter-chip.active {
        background: var(--brand-ink);
        color: white;
        border-color: var(--brand-ink);
    }

    /* Due-date state colours — avoids Blade interpolation inside style="" */
    .due-overdue-text { color: #dc2626; }
    .due-today-text   { color: #d97706; }
    .due-normal-text  { color: var(--brand-ink); }
    .text-muted-inline { color: var(--brand-muted); }
    .text-ink { color: var(--brand-ink); }
    .text-muted { color: var(--brand-muted); }
</style>
@endpush

<div class="min-h-screen" style="background: var(--brand-soft);">
    <div class="max-w-7xl mx-auto px-4 py-8 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-3xl font-bold" style="color: var(--brand-ink);">Job Inbox</h1>
                <p class="text-sm mt-1" style="color: var(--brand-muted);">All field operations tracked in one place. Click a card to filter, or search below.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.operations.overview') }}"
                   class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold border transition"
                   style="background: white; border-color: var(--brand-border); color: var(--brand-ink);">
                    ← Overview
                </a>
                <a href="{{ route('admin.job-requests.create') }}"
                   class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold text-white"
                   style="background: var(--brand-dark);">
                    + New Job Request
                </a>
            </div>
        </div>

        {{-- Summary Strip (clickable filter cards) --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
            @php
                $summaryCards = [
                    [
                        'key'    => 'pending_admin_review',
                        'label'  => 'Ready for Review',
                        'count'  => $summary['pending_review'],
                        'color'  => '#d97706',
                        'bg'     => '#fffbeb',
                        'border' => '#fcd34d',
                    ],
                    [
                        'key'    => 'overdue',
                        'label'  => 'Overdue',
                        'count'  => $summary['overdue'],
                        'color'  => '#dc2626',
                        'bg'     => '#fef2f2',
                        'border' => '#fca5a5',
                    ],
                    [
                        'key'    => 'pending_assignment',
                        'label'  => 'Needs Assignment',
                        'count'  => $summary['needs_assignment'],
                        'color'  => '#7c3aed',
                        'bg'     => '#f5f3ff',
                        'border' => '#c4b5fd',
                    ],
                    [
                        'key'    => 'returned',
                        'label'  => 'Returned to Field',
                        'count'  => $summary['returned'],
                        'color'  => '#ea580c',
                        'bg'     => '#fff7ed',
                        'border' => '#fdba74',
                    ],
                ];
            @endphp

            @foreach($summaryCards as $card)
                @php
                    $isActive = $activeStatus === $card['key'];
                    $filterUrl = $isActive
                        ? route('admin.job-inbox.index', array_filter(array_merge($filters, ['status' => null, 'page' => null])))
                        : route('admin.job-inbox.index', array_merge($filters, ['status' => $card['key'], 'page' => null]));
                @endphp
                @php
                    $cardBg     = $card['bg'];
                    $cardColor  = $card['color'];
                    $cardBorder = $isActive ? $card['color'] : $card['border'];
                    $cardStyle  = "background:{$cardBg};border-color:{$cardBorder};color:{$cardColor};";
                @endphp
                     <a href="{{ $filterUrl }}"
                         class="summary-card {{ $isActive ? 'active' : '' }}"
                         data-bg="{{ $cardBg }}" data-border="{{ $cardBorder }}" data-color="{{ $cardColor }}">
                    <div class="summary-card-label">{{ $card['label'] }}</div>
                    <div class="summary-card-count">{{ $card['count'] }}</div>
                    @if($card['count'] > 0)
                        <div class="summary-card-sub">{{ $isActive ? 'Click to clear' : 'Click to filter' }}</div>
                    @endif
                </a>
            @endforeach
        </div>

        {{-- Filter Bar --}}
        <div class="rounded-2xl border mb-4 overflow-hidden" style="background: white; border-color: var(--brand-border);">
            <form method="GET" action="{{ route('admin.job-inbox.index') }}" id="inbox-filter-form">
                <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                    {{-- Search --}}
                    <div class="lg:col-span-2">
                        <input type="text"
                               name="search"
                               id="inbox-search"
                               value="{{ $filters['search'] }}"
                               placeholder="Search job, client, category…"
                               class="w-full rounded-xl border px-3 py-2 text-sm"
                               style="border-color: var(--brand-border); outline: none;">
                    </div>

                    {{-- Status --}}
                    <div>
                        <select name="status" id="inbox-status"
                                class="w-full rounded-xl border px-3 py-2 text-sm"
                                style="border-color: var(--brand-border);">
                            <option value="">All statuses</option>
                            @foreach($filterableStatuses as $s)
                                <option value="{{ $s }}" @selected($filters['status'] === $s)>
                                    {{ $statusLabels[$s] ?? ucwords(str_replace('_', ' ', $s ?? '')) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Client --}}
                    <div>
                        <select name="client_id" id="inbox-client"
                                class="w-full rounded-xl border px-3 py-2 text-sm"
                                style="border-color: var(--brand-border);">
                            <option value="">All clients</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" @selected((string) $filters['client_id'] === (string) $client->id)>
                                    {{ $client->client_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Assignment --}}
                    <div>
                        <select name="assigned" id="inbox-assigned"
                                class="w-full rounded-xl border px-3 py-2 text-sm"
                                style="border-color: var(--brand-border);">
                            <option value="">All assignments</option>
                            <option value="assigned"   @selected($filters['assigned'] === 'assigned')>Assigned</option>
                            <option value="unassigned" @selected($filters['assigned'] === 'unassigned')>Unassigned</option>
                        </select>
                    </div>

                    {{-- Sort --}}
                    <div>
                        <select name="sort" id="inbox-sort"
                                class="w-full rounded-xl border px-3 py-2 text-sm"
                                style="border-color: var(--brand-border);">
                            @foreach($sortOptions as $val => $label)
                                <option value="{{ $val }}" @selected($filters['sort'] === $val)>Sort: {{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="px-4 pb-4 flex flex-wrap items-center gap-3">
                    {{-- Quick toggles --}}
                    <label class="inline-flex items-center gap-2 text-sm cursor-pointer" style="color: var(--brand-ink);">
                        <input type="checkbox"
                               name="due_today"
                               value="1"
                               id="inbox-due-today"
                               @checked($filters['due_today'])
                               class="rounded border-gray-300">
                        Due today
                    </label>

                    <label class="inline-flex items-center gap-2 text-sm cursor-pointer" style="color: var(--brand-ink);">
                        <input type="checkbox"
                               name="overdue_only"
                               value="1"
                               id="inbox-overdue"
                               @checked($filters['overdue_only'])
                               class="rounded border-gray-300">
                        Overdue only
                    </label>

                    <div class="flex gap-2 ml-auto">
                        <button type="submit"
                                class="action-btn-primary action-btn-view px-4 py-2 text-sm">
                            Filter
                        </button>
                        <a href="{{ route('admin.job-inbox.index') }}"
                           class="action-btn-primary action-btn-view px-4 py-2 text-sm">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        {{-- Active Filter Indicator --}}
        @php
            $hasActiveFilters = $filters['status'] || $filters['client_id'] || $filters['assigned']
                || $filters['search'] !== '' || $filters['due_today'] || $filters['overdue_only'];
        @endphp
        @if($hasActiveFilters)
            <div class="mb-3 flex flex-wrap items-center gap-2 text-sm">
                <span style="color: var(--brand-muted);">Active filters:</span>
                @if($filters['status'])
                    <span class="inbox-badge badge-amber">{{ $statusLabels[$filters['status']] ?? ucwords(str_replace('_', ' ', $filters['status'] ?? '')) }}</span>
                @endif
                @if($filters['search'] !== '')
                    <span class="inbox-badge badge-blue">Search: {{ $filters['search'] }}</span>
                @endif
                @if($filters['client_id'])
                    <span class="inbox-badge badge-purple">Client filtered</span>
                @endif
                @if($filters['assigned'])
                    <span class="inbox-badge badge-gray">{{ ucfirst($filters['assigned']) }}</span>
                @endif
                @if($filters['due_today'])
                    <span class="inbox-badge badge-orange">Due today</span>
                @endif
                @if($filters['overdue_only'])
                    <span class="inbox-badge badge-red">Overdue only</span>
                @endif
                <a href="{{ route('admin.job-inbox.index') }}" class="text-xs font-semibold" style="color: #dc2626;">Clear all</a>
            </div>
        @endif

        {{-- Results Table --}}
        <div class="rounded-2xl border overflow-hidden" style="background: white; border-color: var(--brand-border);">
            @if($items->count() === 0)
                <div class="p-16 text-center" style="color: var(--brand-muted);">
                    <div class="text-5xl mb-4">📋</div>
                    <div class="text-lg font-bold mb-1" style="color: var(--brand-ink);">No jobs match these filters</div>
                    <div class="text-sm">Try adjusting your search or clearing filters.</div>
                    <a href="{{ route('admin.job-inbox.index') }}"
                       class="inline-flex mt-4 px-4 py-2 rounded-xl text-sm font-semibold"
                       style="background: var(--brand-dark); color: white;">
                        Clear Filters
                    </a>
                </div>
            @else
                <div class="flex items-center justify-between px-5 py-3 border-b text-sm"
                     style="border-color: var(--brand-border); color: var(--brand-muted);">
                    <span>
                        Showing {{ $items->firstItem() }}–{{ $items->lastItem() }} of {{ $items->total() }} jobs
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full inbox-table">
                        <thead>
                            <tr>
                                <th class="text-left" style="min-width: 200px;">Job / Category</th>
                                <th class="text-left">Client</th>
                                <th class="text-left">Assigned To</th>
                                <th class="text-left">Status</th>
                                <th class="text-left">Due</th>
                                <th class="text-right" style="min-width: 140px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                @php
                                    $isOverdue   = $item->isOverdue();
                                    $isConverted = (bool) $item->project;
                                @endphp
                                <tr>
                                    {{-- Job / Category --}}
                                    <td>
                                        <div class="font-semibold text-sm" style="color: var(--brand-ink);">
                                            {{ $item->serviceCategory?->name ?? $item->title ?? 'Category item' }}
                                        </div>
                                        @if($item->jobRequest?->title)
                                            <div class="text-xs mt-0.5" style="color: var(--brand-muted); max-width: 240px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                {{ $item->jobRequest->title }}
                                            </div>
                                        @endif
                                        @if($isConverted)
                                            <span class="inbox-badge badge-green mt-1">Converted to Project</span>
                                        @endif
                                    </td>

                                    {{-- Client --}}
                                    <td>
                                        <div class="text-sm" style="color: var(--brand-ink);">
                                            {{ $item->jobRequest?->client?->client_name ?? '—' }}
                                        </div>
                                    </td>

                                    {{-- Assigned To --}}
                                    <td>
                                        <div class="text-sm {{ $item->claimer ? 'text-ink' : 'text-muted' }}">
                                            {{ $item->claimer?->name ?? '—' }}
                                        </div>
                                    </td>

                                    {{-- Status --}}
                                    <td>
                                        <span class="inbox-badge {{ $statusBadgeClasses[$item->status] ?? 'badge-gray' }}">
                                            {{ $statusLabels[$item->status] ?? ucwords(str_replace('_', ' ', $item->status ?? '')) }}
                                        </span>
                                    </td>

                                    {{-- Due Date --}}
                                    <td>
                                        @if($item->due_date)
                                            <div class="text-sm {{ $isOverdue ? 'font-semibold due-overdue-text' : 'due-normal-text' }}">
                                                {{ $item->due_date->format('d M Y') }}
                                            </div>
                                            @if($isOverdue)
                                                <div class="text-xs font-semibold due-overdue-text">Overdue</div>
                                            @elseif($item->due_date->isToday())
                                                <div class="text-xs font-semibold due-today-text">Due today</div>
                                            @endif
                                        @else
                                            <span class="text-muted-inline">—</span>
                                        @endif
                                    </td>

                                    {{-- Context-Aware Action --}}
                                    <td>
                                        <div class="flex justify-end gap-2 flex-wrap">
                                            @switch($item->status)
                                                @case('pending_admin_review')
                                                    <a href="{{ route('admin.job-items.show', $item) }}"
                                                       class="action-btn-primary action-btn-review">
                                                        Review Report
                                                    </a>
                                                    @break

                                                @case('pending_assignment')
                                                    <a href="{{ route('admin.job-items.show', $item) }}"
                                                       class="action-btn-primary action-btn-assign">
                                                        Assign
                                                    </a>
                                                    @break

                                                @case('overdue')
                                                    <form method="POST" action="{{ route('admin.job-items.reopen', $item) }}" class="inline">
                                                        @csrf
                                                        <button type="submit"
                                                                class="action-btn-primary action-btn-reopen"
                                                                onclick="return confirm('Reopen this overdue job?')">
                                                            Reopen
                                                        </button>
                                                    </form>
                                                    <a href="{{ route('admin.job-items.show', $item) }}"
                                                       class="action-btn-primary action-btn-view">
                                                        View
                                                    </a>
                                                    @break

                                                @case('approved')
                                                    @if($isConverted)
                                                        <a href="{{ route('admin.projects.show', $item->project) }}"
                                                           class="action-btn-primary action-btn-convert">
                                                            Open Project
                                                        </a>
                                                    @else
                                                        <a href="{{ route('admin.job-items.show', $item) }}"
                                                           class="action-btn-primary action-btn-convert">
                                                            Convert to Project
                                                        </a>
                                                    @endif
                                                    @break

                                                @default
                                                    <a href="{{ route('admin.job-items.show', $item) }}"
                                                       class="action-btn-primary action-btn-view">
                                                        Open Job
                                                    </a>
                                            @endswitch
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($items->hasPages())
                    <div class="px-5 py-4 border-t" style="border-color: var(--brand-border);">
                        {{ $items->links() }}
                    </div>
                @endif
            @endif
        </div>

    </div>
</div>

<script>
    // Auto-submit on select change for a snappier UX
    ['inbox-status', 'inbox-client', 'inbox-assigned', 'inbox-sort'].forEach(function(id) {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', function() {
                document.getElementById('inbox-filter-form').submit();
            });
        }
    });

    // Auto-submit on checkbox change
    ['inbox-due-today', 'inbox-overdue'].forEach(function(id) {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', function() {
                document.getElementById('inbox-filter-form').submit();
            });
        }
    });

    // Apply dynamic summary-card styles set via data-attributes (avoids Blade inlining CSS)
    document.querySelectorAll('.summary-card[data-bg]').forEach(function(el) {
        el.style.background = el.getAttribute('data-bg') || '';
        el.style.borderColor = el.getAttribute('data-border') || '';
        el.style.color = el.getAttribute('data-color') || '';
    });
</script>
@endsection
