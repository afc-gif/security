<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Assignment</title>
    @include('field.partials.styles')
    <style>
        /* =========================================================
           Coordinator-specific enhancements within field design system
           ========================================================= */

        /* Section count badge */
        .section-count {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 22px;
            height: 22px;
            padding: 0 6px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 700;
            background: var(--primary);
            color: white;
        }
        .section-count.alert { background: var(--red); }

        /* Sticky review action bar inside each report card */
        .review-action-bar {
            position: sticky;
            bottom: 80px; /* above the bottom-nav */
            z-index: 10;
            background: rgba(255,255,255,0.97);
            backdrop-filter: blur(12px);
            border: 1.5px solid var(--border);
            border-radius: 16px;
            padding: 12px 14px;
            margin-top: 12px;
            box-shadow: 0 -4px 20px rgba(15,23,42,0.10);
        }

        .review-action-bar .action-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .review-actions-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .btn-send-admin {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            text-align: center;
            transition: background 0.15s;
        }
        .btn-send-admin:hover { background: var(--primary-dark); }

        .btn-return-field {
            background: var(--orange-soft);
            color: var(--orange);
            border: 1.5px solid var(--orange);
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            text-align: center;
            transition: all 0.15s;
        }
        .btn-return-field:hover { background: var(--orange); color: white; }

        /* Collapsible report detail */
        .report-collapse-toggle {
            background: none;
            border: none;
            font-size: 13px;
            font-weight: 600;
            color: var(--primary);
            cursor: pointer;
            padding: 4px 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .report-collapse-toggle .chevron {
            display: inline-block;
            transition: transform 0.2s;
        }
        .report-collapse-toggle[aria-expanded="true"] .chevron {
            transform: rotate(90deg);
        }
        .collapsible-content {
            display: none;
        }
        .collapsible-content.open {
            display: block;
        }

        /* Coordinator note textarea */
        .note-field {
            width: 100%;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 13px;
            font-family: inherit;
            resize: vertical;
            margin-top: 6px;
            transition: border-color 0.15s;
        }
        .note-field:focus {
            outline: none;
            border-color: var(--primary);
        }

        .note-hint {
            font-size: 11px;
            color: var(--muted);
            margin-top: 3px;
        }

        /* Job card submitted state */
        .job-card.submitted-card {
            border-left: 3px solid var(--primary);
        }

        /* Report summary block */
        .report-summary {
            background: var(--gray-soft);
            border-radius: 10px;
            padding: 10px 12px;
            margin-top: 10px;
            font-size: 13px;
        }

        .report-summary-row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 4px;
        }
        .report-summary-row:last-child { margin-bottom: 0; }
        .report-summary-key { color: var(--muted); font-size: 12px; }
        .report-summary-val { font-weight: 600; text-align: right; }
    </style>
</head>
<body>
    <main class="app-shell">
        @include('field.partials.header')

        <section class="section" aria-labelledby="coordinator-title">
            <p class="eyebrow">Coordinator</p>
            <h1 id="coordinator-title">Job Assignment</h1>
            <p class="subtext">Assign jobs to field staff and review submitted reports.</p>
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

        {{-- ============================================================
             SECTION 1: REPORTS TO REVIEW (highest priority for coordinator)
             ============================================================ --}}
        <section class="section" aria-labelledby="submitted-reports-title">
            <div class="section-heading">
                <h2 id="submitted-reports-title" style="display: flex; align-items: center; gap: 8px;">
                    Reports to Review
                    @if($submittedJobs->count() > 0)
                        <span class="section-count alert">{{ $submittedJobs->count() }}</span>
                    @endif
                </h2>
            </div>

            @if($submittedJobs->count() === 0)
                <div class="empty-state">No reports are waiting for coordinator review.</div>
            @else
                <div class="job-grid">
                    @foreach($submittedJobs as $job)
                        @php
                            $latestAttempt   = $job->attempts->first();
                            $mediaByChecklist = $latestAttempt?->media?->whereNotNull('job_checklist_item_id')->groupBy('job_checklist_item_id') ?? collect();
                            $generalMedia    = $latestAttempt?->media?->whereNull('job_checklist_item_id') ?? collect();
                            $hasChecklist    = $job->checklistItems->count() > 0;
                            $hasRequirements = ($latestAttempt?->requirements->count() ?? 0) > 0;
                            $hasMedia        = ($latestAttempt?->media->count() ?? 0) > 0;
                            $collapseId      = 'report-detail-' . $job->id;
                            $noteId          = 'coordinator_note_' . $job->id;
                        @endphp

                        <article class="job-card submitted-card">
                            {{-- Job header --}}
                            <div class="job-top">
                                <div>
                                    <h3 class="client-name">{{ $job->jobRequest?->client?->client_name ?? 'Client unavailable' }}</h3>
                                    <p class="job-title">{{ $job->jobRequest?->title ?? 'Job request unavailable' }}</p>
                                </div>
                                <span class="badge submitted">Submitted</span>
                            </div>

                            <span class="category-pill">{{ $job->serviceCategory?->name ?? $job->title ?? 'Service category' }}</span>

                            {{-- Report summary strip --}}
                            <div class="report-summary">
                                <div class="report-summary-row">
                                    <span class="report-summary-key">Field staff</span>
                                    <span class="report-summary-val">{{ $job->claimer?->name ?? '—' }}</span>
                                </div>
                                <div class="report-summary-row">
                                    <span class="report-summary-key">Submitted</span>
                                    <span class="report-summary-val">{{ $job->submitted_at?->format('d M Y H:i') ?? '—' }}</span>
                                </div>
                                @if($latestAttempt?->notes)
                                    <div class="report-summary-row" style="flex-direction: column; gap: 2px;">
                                        <span class="report-summary-key">Notes</span>
                                        <span class="report-summary-val" style="text-align: left; font-weight: 400; font-size: 12px;">{{ $latestAttempt->notes }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Collapsible full report detail --}}
                            @if($hasChecklist || $hasRequirements || $hasMedia)
                                <button class="report-collapse-toggle" type="button"
                                        aria-expanded="false"
                                        aria-controls="{{ $collapseId }}"
                                        onclick="toggleCollapse(this, '{{ $collapseId }}')">
                                    <span class="chevron">▶</span>
                                    View full report detail
                                    ({{ $job->checklistItems->count() }} checklist
                                    · {{ $latestAttempt?->requirements->count() ?? 0 }} requirements
                                    · {{ $latestAttempt?->media->count() ?? 0 }} photos)
                                </button>

                                <div id="{{ $collapseId }}" class="collapsible-content" role="region">

                                    @if($hasChecklist)
                                        <div class="form-row" style="margin-top:10px;">
                                            <strong>Checklist Report</strong>
                                        </div>
                                        <div class="timeline" style="margin-top:8px;">
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
                                                        @if($checklistItem->input_type === 'load_table' && str_starts_with(trim($checklistItem->response), '{') && is_array($tbl = json_decode($checklistItem->response, true)))
                                                            <div class="muted" style="margin-top:6px; overflow-x:auto;">
                                                                <table style="border-collapse:collapse; font-size:12px; width:100%;">
                                                                    <thead><tr style="background:#f1f5f9;">
                                                                        <th style="border:1px solid #d1d5db;padding:4px 6px;text-align:left;">Appliance</th>
                                                                        <th style="border:1px solid #d1d5db;padding:4px 6px;text-align:center;">Qty</th>
                                                                        <th style="border:1px solid #d1d5db;padding:4px 6px;text-align:center;">Power (W)</th>
                                                                        <th style="border:1px solid #d1d5db;padding:4px 6px;text-align:center;">Hrs/Day</th>
                                                                    </tr></thead>
                                                                    <tbody>
                                                                        @foreach($tbl as $appliance => $row)
                                                                            @if(!empty($row['qty']) || !empty($row['power']) || !empty($row['hours']))
                                                                                <tr>
                                                                                    <td style="border:1px solid #d1d5db;padding:4px 6px;font-weight:700;">{{ $appliance }}</td>
                                                                                    <td style="border:1px solid #d1d5db;padding:4px 6px;text-align:center;">{{ $row['qty'] ?? '—' }}</td>
                                                                                    <td style="border:1px solid #d1d5db;padding:4px 6px;text-align:center;">{{ $row['power'] ?? '—' }}</td>
                                                                                    <td style="border:1px solid #d1d5db;padding:4px 6px;text-align:center;">{{ $row['hours'] ?? '—' }}</td>
                                                                                </tr>
                                                                            @endif
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @else
                                                            <div class="muted">Response: {{ $checklistItem->response }}</div>
                                                        @endif
                                                    @endif
                                                    @if($checklistItem->notes)
                                                        <div class="muted">Note: {{ $checklistItem->notes }}</div>
                                                    @endif
                                                    @if($itemMedia->count())
                                                        <div class="files" style="margin-top:8px;">
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

                                    @if($hasRequirements)
                                        <div class="form-row" style="margin-top:10px;">
                                            <strong>Requirements</strong>
                                        </div>
                                        <div class="timeline" style="margin-top:8px;">
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
                                        <div class="form-row" style="margin-top:10px;">
                                            <strong>Photos</strong>
                                        </div>
                                        <div class="files" style="margin-top:8px;">
                                            @foreach($generalMedia as $media)
                                                @php($mediaUrl = \App\Support\ImageUrl::url($media->file_path))
                                                <div class="file">
                                                    @if($mediaUrl)
                                                        <a href="{{ $mediaUrl }}" target="_blank" rel="noopener">
                                                            <img src="{{ $mediaUrl }}" alt="{{ $media->file_name ?? 'Photo' }}">
                                                            {{ $media->file_name ?? 'Photo' }}
                                                        </a>
                                                    @else
                                                        <strong>{{ $media->file_name ?? 'Photo' }}</strong>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                </div>
                            @endif

                            {{-- ============================================================
                                 STICKY REVIEW ACTION BAR — always visible, no need to scroll
                                 ============================================================ --}}
                            <form method="POST"
                                  action="{{ route('coordinator.jobs.review', $job) }}"
                                  id="review-form-{{ $job->id }}">
                                @csrf
                                <div class="review-action-bar">
                                    <div class="action-label">Coordinator Decision</div>

                                    <div class="review-actions-grid" style="margin-bottom: 8px;">
                                        <button class="btn-send-admin"
                                                type="submit"
                                                name="action"
                                                value="approve">
                                            ✓ Send to Admin
                                        </button>
                                        <button class="btn-return-field"
                                                type="submit"
                                                name="action"
                                                value="return"
                                                onclick="return requireNote(this, '{{ $noteId }}')">
                                            ↩ Return to Field
                                        </button>
                                    </div>

                                    <textarea id="{{ $noteId }}"
                                              name="coordinator_note"
                                              class="note-field"
                                              rows="2"
                                              placeholder="Add a coordinator note (required when returning)…">{{ old('coordinator_note') }}</textarea>
                                    <p class="note-hint">Note is required when returning to field staff.</p>
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

        {{-- ============================================================
             SECTION 2: PENDING ASSIGNMENT
             ============================================================ --}}
        <section class="section" aria-labelledby="pending-assignment-title">
            <div class="section-heading">
                <h2 id="pending-assignment-title" style="display: flex; align-items: center; gap: 8px;">
                    Pending Assignment
                    @if($pendingJobs->count() > 0)
                        <span class="section-count">{{ $pendingJobs->count() }}</span>
                    @endif
                </h2>
            </div>

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
                                <span>Due: {{ $job->due_date?->format('d M Y H:i') ?? '—' }}</span>
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
    </main>

    @include('field.partials.bottom-nav')

    @if (session('whatsapp_url'))
        <script>
            window.addEventListener('load', () => {
                const whatsappNotice = document.querySelector('[data-whatsapp-redirect]');
                const whatsappUrl = whatsappNotice?.dataset.whatsappRedirect;
                if (!whatsappUrl) return;
                setTimeout(() => { window.location.href = whatsappUrl; }, 500);
            });
        </script>
    @endif

    <script>
        function toggleCollapse(btn, id) {
            const el = document.getElementById(id);
            const expanded = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', String(!expanded));
            el.classList.toggle('open', !expanded);
            btn.querySelector('.chevron').style.transform = !expanded ? 'rotate(90deg)' : '';
        }

        function requireNote(btn, noteId) {
            const textarea = document.getElementById(noteId);
            if (!textarea || textarea.value.trim() !== '') return true;
            textarea.focus();
            textarea.style.borderColor = 'var(--red)';
            textarea.placeholder = 'A coordinator note is required when returning to field staff.';
            return false;
        }
    </script>
</body>
</html>
