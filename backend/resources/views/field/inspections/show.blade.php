<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $inspection->inspection_code }}</title>
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

        @if($errors->any())
            <div class="notice error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
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

        @if(($inspection->review_status ?? 'pending_review') === 'rejected')
            <section class="panel">
                <h2>Review Notes</h2>
                <div class="notice error" style="margin-bottom:0;">
                    {{ $inspection->review_notes ?: 'This report needs correction. Contact an administrator for details.' }}
                </div>
            </section>
        @endif

        <section class="panel">
            <h2>Inspection Report</h2>
            @if($inspection->status === 'completed')
                <div class="muted">This inspection has been submitted. Contact an administrator if changes are needed.</div>
                <div class="form-row" style="margin-top:14px;">
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
                    <div class="form-row">
                        <label for="findings">Findings</label>
                        <textarea id="findings" name="findings" rows="5">{{ old('findings', $inspection->findings) }}</textarea>
                    </div>
                    <div class="form-row">
                        <label for="risks_identified">Risks Identified</label>
                        <textarea id="risks_identified" name="risks_identified" rows="4">{{ old('risks_identified', $inspection->risks_identified) }}</textarea>
                    </div>
                    <div class="form-row">
                        <label for="recommendations">Recommendations</label>
                        <textarea id="recommendations" name="recommendations" rows="4">{{ old('recommendations', $inspection->recommendations) }}</textarea>
                    </div>
                    <div class="form-row">
                        <label for="media">Evidence Files</label>
                        <input id="media" type="file" name="media[]" multiple accept=".jpg,.jpeg,.png,.pdf">
                        <div class="muted">JPG, PNG, or PDF. Maximum 5 MB per file.</div>
                    </div>
                    <button class="button full" type="submit">Submit Report</button>
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
