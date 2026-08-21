@extends('layouts.field')

@section('title', 'Inspections | ARTSCI')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-5 shadow-xs">
        <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Inspections</span>
        <h1 class="text-2xl font-extrabold text-slate-900 dark:text-white mt-1">My Inspections</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Open assigned site inspections and submit field reports.</p>
    </div>

    <div class="space-y-3">
        <h2 class="text-sm font-extrabold text-slate-900 dark:text-white uppercase tracking-wider">Assigned Inspections</h2>

        @if($inspections->isEmpty())
            <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-6 text-center text-xs text-slate-400 dark:text-slate-505 font-semibold shadow-xs">
                No inspections assigned yet.
            </div>
        @else
            <div class="space-y-3.5">
                @foreach($inspections as $inspection)
                    <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-2xl p-4 shadow-sm space-y-3">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="text-[9px] font-extrabold uppercase text-slate-400 dark:text-slate-500">{{ $inspection->inspection_code }}</span>
                                <h3 class="text-xs font-bold text-slate-900 dark:text-white mt-0.5">{{ $inspection->title }}</h3>
                                <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium mt-0.5">Client: {{ $inspection->client?->client_name ?? '-' }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase bg-sky-50 dark:bg-sky-955/20 text-sky-700 dark:text-sky-400 border border-sky-100 dark:border-sky-900/50 whitespace-nowrap">
                                {{ str_replace('_', ' ', \Illuminate\Support\Str::title($inspection->status)) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-slate-500 border-t border-slate-50 dark:border-slate-850 pt-2.5">
                            <span class="text-slate-400 dark:text-slate-500">Date: <strong class="text-slate-700 dark:text-slate-300">{{ $inspection->scheduled_date?->format('d M Y H:i') ?? '-' }}</strong></span>
                            <a href="{{ route('field.inspections.show', $inspection) }}" class="text-xs text-indigo-650 dark:text-indigo-400 hover:underline font-extrabold">Open &rarr;</a>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($inspections->hasPages())
                <div class="mt-4">{{ $inspections->links() }}</div>
            @endif
        @endif
    </div>
</div>
@endsection
