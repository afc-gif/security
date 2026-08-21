@extends('layouts.field')

@section('title', $inspection->inspection_code . ' | ARTSCI')

@section('content')
<div class="space-y-6">
    <!-- Header banner -->
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-xs">
        <div class="flex items-center justify-between gap-4">
            <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">{{ $inspection->inspection_code }}</span>
            <span class="px-2.5 py-1 rounded-lg text-[9px] font-extrabold uppercase
                {{ $inspection->status === 'completed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-amber-50 text-amber-700 border border-amber-100' }}">
                {{ str_replace('_', ' ', \Illuminate\Support\Str::title($inspection->status)) }}
            </span>
        </div>
        <h1 class="text-xl font-extrabold text-slate-900 mt-2">{{ $inspection->title }}</h1>
        <p class="text-xs text-slate-500 mt-1">Submit findings and checklist responses from the field.</p>
    </div>

    <!-- Return Alert -->
    @if($inspection->review_status === 'returned' || $inspection->status === 'returned')
        <div class="bg-amber-50 border border-amber-250 rounded-2xl p-4 shadow-sm space-y-3">
            <div class="flex items-center gap-2">
                <span class="text-lg">⚠️</span>
                <h3 class="text-xs font-extrabold text-amber-800 uppercase tracking-wider">Returned for correction</h3>
            </div>
            @if($inspection->return_reason ?: $inspection->review_notes)
                <div class="bg-white p-3 rounded-xl border border-amber-200/50 text-xs italic text-slate-700">
                    "{{ $inspection->return_reason ?: $inspection->review_notes }}"
                </div>
            @endif
            <p class="text-[10px] text-amber-700 leading-relaxed">
                Returned by {{ $inspection->returnedBy?->name ?? 'Admin' }} on {{ $inspection->returned_at?->format('d M Y H:i') ?? '—' }}. Please update your report/checklist responses below and resubmit.
            </p>
        </div>
    @endif

    <!-- Details card -->
    <div class="bg-white border border-slate-100 rounded-2xl p-4 shadow-sm grid grid-cols-2 gap-4 text-xs">
        <div>
            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Client</span>
            <strong class="block text-slate-800 mt-0.5">{{ $inspection->client?->client_name ?? '-' }}</strong>
        </div>
        <div>
            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Scheduled Date</span>
            <strong class="block text-slate-800 mt-0.5">{{ $inspection->scheduled_date?->format('d M Y H:i') ?? '-' }}</strong>
        </div>
        <div>
            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Location</span>
            <strong class="block text-slate-800 mt-0.5 truncate" title="{{ $inspection->location }}">{{ $inspection->location }}</strong>
        </div>
        <div>
            <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Type</span>
            <strong class="block text-slate-800 mt-0.5">{{ $inspection->inspection_type ?: '-' }}</strong>
        </div>
    </div>

    <!-- Report & Checklist Form -->
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm space-y-4">
        <h2 class="text-sm font-extrabold text-slate-950 uppercase tracking-wider border-b border-slate-50 pb-2.5">Inspection Checklist & Report</h2>

        @if($inspection->status === 'completed' && $inspection->review_status === 'approved')
            <div class="p-3.5 bg-emerald-50 border border-emerald-100 text-emerald-800 text-xs font-semibold rounded-xl text-center">
                This inspection report has been reviewed and approved by Admin.
            </div>
            
            <div class="space-y-3 text-xs">
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Findings</span>
                    <p class="text-slate-700 font-medium mt-1 leading-relaxed">{{ $inspection->findings ?: '-' }}</p>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Risks Identified</span>
                    <p class="text-slate-700 font-medium mt-1 leading-relaxed">{{ $inspection->risks_identified ?: '-' }}</p>
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Recommendations</span>
                    <p class="text-slate-700 font-medium mt-1 leading-relaxed">{{ $inspection->recommendations ?: '-' }}</p>
                </div>
            </div>
        @else
            <form method="POST" action="{{ route('field.inspections.submit', $inspection) }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <!-- Checklist Items -->
                @php($checklist = $inspection->effective_checklist_items)
                @if($checklist->count() > 0)
                    <div class="space-y-3">
                        <label class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider">Inspection Checklist</label>
                        
                        <div class="space-y-4">
                            @foreach($checklist as $item)
                                <div class="bg-slate-50/50 border border-slate-100 rounded-2xl p-4 space-y-3">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <span class="text-[9px] font-extrabold uppercase text-slate-400">Checklist Item {{ $loop->iteration }}</span>
                                            <h4 class="text-xs font-bold text-slate-800 mt-0.5">{{ $item->title }}</h4>
                                            @if($item->description)
                                                <p class="text-[10px] text-slate-500 mt-1 leading-relaxed">{{ $item->description }}</p>
                                            @endif
                                        </div>

                                        <div class="shrink-0">
                                            <select name="checklist[{{ $item->id }}][status]" class="text-xs border border-slate-200 rounded-lg px-2.5 py-1 bg-white focus:outline-none focus:border-indigo-500">
                                                <option value="pending" {{ $item->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                <option value="done" {{ $item->status === 'done' ? 'selected' : '' }}>Completed</option>
                                                <option value="not_applicable" {{ $item->status === 'not_applicable' ? 'selected' : '' }}>N/A</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="space-y-1.5 pt-2 border-t border-slate-100">
                                        <label class="block text-[9px] font-bold text-slate-500 uppercase tracking-wider">Response / Answer</label>
                                        <input type="text" name="checklist[{{ $item->id }}][response]" value="{{ old("checklist.{$item->id}.response", $item->response) }}" class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:border-indigo-500" placeholder="Enter findings or values...">
                                    </div>

                                    <div class="space-y-1.5 pt-2">
                                        <label class="block text-[9px] font-bold text-slate-500 uppercase tracking-wider">Notes / Remarks</label>
                                        <textarea name="checklist[{{ $item->id }}][notes]" rows="2" class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white focus:outline-none focus:border-indigo-500" placeholder="Additional observations...">{{ old("checklist.{$item->id}.notes", $item->notes) }}</textarea>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="space-y-1.5">
                    <label for="findings" class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider">Findings *</label>
                    <textarea id="findings" name="findings" rows="4" required class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:border-indigo-500" placeholder="Describe main inspection findings...">{{ old('findings', $inspection->findings) }}</textarea>
                </div>

                <div class="space-y-1.5">
                    <label for="risks_identified" class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider">Risks Identified</label>
                    <textarea id="risks_identified" name="risks_identified" rows="3" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:border-indigo-500" placeholder="Identify hazards or safety risks...">{{ old('risks_identified', $inspection->risks_identified) }}</textarea>
                </div>

                <div class="space-y-1.5">
                    <label for="recommendations" class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider">Recommendations</label>
                    <textarea id="recommendations" name="recommendations" rows="3" class="w-full border border-slate-200 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:border-indigo-500" placeholder="Suggested corrective actions...">{{ old('recommendations', $inspection->recommendations) }}</textarea>
                </div>

                <div class="space-y-1.5">
                    <label for="media" class="block text-[10px] font-bold text-slate-700 uppercase tracking-wider">Evidence Files / Photos</label>
                    <input id="media" type="file" name="media[]" multiple accept=".jpg,.jpeg,.png,.pdf" class="w-full text-xs border border-slate-200 rounded-lg px-3 py-2 bg-white focus:outline-none">
                    <div class="text-[9px] text-slate-400 mt-1">JPG, PNG, or PDF. Maximum 5 MB per file.</div>
                </div>
                
                <button type="submit" class="w-full inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs py-3 px-4 rounded-xl transition-all shadow-md mt-2">
                    {{ $inspection->status === 'returned' || $inspection->review_status === 'returned' ? 'Resubmit Report to Admin' : 'Submit Report' }}
                </button>
            </form>
        @endif
    </div>

    <!-- Uploaded Files panel -->
    <div class="bg-white border border-slate-100 rounded-2xl p-5 shadow-sm space-y-4">
        <h2 class="text-sm font-extrabold text-slate-900 uppercase tracking-wider border-b border-slate-50 pb-2.5">Uploaded Files</h2>
        
        @if($inspection->media->count() === 0)
            <div class="text-xs text-slate-400 text-center py-4 font-semibold">
                No files uploaded yet.
            </div>
        @else
            <div class="grid grid-cols-2 gap-2">
                @foreach($inspection->media as $media)
                    @php($mediaUrl = \App\Support\ImageUrl::url($media->file_path))
                    <div class="bg-slate-50 border border-slate-100 rounded-xl p-2.5 flex items-center gap-2 min-w-0">
                        <span class="text-base">📄</span>
                        <div class="min-w-0 flex-1 text-[9px]">
                            @if($mediaUrl)
                                <a href="{{ $mediaUrl }}" target="_blank" rel="noopener" class="text-indigo-600 font-bold truncate block hover:underline">{{ $media->file_name ?? basename($media->file_path) }}</a>
                            @else
                                <span class="font-bold text-slate-700 truncate block">{{ $media->file_name ?? basename($media->file_path) }}</span>
                            @endif
                            <span class="text-slate-400 block mt-0.5">{{ number_format($media->file_size / 1024, 1) }} KB</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
