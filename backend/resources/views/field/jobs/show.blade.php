@php
    $latestOwnAttempt = $jobItem->attempts->first();
    $isOverdue = $jobItem->isOverdue();
    $displayStatus = $latestOwnAttempt?->status === \App\Models\JobItemAttempt::STATUS_REJECTED
        ? \App\Models\JobItemAttempt::STATUS_REJECTED
        : ($isOverdue ? \App\Models\JobRequestItem::STATUS_OVERDUE : $jobItem->status);
    $reviewNote = null;

    if (in_array($latestOwnAttempt?->status, [\App\Models\JobItemAttempt::STATUS_RETURNED, \App\Models\JobItemAttempt::STATUS_REJECTED], true)) {
        $notes = (string) $latestOwnAttempt->notes;
        $reviewNote = str_contains($notes, 'Admin note:')
            ? trim(\Illuminate\Support\Str::afterLast($notes, 'Admin note:'))
            : (str_contains($notes, 'Coordinator note:')
                ? trim(\Illuminate\Support\Str::afterLast($notes, 'Coordinator note:'))
                : trim($notes));
    }

    $submittedRequirements = $latestOwnAttempt?->requirements ?? collect();
    $submittedMedia = $latestOwnAttempt?->media ?? collect();
    $submittedMediaByChecklist = $submittedMedia->whereNotNull('job_checklist_item_id')->groupBy('job_checklist_item_id');
    $generalSubmittedMedia = $submittedMedia->whereNull('job_checklist_item_id');
    $checklistItems = $jobItem->checklistItems ?? collect();
    $requirementRows = old('requirements', $submittedRequirements->count()
        ? $submittedRequirements->map(fn ($requirement) => [
            'type' => $requirement->type,
            'name' => $requirement->name,
            'quantity' => $requirement->quantity,
            'notes' => $requirement->notes,
        ])->values()->all()
        : [['type' => 'material', 'name' => '', 'quantity' => '', 'notes' => '']]
    );
    $customChecklistRows = old('custom_checklist', [['title' => '', 'status' => 'pending', 'notes' => '']]);
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
                        @if($reviewNote)
                            <div class="admin-note"><strong>Review note:</strong><br>{{ $reviewNote }}</div>
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
                        <label>Checklist</label>
                        @if($checklistItems->isEmpty())
                            <div class="notice locked">No default checklist has been added for this category yet. Add any on-site checklist items below.</div>
                        @else
                            <div class="timeline">
                                @foreach($checklistItems as $checklistItem)
                                    <article class="update">
                                        <div class="grid">
                                            <div>
                                                <div class="label">{{ $checklistItem->is_custom ? 'Added item' : 'Checklist item' }}</div>
                                                <div class="value">{{ $checklistItem->title }}</div>
                                                @if($checklistItem->description)
                                                    <div class="muted">{{ $checklistItem->description }}</div>
                                                @endif
                                            </div>
                                            <div class="form-row">
                                                <label for="checklist_{{ $checklistItem->id }}_status">Status</label>
                                                <select id="checklist_{{ $checklistItem->id }}_status" name="checklist[{{ $checklistItem->id }}][status]" required>
                                                    <option value="pending" @selected(old("checklist.{$checklistItem->id}.status", $checklistItem->status) === 'pending')>Pending</option>
                                                    <option value="done" @selected(old("checklist.{$checklistItem->id}.status", $checklistItem->status) === 'done')>Done</option>
                                                    <option value="not_applicable" @selected(old("checklist.{$checklistItem->id}.status", $checklistItem->status) === 'not_applicable')>Not Applicable</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-row" style="margin-top:10px;">
                                            <label>Response</label>
                                            @php
                                                $inputType = $checklistItem->input_type ?? 'textarea';
                                                $options = collect($checklistItem->options ?? []);
                                                $oldResponse = old("checklist.{$checklistItem->id}.response", $checklistItem->response);
                                                $selectedResponses = is_array($oldResponse) ? $oldResponse : array_map('trim', explode(',', (string) $oldResponse));
                                            @endphp
                                            @if($inputType === 'single_choice' && $options->isNotEmpty())
                                                <select name="checklist[{{ $checklistItem->id }}][response]">
                                                    <option value="">Select response</option>
                                                    @foreach($options as $option)
                                                        <option value="{{ $option }}" @selected((string) $oldResponse === (string) $option)>{{ $option }}</option>
                                                    @endforeach
                                                </select>
                                            @elseif($inputType === 'multi_choice' && $options->isNotEmpty())
                                                <div class="timeline">
                                                    @foreach($options as $option)
                                                        <label class="update" style="display:flex; gap:10px; align-items:center;">
                                                            <input type="checkbox" name="checklist[{{ $checklistItem->id }}][response][]" value="{{ $option }}" @checked(in_array((string) $option, $selectedResponses, true))>
                                                            <span>{{ $option }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @elseif($inputType === 'photo')
                                                <input type="file" name="checklist[{{ $checklistItem->id }}][photos][]" multiple accept="image/*" capture="environment" @required($checklistItem->is_required)>
                                                <div class="muted">Upload or open camera. JPG or PNG, maximum 5 MB each.</div>
                                            @elseif($inputType === 'number')
                                                <input type="number" name="checklist[{{ $checklistItem->id }}][response]" value="{{ $oldResponse }}">
                                            @elseif($inputType === 'text')
                                                <input type="text" name="checklist[{{ $checklistItem->id }}][response]" value="{{ $oldResponse }}">
                                            @else
                                                <textarea name="checklist[{{ $checklistItem->id }}][response]" rows="3">{{ $oldResponse }}</textarea>
                                            @endif
                                        </div>
                                        <div class="form-row" style="margin-top:10px;">
                                            <label for="checklist_{{ $checklistItem->id }}_notes">Checklist Notes</label>
                                            <input id="checklist_{{ $checklistItem->id }}_notes" type="text" name="checklist[{{ $checklistItem->id }}][notes]" value="{{ old("checklist.{$checklistItem->id}.notes", $checklistItem->notes) }}">
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="form-row">
                        <label>Additional Checklist Items</label>
                        <div id="custom-checklist-list">
                            @foreach($customChecklistRows as $index => $item)
                                <div style="border:1px solid var(--border); border-radius:14px; padding:12px; margin-bottom:10px; background:#fff;">
                                    <div class="grid">
                                        <div class="form-row">
                                            <label for="custom_checklist_{{ $index }}_title">Item</label>
                                            <input id="custom_checklist_{{ $index }}_title" type="text" name="custom_checklist[{{ $index }}][title]" value="{{ $item['title'] ?? '' }}" maxlength="255">
                                        </div>
                                        <div class="form-row">
                                            <label for="custom_checklist_{{ $index }}_status">Status</label>
                                            <select id="custom_checklist_{{ $index }}_status" name="custom_checklist[{{ $index }}][status]">
                                                <option value="pending" @selected(($item['status'] ?? 'pending') === 'pending')>Pending</option>
                                                <option value="done" @selected(($item['status'] ?? '') === 'done')>Done</option>
                                                <option value="not_applicable" @selected(($item['status'] ?? '') === 'not_applicable')>Not Applicable</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-row" style="margin-top:10px;">
                                        <label for="custom_checklist_{{ $index }}_notes">Notes</label>
                                        <input id="custom_checklist_{{ $index }}_notes" type="text" name="custom_checklist[{{ $index }}][notes]" value="{{ $item['notes'] ?? '' }}">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button class="button secondary full" type="button" id="add-custom-checklist">Add Checklist Item</button>
                    </div>

                    <div class="form-row">
                        <label>Requirements</label>
                        <div class="muted">Optional. Add materials or extra tasks needed after inspection.</div>
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
                <div class="notice locked">Submitted. Awaiting coordinator review.</div>
            @elseif($jobItem->status === \App\Models\JobRequestItem::STATUS_PENDING_ADMIN_REVIEW)
                <div class="notice locked">Coordinator approved. Awaiting admin final review.</div>
            @elseif($jobItem->status === \App\Models\JobRequestItem::STATUS_APPROVED)
                <div class="notice success">Approved</div>
            @elseif($displayStatus === \App\Models\JobItemAttempt::STATUS_REJECTED)
                <div class="notice error">
                    Rejected
                    @if($reviewNote)
                        <div class="admin-note"><strong>Review note:</strong><br>{{ $reviewNote }}</div>
                    @endif
                </div>
            @else
                <div class="notice">This job is not available for submission.</div>
            @endif
        </section>

        @if($checklistItems->count() || $submittedRequirements->count() || $submittedMedia->count())
            <section class="panel">
                <h2>Latest Submission</h2>
                @if($checklistItems->count())
                    <div class="timeline">
                        @foreach($checklistItems as $checklistItem)
                            @php($itemMedia = $submittedMediaByChecklist->get($checklistItem->id, collect()))
                            <article class="update">
                                <div class="label">Step {{ $loop->iteration }}</div>
                                <div class="value">{{ $checklistItem->title }}</div>
                                @if($checklistItem->description)
                                    <div class="muted">{{ $checklistItem->description }}</div>
                                @endif
                                <div class="muted">Status: {{ str_replace('_', ' ', \Illuminate\Support\Str::title($checklistItem->status)) }}</div>
                                @if($checklistItem->response)
                                    <div class="muted">Response: {{ $checklistItem->response }}</div>
                                @endif
                                @if($checklistItem->notes)
                                    <div class="muted">Note: {{ $checklistItem->notes }}</div>
                                @endif
                                @if($itemMedia->count())
                                    <div class="files" style="margin-top:10px;">
                                        @foreach($itemMedia as $media)
                                            @php($mediaUrl = \App\Support\ImageUrl::url($media->file_path))
                                            <div class="file">
                                                @if($mediaUrl)
                                                    <a href="{{ $mediaUrl }}" target="_blank" rel="noopener">
                                                        <img src="{{ $mediaUrl }}" alt="{{ $media->file_name ?? 'Checklist photo' }}">
                                                        {{ $media->file_name ?? 'Checklist photo' }}
                                                    </a>
                                                @else
                                                    <strong>{{ $media->file_name ?? 'Checklist photo' }}</strong>
                                                @endif
                                                <div class="muted">{{ $media->file_type ?: 'Photo' }} @if($media->file_size) &middot; {{ number_format($media->file_size / 1024, 1) }} KB @endif</div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
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
                @if($generalSubmittedMedia->count())
                    <div class="files">
                        @foreach($generalSubmittedMedia as $media)
                            @php($mediaUrl = \App\Support\ImageUrl::url($media->file_path))
                            <div class="file">
                                @if($mediaUrl)
                                    <a href="{{ $mediaUrl }}" target="_blank" rel="noopener">
                                        <img src="{{ $mediaUrl }}" alt="{{ $media->file_name ?? 'Inspection photo' }}">
                                        {{ $media->file_name ?? 'Inspection photo' }}
                                    </a>
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
        const addCustomChecklistButton = document.getElementById('add-custom-checklist');
        const customChecklistList = document.getElementById('custom-checklist-list');

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

        if (addCustomChecklistButton && customChecklistList) {
            addCustomChecklistButton.addEventListener('click', () => {
                const index = customChecklistList.children.length;
                const wrapper = document.createElement('div');
                wrapper.style.cssText = 'border:1px solid var(--border); border-radius:14px; padding:12px; margin-bottom:10px; background:#fff;';
                wrapper.innerHTML = `
                    <div class="grid">
                        <div class="form-row">
                            <label for="custom_checklist_${index}_title">Item</label>
                            <input id="custom_checklist_${index}_title" type="text" name="custom_checklist[${index}][title]" maxlength="255">
                        </div>
                        <div class="form-row">
                            <label for="custom_checklist_${index}_status">Status</label>
                            <select id="custom_checklist_${index}_status" name="custom_checklist[${index}][status]">
                                <option value="pending">Pending</option>
                                <option value="done">Done</option>
                                <option value="not_applicable">Not Applicable</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row" style="margin-top:10px;">
                        <label for="custom_checklist_${index}_notes">Notes</label>
                        <input id="custom_checklist_${index}_notes" type="text" name="custom_checklist[${index}][notes]">
                    </div>
                `;
                customChecklistList.appendChild(wrapper);
            });
        }
    </script>
</body>
</html>
