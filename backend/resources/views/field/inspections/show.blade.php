<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $inspection->inspection_code }} | Field Console</title>
    @include('field.partials.styles')
</head>
<body>
    <main class="app-shell">
        @include('field.partials.header')

        <section class="section" aria-labelledby="inspection-title">
            <p class="eyebrow">Inspection Details</p>
            <h1 id="inspection-title">{{ $inspection->inspection_code }}</h1>
            <p class="subtext">{{ $inspection->title }}</p>
        </section>

        @if (session('success'))
            <div class="notice success">{{ session('success') }}</div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="notice error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        @if($inspection->review_status === 'returned' || $inspection->status === 'returned')
            <section class="panel" style="border: 2px solid #f59e0b; background-color: #fffbeb;">
                <h2 style="color: #b45309; margin-bottom: 8px;">⚠️ Returned for Additional Details</h2>
                <div class="notice error" style="background-color: #fef3c7; color: #92400e; border-color: #fcd34d; margin-bottom: 12px;">
                    <strong>Admin Reason:</strong> {{ $inspection->return_reason ?: $inspection->review_notes }}
                </div>
                <p class="muted" style="color: #78350f;">
                    Returned by {{ $inspection->returnedBy?->name ?? 'Admin' }} on {{ $inspection->returned_at?->format('d M Y H:i') ?? '—' }}. Please update your report/checklist responses below and resubmit.
                </p>
            </section>
        @endif

        <section class="panel">
            <h2>Inspection Details</h2>
            <div class="grid">
                <div>
                    <div class="label">Client</div>
                    <div class="value">{{ $inspection->client?->client_name ?? '-' }}</div>
                </div>
                <div>
                    <div class="label">Status</div>
                    <div class="value">
                        <span class="status {{ $inspection->status }}">{{ str_replace('_', ' ', \Illuminate\Support\Str::title($inspection->status)) }}</span>
                    </div>
                </div>
                <div>
                    <div class="label">Location</div>
                    <div class="value">{{ $inspection->location }}</div>
                </div>
                <div>
                    <div class="label">Scheduled Date</div>
                    <div class="value">{{ $inspection->scheduled_date?->format('d M Y H:i') ?? '-' }}</div>
                </div>
                <div>
                    <div class="label">Inspection Type</div>
                    <div class="value">{{ $inspection->inspection_type ?: '-' }}</div>
                </div>
                <div>
                    <div class="label">Priority</div>
                    <div class="value">{{ $inspection->priority ? ucfirst($inspection->priority) : '-' }}</div>
                </div>
                <div>
                    <div class="label">Review Status</div>
                    @php($reviewStatus = $inspection->review_status ?? 'pending_review')
                    <div class="value">
                        <span class="status {{ $reviewStatus }}">{{ str_replace('_', ' ', \Illuminate\Support\Str::title($reviewStatus)) }}</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="panel">
            <h2>Inspection Report & Checklist</h2>
            @if($inspection->status === 'completed' && $inspection->review_status === 'approved')
                <div class="notice success" style="margin-bottom: 16px;">This inspection report has been reviewed and approved by Admin.</div>
                <div class="form-row">
                    <label>Findings</label>
                    <div>{{ $inspection->findings ?: '-' }}</div>
                </div>
                <div class="form-row">
                    <label>Risks Identified</label>
                    <div>{{ $inspection->risks_identified ?: '-' }}</div>
                </div>
                <div class="form-row">
                    <label>Recommendations</label>
                    <div>{{ $inspection->recommendations ?: '-' }}</div>
                </div>
            @else
                <form method="POST" action="{{ route('field.inspections.submit', $inspection) }}" enctype="multipart/form-data">
                    @csrf

                    <!-- Checklist Items -->
                    @php($checklist = $inspection->effective_checklist_items)
                    @if($checklist->count() > 0)
                        <div style="margin-bottom: 24px;">
                            <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 12px;">Inspection Checklist</h3>
                            <div style="display: flex; flex-direction: column; gap: 16px;">
                                @foreach($checklist as $item)
                                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 14px; border-radius: 8px;">
                                        <div style="font-weight: 700; color: #0f172a; margin-bottom: 4px;">
                                            {{ $loop->iteration }}. {{ $item->title }}
                                        </div>
                                        @if($item->description)
                                            <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 8px;">{{ $item->description }}</div>
                                        @endif

                                        <div class="form-row" style="margin-bottom: 8px;">
                                            <label style="font-size: 0.8rem;">Status</label>
                                            <select name="checklist[{{ $item->id }}][status]">
                                                <option value="pending" {{ $item->status === 'pending' ? 'selected' : '' }}>Pending / Incomplete</option>
                                                <option value="done" {{ $item->status === 'done' ? 'selected' : '' }}>Completed / Verified</option>
                                                <option value="not_applicable" {{ $item->status === 'not_applicable' ? 'selected' : '' }}>Not Applicable</option>
                                            </select>
                                        </div>

                                        <div class="form-row" style="margin-bottom: 8px;">
                                            <label style="font-size: 0.8rem;">Response / Answer</label>
                                            <input type="text" name="checklist[{{ $item->id }}][response]" value="{{ old("checklist.{$item->id}.response", $item->response) }}" placeholder="Enter answer or findings...">
                                        </div>

                                        <div class="form-row" style="margin-bottom: 0;">
                                            <label style="font-size: 0.8rem;">Notes / Remarks</label>
                                            <textarea name="checklist[{{ $item->id }}][notes]" rows="2" placeholder="Additional details or measurements...">{{ old("checklist.{$item->id}.notes", $item->notes) }}</textarea>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="form-row">
                        <label for="findings">Findings</label>
                        <textarea id="findings" name="findings" rows="4" placeholder="Describe main inspection findings...">{{ old('findings', $inspection->findings) }}</textarea>
                    </div>
                    <div class="form-row">
                        <label for="risks_identified">Risks Identified</label>
                        <textarea id="risks_identified" name="risks_identified" rows="3" placeholder="Identify hazards or safety risks...">{{ old('risks_identified', $inspection->risks_identified) }}</textarea>
                    </div>
                    <div class="form-row">
                        <label for="recommendations">Recommendations</label>
                        <textarea id="recommendations" name="recommendations" rows="3" placeholder="Suggested corrective actions...">{{ old('recommendations', $inspection->recommendations) }}</textarea>
                    </div>
                    <div class="form-row">
                        <label for="media">Evidence Files / Photos</label>
                        <input id="media" type="file" name="media[]" multiple accept=".jpg,.jpeg,.png,.pdf">
                        <div class="muted">JPG, PNG, or PDF. Maximum 5 MB per file.</div>
                    </div>
                    
                    <button class="button full" type="submit">
                        {{ $inspection->status === 'returned' || $inspection->review_status === 'returned' ? 'Resubmit Report to Admin' : 'Submit Report' }}
                    </button>
                </form>
            @endif
        </section>

        <section class="panel">
            <h2>Uploaded Files</h2>
            @if($inspection->media->count() === 0)
                <div class="muted">No files uploaded yet.</div>
            @else
                <div class="files">
                    @foreach($inspection->media as $media)
                        <div class="file">
                            @php($mediaUrl = \App\Support\ImageUrl::url($media->file_path))
                            @if($mediaUrl)
                                <a href="{{ $mediaUrl }}" target="_blank" rel="noopener">{{ $media->file_name ?? basename($media->file_path) }}</a>
                            @else
                                <strong>{{ $media->file_name ?? basename((string) $media->file_path) }}</strong>
                                <div class="muted">File unavailable</div>
                            @endif
                            <div class="muted">{{ $media->file_type ?: 'File' }} @if($media->file_size) &middot; {{ number_format($media->file_size / 1024, 1) }} KB @endif</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </main>

    @include('field.partials.bottom-nav')
</body>
</html>
