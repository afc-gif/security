<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Assignment</title>
    @include('field.partials.styles')
</head>
<body>
    <main class="app-shell">
        @include('field.partials.header')

        <section class="section" aria-labelledby="assignment-title">
            <p class="eyebrow">Coordinator</p>
            <h1 id="assignment-title">Job Assignment</h1>
            <p class="subtext">Assign new job requests to field staff or release them for open claim.</p>
        </section>

        @if (session('success'))
            <div class="notice success">{{ session('success') }}</div>
        @endif

        @if (session('whatsapp_url'))
            <div class="notice locked" data-whatsapp-redirect="{{ session('whatsapp_url') }}">
                Opening WhatsApp with the admin transport fare message.
                <div style="margin-top:10px;">
                    <a class="button full" href="{{ session('whatsapp_url') }}" target="_blank" rel="noopener">
                        Open WhatsApp Manually
                    </a>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="notice error">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <section class="section" aria-labelledby="pending-assignment-title">
            <h2 id="pending-assignment-title">Pending Assignment</h2>
            @if($pendingJobs->count() === 0)
                <div class="empty-state">No jobs are waiting for assignment.</div>
            @else
                <div class="job-grid">
                    @foreach($pendingJobs as $job)
                        <article class="job-card">
                            <div class="job-top">
                                <div>
                                    <h3 class="client-name">{{ $job->jobRequest?->client?->client_name ?? 'Client unavailable' }}</h3>
                                    <p class="job-title">{{ $job->jobRequest?->title ?? 'Job request unavailable' }}</p>
                                </div>
                                <span class="badge {{ $job->status }}">{{ str_replace('_', ' ', \Illuminate\Support\Str::title($job->status)) }}</span>
                            </div>

                            <span class="category-pill">{{ $job->serviceCategory?->name ?? $job->title ?? 'Service category' }}</span>

                            <div class="job-meta">
                                <span>Due: {{ $job->due_date?->format('d M Y H:i') ?? '-' }}</span>
                            </div>

                            @if($job->checklistItems->count())
                                <div class="timeline" style="margin-top:10px;">
                                    @foreach($job->checklistItems->take(4) as $checklistItem)
                                        <article class="update">
                                            <div class="label">{{ $checklistItem->is_custom ? 'Added checklist item' : 'Checklist item' }}</div>
                                            <div class="value">{{ $checklistItem->title }}</div>
                                            <form method="POST" action="{{ route('coordinator.jobs.checklist.destroy', [$job, $checklistItem]) }}" style="margin-top:8px;">
                                                @csrf
                                                @method('DELETE')
                                                <button class="card-button secondary" type="submit" onclick="return confirm('Remove this checklist item from this job?')">Remove Item</button>
                                            </form>
                                        </article>
                                    @endforeach
                                    @if($job->checklistItems->count() > 4)
                                        <div class="muted">+{{ $job->checklistItems->count() - 4 }} more checklist items</div>
                                    @endif
                                </div>
                            @else
                                <div class="notice locked" style="margin-top:10px;">No checklist items yet.</div>
                            @endif

                            <form method="POST" action="{{ route('coordinator.jobs.checklist.store', $job) }}" class="form-row" style="margin-top:10px;">
                                @csrf
                                <label for="checklist_title_{{ $job->id }}">Add Checklist Item</label>
                                <input id="checklist_title_{{ $job->id }}" type="text" name="title" placeholder="Extra item for this job" maxlength="255" required>
                                <input type="text" name="description" placeholder="Optional note or instruction">
                                <button class="card-button secondary" type="submit">Add Item</button>
                            </form>

                            <form method="POST" action="{{ route('coordinator.jobs.assign', $job) }}" class="form-row">
                                @csrf
                                <label for="assigned_to_{{ $job->id }}">Assign to</label>
                                <select id="assigned_to_{{ $job->id }}" name="assigned_to" required>
                                    <option value="">Select staff</option>
                                    @foreach($fieldStaff as $staff)
                                        <option value="{{ $staff->id }}">
                                            {{ $staff->name }}{{ $staff->role === 'field_coordinator' ? ' (Coordinator)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <button class="card-button" type="submit">Assign Job</button>
                            </form>

                            <form method="POST" action="{{ route('coordinator.jobs.claim', $job) }}" style="margin-top:10px;">
                                @csrf
                                <button class="card-button secondary" type="submit">Assign to Me</button>
                            </form>

                            <form method="POST" action="{{ route('coordinator.jobs.release', $job) }}" style="margin-top:10px;">
                                @csrf
                                <button class="card-button secondary" type="submit">Release for Claim</button>
                            </form>
                        </article>
                    @endforeach
                </div>

                <div class="pagination">
                    {{ $pendingJobs->links() }}
                </div>
            @endif
        </section>

        <section class="section" aria-labelledby="submitted-reports-title">
            <h2 id="submitted-reports-title">Submitted Reports</h2>
            @if($submittedJobs->count() === 0)
                <div class="empty-state">No reports are waiting for coordinator review.</div>
            @else
                <div class="job-grid">
                    @foreach($submittedJobs as $job)
                        @php
                            $latestAttempt = $job->attempts->first();
                            $mediaByChecklist = $latestAttempt?->media?->whereNotNull('job_checklist_item_id')->groupBy('job_checklist_item_id') ?? collect();
                            $generalMedia = $latestAttempt?->media?->whereNull('job_checklist_item_id') ?? collect();
                        @endphp
                        <article class="job-card">
                            <div class="job-top">
                                <div>
                                    <h3 class="client-name">{{ $job->jobRequest?->client?->client_name ?? 'Client unavailable' }}</h3>
                                    <p class="job-title">{{ $job->jobRequest?->title ?? 'Job request unavailable' }}</p>
                                </div>
                                <span class="badge {{ $job->status }}">{{ str_replace('_', ' ', \Illuminate\Support\Str::title($job->status)) }}</span>
                            </div>

                            <span class="category-pill">{{ $job->serviceCategory?->name ?? $job->title ?? 'Service category' }}</span>

                            <div class="meta">
                                <div>Field staff: {{ $job->claimer?->name ?? '-' }}</div>
                                <div>Submitted: {{ $job->submitted_at?->format('d M Y H:i') ?? '-' }}</div>
                            </div>

                            @if($latestAttempt)
                                <div class="form-row" style="margin-top:10px;">
                                    <strong>Notes</strong>
                                    <div>{{ $latestAttempt->notes ?: '-' }}</div>
                                </div>

                                @if($job->checklistItems->count())
                                    <div class="form-row" style="margin-top:10px;">
                                        <strong>Checklist Report</strong>
                                    </div>
                                    <div class="timeline" style="margin-top:10px;">
                                        @foreach($job->checklistItems as $checklistItem)
                                            @php($itemMedia = $mediaByChecklist->get($checklistItem->id, collect()))
                                            <article class="update">
                                                <div class="label">Step {{ $loop->iteration }}</div>
                                                <div class="value">{{ $checklistItem->title }}</div>
                                                @if($checklistItem->description)
                                                    <div class="muted">{{ $checklistItem->description }}</div>
                                                @endif
                                                <div class="muted">
                                                    Status: {{ str_replace('_', ' ', \Illuminate\Support\Str::title($checklistItem->status)) }}
                                                    @if($checklistItem->completedBy)
                                                        &middot; Completed by {{ $checklistItem->completedBy->name }}
                                                    @endif
                                                </div>
                                                @if($checklistItem->response)
                                                    <div class="muted">Response: {{ $checklistItem->response }}</div>
                                                @endif
                                                @if($checklistItem->notes)
                                                    <div class="muted">Note: {{ $checklistItem->notes }}</div>
                                                @endif
                                                @if($checklistItem->addedBy)
                                                    <div class="muted">Added by {{ $checklistItem->addedBy->name }}</div>
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
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </article>
                                        @endforeach
                                    </div>
                                @endif

                                @if($latestAttempt->requirements->count())
                                    <div class="form-row" style="margin-top:10px;">
                                        <strong>Requirements</strong>
                                    </div>
                                    <div class="timeline" style="margin-top:10px;">
                                        @foreach($latestAttempt->requirements as $requirement)
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

                                @if($generalMedia->count())
                                    <div class="files" style="margin-top:10px;">
                                        @foreach($generalMedia as $media)
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
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            @endif

                            <form method="POST" action="{{ route('coordinator.jobs.review', $job) }}" style="margin-top:12px;">
                                @csrf
                                <div class="form-row">
                                    <label for="coordinator_note_{{ $job->id }}">Coordinator Note</label>
                                    <textarea id="coordinator_note_{{ $job->id }}" name="coordinator_note" rows="3">{{ old('coordinator_note') }}</textarea>
                                    <div class="muted">Required when returning for correction.</div>
                                </div>
                                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
                                    <button class="card-button" type="submit" name="action" value="approve">Send to Admin</button>
                                    <button class="card-button secondary" type="submit" name="action" value="return">Return</button>
                                </div>
                            </form>
                        </article>
                    @endforeach
                </div>

                <div class="pagination">
                    {{ $submittedJobs->links() }}
                </div>
            @endif
        </section>
    </main>

    @include('field.partials.bottom-nav')
    @if (session('whatsapp_url'))
        <script>
            window.addEventListener('load', () => {
                const whatsappNotice = document.querySelector('[data-whatsapp-redirect]');
                const whatsappUrl = whatsappNotice?.dataset.whatsappRedirect;

                if (!whatsappUrl) {
                    return;
                }

                setTimeout(() => {
                    window.location.href = whatsappUrl;
                }, 500);
            });
        </script>
    @endif
</body>
</html>
