@php
    $latestOwnAttempt = $jobItem->attempts->first();
    $isOverdue = $jobItem->isOverdue();
    $displayStatus = $latestOwnAttempt?->status === \App\Models\JobItemAttempt::STATUS_REJECTED
        ? \App\Models\JobItemAttempt::STATUS_REJECTED
        : ($isOverdue ? \App\Models\JobRequestItem::STATUS_OVERDUE : $jobItem->status);
    $adminNote = null;

    if (in_array($latestOwnAttempt?->status, [\App\Models\JobItemAttempt::STATUS_RETURNED, \App\Models\JobItemAttempt::STATUS_REJECTED], true)) {
        $notes = (string) $latestOwnAttempt->notes;
        $adminNote = str_contains($notes, 'Admin note:')
            ? trim(\Illuminate\Support\Str::afterLast($notes, 'Admin note:'))
            : trim($notes);
    }

    $submittedRequirements = $latestOwnAttempt?->requirements ?? collect();
    $submittedMedia = $latestOwnAttempt?->media ?? collect();
    $requirementRows = old('requirements', $submittedRequirements->count()
        ? $submittedRequirements->map(fn ($requirement) => [
            'type' => $requirement->type,
            'name' => $requirement->name,
            'quantity' => $requirement->quantity,
            'notes' => $requirement->notes,
        ])->values()->all()
        : [['type' => 'material', 'name' => '', 'quantity' => '', 'notes' => '']]
    );
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Details</title>
    @include('field.partials.styles')
</head>
<body>
    <main class="app-shell">
        @include('field.partials.header')

        <section class="section" aria-labelledby="job-title">
            <p class="eyebrow">Job Details</p>
            <h1 id="job-title">{{ $jobItem->jobRequest?->title ?? 'Job request unavailable' }}</h1>
            <p class="subtext">{{ $jobItem->serviceCategory?->name ?? 'Service category' }}</p>
        </section>

        @if (session('success'))
            <div class="notice success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="notice error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <section class="panel">
            <div class="grid">
                <div>
                    <div class="label">Client</div>
                    <div class="value">{{ $jobItem->jobRequest?->client?->client_name ?? '-' }}</div>
                </div>
                <div>
                    <div class="label">Category</div>
                    <div class="value">{{ $jobItem->serviceCategory?->name ?? '-' }}</div>
                </div>
                <div>
                    <div class="label">Status</div>
                    <div class="value">
                        <span class="status {{ $displayStatus }}">{{ str_replace('_', ' ', \Illuminate\Support\Str::title($displayStatus)) }}</span>
                    </div>
                </div>
                <div>
                    <div class="label">Due Date</div>
                    <div class="value deadline {{ $isOverdue ? 'overdue' : ($jobItem->due_date?->isToday() ? 'today' : '') }}">
                        {{ $jobItem->due_date?->format('d M Y H:i') ?? '-' }}
                        @if($isOverdue)
                            (overdue)
                        @elseif($jobItem->due_date?->isToday())
                            (due today)
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section class="panel" aria-labelledby="submission-title">
            <h2 id="submission-title">Job Report</h2>

            @if($isOverdue)
                <div class="notice error">Submission deadline exceeded. Contact admin.</div>
            @elseif(in_array($jobItem->status, [\App\Models\JobRequestItem::STATUS_CLAIMED, \App\Models\JobRequestItem::STATUS_RETURNED], true))
                @if($jobItem->status === \App\Models\JobRequestItem::STATUS_RETURNED)
                    <div class="notice locked">
                        This job was returned for updates. Submit the corrected report when ready.
                        @if($adminNote)
                            <div class="admin-note"><strong>Admin note:</strong><br>{{ $adminNote }}</div>
                        @endif
                    </div>
                @endif
                <form method="POST" action="{{ route('field.jobs.submit', $jobItem) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-row">
                        <label for="notes">Inspection Notes</label>
                        <textarea id="notes" name="notes" required minlength="5">{{ old('notes') }}</textarea>
                    </div>
                    <div class="form-row">
                        <label>Requirements</label>
                        <div id="requirements-list">
                            @foreach($requirementRows as $index => $requirement)
                                <div style="border:1px solid var(--border); border-radius:14px; padding:12px; margin-bottom:10px; background:#fff;">
                                    <div class="grid">
                                        <div class="form-row">
                                            <label for="requirements_{{ $index }}_type">Type</label>
                                            <select id="requirements_{{ $index }}_type" name="requirements[{{ $index }}][type]" required>
                                                <option value="material" @selected(($requirement['type'] ?? 'material') === 'material')>Material</option>
                                                <option value="task" @selected(($requirement['type'] ?? '') === 'task')>Task</option>
                                            </select>
                                        </div>
                                        <div class="form-row">
                                            <label for="requirements_{{ $index }}_name">Name</label>
                                            <input id="requirements_{{ $index }}_name" type="text" name="requirements[{{ $index }}][name]" value="{{ $requirement['name'] ?? '' }}" required maxlength="255">
                                        </div>
                                        <div class="form-row">
                                            <label for="requirements_{{ $index }}_quantity">Quantity</label>
                                            <input id="requirements_{{ $index }}_quantity" type="text" name="requirements[{{ $index }}][quantity]" value="{{ $requirement['quantity'] ?? '' }}" maxlength="100">
                                        </div>
                                        <div class="form-row">
                                            <label for="requirements_{{ $index }}_notes">Notes</label>
                                            <input id="requirements_{{ $index }}_notes" type="text" name="requirements[{{ $index }}][notes]" value="{{ $requirement['notes'] ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button class="button secondary full" type="button" id="add-requirement">Add Requirement</button>
                    </div>
                    <div class="form-row">
                        <label for="media">Inspection Photos</label>
                        <input id="media" type="file" name="media[]" multiple accept=".jpg,.jpeg,.png">
                        <div class="muted">JPG or PNG. Maximum 5 MB per photo.</div>
                    </div>
                    <button class="button full" type="submit">Submit Job Report</button>
                </form>
            @elseif($jobItem->status === \App\Models\JobRequestItem::STATUS_SUBMITTED)
                <div class="notice locked">Submitted. Awaiting review.</div>
            @elseif($jobItem->status === \App\Models\JobRequestItem::STATUS_APPROVED)
                <div class="notice success">Approved</div>
            @elseif($displayStatus === \App\Models\JobItemAttempt::STATUS_REJECTED)
                <div class="notice error">
                    Rejected
                    @if($adminNote)
                        <div class="admin-note"><strong>Admin note:</strong><br>{{ $adminNote }}</div>
                    @endif
                </div>
            @else
                <div class="notice">This job is not available for submission.</div>
            @endif
        </section>

        @if($submittedRequirements->count() || $submittedMedia->count())
            <section class="panel">
                <h2>Latest Submission</h2>
                @if($submittedRequirements->count())
                    <div class="timeline">
                        @foreach($submittedRequirements as $requirement)
                            <article class="update">
                                <div class="label">{{ ucfirst($requirement->type) }}</div>
                                <div class="value">{{ $requirement->name }}</div>
                                @if($requirement->quantity)
                                    <div class="muted">Qty: {{ $requirement->quantity }}</div>
                                @endif
                                @if($requirement->notes)
                                    <div class="muted">{{ $requirement->notes }}</div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
                @if($submittedMedia->count())
                    <div class="files">
                        @foreach($submittedMedia as $media)
                            @php($mediaUrl = \App\Support\ImageUrl::url($media->file_path))
                            <div class="file">
                                @if($mediaUrl)
                                    <a href="{{ $mediaUrl }}" target="_blank" rel="noopener">{{ $media->file_name ?? 'Inspection photo' }}</a>
                                @else
                                    <strong>{{ $media->file_name ?? 'Inspection photo' }}</strong>
                                @endif
                                <div class="muted">{{ $media->file_type ?: 'Photo' }} @if($media->file_size) &middot; {{ number_format($media->file_size / 1024, 1) }} KB @endif</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif
    </main>

    @include('field.partials.bottom-nav')
    <script>
        const addRequirementButton = document.getElementById('add-requirement');
        const requirementsList = document.getElementById('requirements-list');

        if (addRequirementButton && requirementsList) {
            addRequirementButton.addEventListener('click', () => {
                const index = requirementsList.children.length;
                const wrapper = document.createElement('div');
                wrapper.style.cssText = 'border:1px solid var(--border); border-radius:14px; padding:12px; margin-bottom:10px; background:#fff;';
                wrapper.innerHTML = `
                    <div class="grid">
                        <div class="form-row">
                            <label for="requirements_${index}_type">Type</label>
                            <select id="requirements_${index}_type" name="requirements[${index}][type]" required>
                                <option value="material">Material</option>
                                <option value="task">Task</option>
                            </select>
                        </div>
                        <div class="form-row">
                            <label for="requirements_${index}_name">Name</label>
                            <input id="requirements_${index}_name" type="text" name="requirements[${index}][name]" required maxlength="255">
                        </div>
                        <div class="form-row">
                            <label for="requirements_${index}_quantity">Quantity</label>
                            <input id="requirements_${index}_quantity" type="text" name="requirements[${index}][quantity]" maxlength="100">
                        </div>
                        <div class="form-row">
                            <label for="requirements_${index}_notes">Notes</label>
                            <input id="requirements_${index}_notes" type="text" name="requirements[${index}][notes]">
                        </div>
                    </div>
                `;
                requirementsList.appendChild(wrapper);
            });
        }
    </script>
</body>
</html>
