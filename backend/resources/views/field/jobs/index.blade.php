@extends('layouts.field')

@section('title', 'Field Jobs | ARTSCI')

@section('content')
<div class="space-y-6">
    <!-- Header banner -->
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-xs">
        <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Jobs Board</span>
        <h1 class="text-2xl font-extrabold text-slate-900 mt-1">Field Jobs</h1>
        <p class="text-xs text-slate-500 mt-1">Claim available category items and submit active job reports.</p>
    </div>

    <!-- Available Jobs -->
    <div class="space-y-3">
        <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">Available Jobs</h2>
        
        @if($availableJobs->isEmpty())
            <div class="bg-white border border-slate-100 rounded-2xl p-6 text-center text-xs text-slate-400 font-semibold shadow-xs">
                No available jobs right now.
            </div>
        @else
            <div class="space-y-3.5">
                @foreach($availableJobs as $job)
                    <div class="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm space-y-3">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-xs font-bold text-slate-900">{{ $job->jobRequest?->title ?? 'Job Request' }}</h3>
                                <p class="text-[10px] text-slate-400 font-medium mt-0.5">{{ $job->jobRequest?->client?->client_name ?? 'Client name unavailable' }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase bg-emerald-50 text-emerald-700 border border-emerald-100 whitespace-nowrap">
                                Open
                            </span>
                        </div>
                        
                        <div class="flex flex-wrap gap-1.5">
                            <span class="px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg text-[9px] font-bold">
                                {{ $job->serviceCategory?->name ?? 'Service Category' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-[10px] text-slate-500 border-t border-slate-50 pt-2.5">
                            <span>Due: <strong class="text-slate-700">{{ $job->due_date?->format('d M Y H:i') ?? '-' }}</strong></span>
                            <form method="POST" action="{{ route('field.jobs.claim', $job) }}" onsubmit="return confirm('Do you want to claim this job?');">
                                @csrf
                                <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800 font-extrabold">
                                    Claim Job &rarr;
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($availableJobs->hasPages())
                <div class="mt-4">
                    {{ $availableJobs->links() }}
                </div>
            @endif
        @endif
    </div>

    <!-- My Claims -->
    <div class="space-y-3">
        <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider">My Claimed Jobs</h2>
        
        @if($myJobs->isEmpty())
            <div class="bg-white border border-slate-100 rounded-2xl p-6 text-center text-xs text-slate-400 font-semibold shadow-xs">
                You have not claimed any jobs yet.
            </div>
        @else
            <div class="space-y-3.5">
                @foreach($myJobs as $job)
                    @php
                        $latestOwnAttempt = $job->attempts->first();
                        $displayStatus = $latestOwnAttempt?->status === \App\Models\JobItemAttempt::STATUS_REJECTED
                            ? \App\Models\JobItemAttempt::STATUS_REJECTED
                            : ($job->isOverdue() ? \App\Models\JobRequestItem::STATUS_OVERDUE : $job->status);
                        $isOverdue = $job->isOverdue();
                    @endphp
                    <div class="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm space-y-3">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h3 class="text-xs font-bold text-slate-900">{{ $job->jobRequest?->title ?? 'Job Request' }}</h3>
                                <p class="text-[10px] text-slate-400 font-medium mt-0.5">{{ $job->jobRequest?->client?->client_name ?? 'Client name' }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-[9px] font-extrabold uppercase whitespace-nowrap
                                {{ $isOverdue ? 'bg-rose-50 text-rose-700 border border-rose-100' : 'bg-indigo-50 text-indigo-700 border border-indigo-100' }}">
                                {{ str_replace('_', ' ', \Illuminate\Support\Str::title($displayStatus)) }}
                            </span>
                        </div>

                        <div class="flex flex-wrap gap-1.5">
                            <span class="px-2.5 py-1 bg-slate-100 text-slate-700 rounded-lg text-[9px] font-bold">
                                {{ $job->serviceCategory?->name ?? 'Service Category' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-[10px] text-slate-500 border-t border-slate-50 pt-2.5">
                            <span class="{{ $isOverdue ? 'text-rose-600 font-semibold' : '' }}">
                                Due: {{ $job->due_date?->format('d M Y H:i') ?? '-' }}
                                @if($isOverdue)
                                    (overdue)
                                @elseif($job->due_date?->isToday())
                                    (today)
                                @endif
                            </span>
                            <a href="{{ route('field.jobs.show', $job) }}" class="text-xs text-indigo-600 hover:text-indigo-800 font-extrabold">
                                Open &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($myJobs->hasPages())
                <div class="mt-4">
                    {{ $myJobs->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
