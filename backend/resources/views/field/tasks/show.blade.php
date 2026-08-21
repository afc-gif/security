@extends('layouts.field')

@section('title', $task->title . ' | ARTSCI')

@section('content')
<div class="space-y-6">
    <!-- Header banner -->
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-xs">
        <div class="flex items-center justify-between gap-4">
            <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Task Details</span>
            <span class="px-2.5 py-1 rounded-lg text-[9px] font-extrabold uppercase
                {{ strtolower($task->status ?? '') === 'completed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-amber-50 text-amber-700 border border-amber-100' }}">
                {{ str_replace('_', ' ', \Illuminate\Support\Str::title($task->status)) }}
            </span>
        </div>
        <h1 class="text-xl font-extrabold text-slate-900 mt-2">{{ $task->title }}</h1>
        <p class="text-xs text-slate-500 mt-1">{{ $linkedType }}: {{ $linkedCode ?? 'N/A' }}</p>
    </div>

    <!-- Details grid -->
    <div class="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm grid grid-cols-2 gap-4 text-xs">
        <div>
            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Priority</span>
            <strong class="block text-slate-800 mt-0.5">{{ $task->priority ? ucfirst($task->priority) : '-' }}</strong>
        </div>
        <div>
            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Due Date</span>
            <strong class="block text-slate-800 mt-0.5">{{ $task->due_date?->format('d M Y H:i') ?? '-' }}</strong>
        </div>
        @if($task->completed_at)
            <div class="col-span-2 border-t border-slate-50 pt-2.5">
                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Completed At</span>
                <strong class="block text-slate-800 mt-0.5">{{ $task->completed_at->format('d M Y H:i') }}</strong>
            </div>
        @endif
    </div>

    <!-- Description -->
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm space-y-3">
        <h2 class="text-sm font-extrabold text-slate-950 uppercase tracking-wider border-b border-slate-50 pb-2.5">Description</h2>
        <p class="text-xs text-slate-600 leading-relaxed">{{ $task->description ?: 'No description provided.' }}</p>
    </div>

    <!-- Linked record info -->
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm space-y-3">
        <h2 class="text-sm font-extrabold text-slate-950 uppercase tracking-wider border-b border-slate-50 pb-2.5">Linked {{ $linkedType }}</h2>
        <div class="text-xs">
            <strong class="text-slate-800 text-sm block">{{ $linkedCode ?? 'Linked record unavailable' }}</strong>
            <span class="text-slate-500 block mt-1 leading-relaxed">{{ $linkedTitle ?? 'Linked record details unavailable.' }}</span>
        </div>
    </div>

    <!-- Update status -->
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm space-y-4">
        <h2 class="text-sm font-extrabold text-slate-950 uppercase tracking-wider border-b border-slate-50 pb-2.5">Update Status</h2>
        
        <form method="POST" action="{{ route('field.tasks.update-status', $task) }}" class="space-y-4">
            @csrf
            @method('PATCH')
            
            <div class="space-y-1.5">
                <label for="status" class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider">Status *</label>
                <select id="status" name="status" required class="w-full text-xs border border-slate-200 rounded-xl px-3 py-2.5 bg-white focus:outline-none focus:border-indigo-500">
                    <option value="pending" @selected(old('status', $task->status) === 'pending')>Pending</option>
                    <option value="in_progress" @selected(old('status', $task->status) === 'in_progress')>In Progress</option>
                    <option value="completed" @selected(old('status', $task->status) === 'completed')>Completed</option>
                </select>
            </div>

            <button type="submit" class="w-full inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-3 px-4 rounded-xl transition-all shadow-md">
                Update Task Status
            </button>
        </form>
    </div>
</div>
@endsection
