@extends('layouts.field')

@section('title', 'Inspections | ARTSCI')

@section('content')
<div class="space-y-6">
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-xs">
        <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Inspections</span>
        <h1 class="text-2xl font-extrabold text-slate-900 mt-1">My Inspections</h1>
        <p class="text-xs text-slate-500 mt-1">Open assigned site inspections and submit field reports.</p>
    </div>

    <div class="space-y-3">
        <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">Assigned Inspections</h2>

        @if($inspections->isEmpty())
            <div class="bg-white border border-slate-100 rounded-2xl p-6 text-center text-xs text-slate-400 font-semibold shadow-xs">
                No inspections assigned yet.
            </div>
        @else
            <div class="space-y-3.5">
                @foreach($inspections as $inspection)
                    <div class="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm space-y-3">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <span class="text-[9px] font-extrabold uppercase text-slate-400">{{ $inspection->inspection_code }}</span>
                                <h3 class="text-xs font-bold text-slate-900 mt-0.5">{{ $inspection->title }}</h3>
                                <p class="text-[10px] text-slate-400 font-medium mt-0.5">Client: {{ $inspection->client?->client_name ?? '-' }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase bg-sky-50 text-sky-700 border border-sky-100 whitespace-nowrap">
                                {{ str_replace('_', ' ', \Illuminate\Support\Str::title($inspection->status)) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-slate-500 border-t border-slate-50 pt-2.5">
                            <span>Date: <strong class="text-slate-700">{{ $inspection->scheduled_date?->format('d M Y H:i') ?? '-' }}</strong></span>
                            <a href="{{ route('field.inspections.show', $inspection) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-extrabold">Open &rarr;</a>
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
