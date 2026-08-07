<style>
    :root {
        color-scheme: light;
        --primary: #0b4aa2;
        --primary-dark: #07316c;
        --primary-soft: #eaf2ff;
        --ink: #101828;
        --muted: #667085;
        --border: #d9e2ec;
        --surface: #ffffff;
        --page: #f4f7fb;
        --green: #15803d;
        --green-soft: #dcfce7;
        --yellow: #a16207;
        --yellow-soft: #fef3c7;
        --orange: #c2410c;
        --orange-soft: #ffedd5;
        --red: #b42318;
        --red-soft: #fee4e2;
        --gray-soft: #eef2f6;
        --shadow: 0 14px 34px rgba(15, 23, 42, 0.08);
    }

    * { box-sizing: border-box; }

    body {
        margin: 0;
        min-height: 100vh;
        font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
        color: var(--ink);
        background: var(--page);
    }

    a { color: inherit; }

    .app-shell {
        width: min(1180px, 100%);
        margin: 0 auto;
        padding: 14px 14px 96px;
    }

    .app-header {
        position: sticky;
        top: 0;
        z-index: 20;
        margin: -14px -14px 16px;
        padding: 14px;
        background: rgba(244, 247, 251, 0.94);
        backdrop-filter: blur(14px);
        border-bottom: 1px solid rgba(217, 226, 236, 0.85);
    }

    .header-row,
    .brand-lockup,
    .section-heading,
    .card-head,
    .job-top,
    .card-meta-row,
    .activity-item {
        display: flex;
        gap: 12px;
    }

    .header-row,
    .section-heading,
    .card-head,
    .job-top,
    .card-meta-row,
    .activity-item {
        justify-content: space-between;
    }

    .header-row,
    .brand-lockup,
    .activity-item {
        align-items: center;
    }

    .section-heading,
    .card-head,
    .job-top {
        align-items: flex-start;
    }

    .brand-lockup { min-width: 0; }

    .logo-frame {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        display: grid;
        place-items: center;
        border-radius: 8px;
        background: var(--surface);
        border: 1px solid var(--border);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .logo-frame img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 4px;
    }

    .app-name {
        margin: 0;
        font-size: 18px;
        line-height: 1.1;
        font-weight: 800;
    }

    .hello {
        margin: 3px 0 0;
        color: var(--muted);
        font-size: 13px;
        font-weight: 700;
    }

    .logout-button,
    .button,
    .card-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 46px;
        padding: 10px 14px;
        border: 1px solid var(--primary);
        border-radius: 8px;
        background: var(--primary);
        color: #fff;
        font: inherit;
        font-size: 14px;
        font-weight: 900;
        text-decoration: none;
        cursor: pointer;
    }

    .logout-button {
        min-height: 42px;
        border-color: var(--border);
        background: var(--surface);
        color: var(--primary-dark);
        font-size: 13px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
    }

    .button.secondary,
    .card-button.secondary {
        border-color: var(--border);
        background: var(--surface);
        color: var(--ink);
    }

    .button.warning,
    .card-button.warning {
        border-color: var(--orange);
        background: var(--orange);
    }

    .button.danger,
    .card-button.danger {
        border-color: var(--red);
        background: var(--red);
    }

    .button.full,
    .card-button {
        width: 100%;
    }

    .button[disabled],
    .card-button[disabled] {
        border-color: var(--border);
        background: var(--gray-soft);
        color: var(--muted);
        cursor: not-allowed;
    }

    .status-strip {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        margin-top: 14px;
    }

    .status-tile {
        min-height: 68px;
        padding: 10px;
        border-radius: 8px;
        border: 1px solid var(--border);
        background: var(--surface);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.05);
    }

    .status-tile strong {
        display: block;
        font-size: 22px;
        line-height: 1;
    }

    .status-tile span {
        display: block;
        margin-top: 7px;
        color: var(--muted);
        font-size: 12px;
        font-weight: 800;
    }

    .status-tile.danger {
        border-color: #fecaca;
        background: var(--red-soft);
        color: var(--red);
    }

    .section { margin-top: 22px; }
    .section-heading { margin-bottom: 12px; }

    .eyebrow {
        margin: 0 0 5px;
        color: var(--primary);
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0;
    }

    h1, h2, h3, p { margin: 0; }

    h1 {
        font-size: 28px;
        line-height: 1.12;
        font-weight: 900;
    }

    h2 {
        font-size: 19px;
        line-height: 1.2;
        font-weight: 900;
    }

    .subtext,
    .muted {
        color: var(--muted);
        font-size: 14px;
        line-height: 1.5;
    }

    .subtext { margin-top: 6px; }

    .summary-grid,
    .action-grid,
    .card-grid,
    .job-grid,
    .activity-list,
    .list,
    .timeline,
    .files {
        display: grid;
        gap: 12px;
    }

    .summary-card,
    .action-card,
    .job-card,
    .activity-item,
    .empty-state,
    .panel,
    .item,
    .notice,
    .alert {
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--surface);
        box-shadow: var(--shadow);
    }

    .summary-card,
    .action-card,
    .job-card,
    .activity-item,
    .empty-state,
    .panel,
    .item {
        padding: 15px;
    }

    .summary-card {
        min-height: 118px;
        position: relative;
        overflow: hidden;
    }

    .summary-card::after {
        content: "";
        position: absolute;
        right: 14px;
        top: 14px;
        width: 36px;
        height: 5px;
        border-radius: 999px;
        background: currentColor;
        opacity: 0.26;
    }

    .summary-card.blue { color: var(--primary); }
    .summary-card.orange { color: var(--orange); }
    .summary-card.red { color: var(--red); }
    .summary-card.green { color: var(--green); }

    .summary-card strong {
        display: block;
        color: var(--ink);
        font-size: 32px;
        line-height: 1;
        font-weight: 900;
    }

    .summary-card span {
        display: block;
        margin-top: 10px;
        color: var(--muted);
        font-size: 14px;
        font-weight: 800;
    }

    .summary-card small {
        display: block;
        margin-top: 8px;
        color: currentColor;
        font-size: 12px;
        font-weight: 800;
    }

    .action-card {
        min-height: 72px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        text-decoration: none;
    }

    .action-card strong {
        font-size: 15px;
        line-height: 1.2;
    }

    .action-card span {
        display: inline-grid;
        place-items: center;
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        border-radius: 8px;
        background: var(--primary);
        color: #fff;
        font-weight: 900;
    }

    .client-name,
    .card-title,
    .code {
        font-size: 16px;
        font-weight: 900;
        line-height: 1.25;
    }

    .job-title,
    .card-subtitle {
        margin-top: 4px;
        color: var(--muted);
        font-size: 13px;
        line-height: 1.4;
        font-weight: 700;
    }

    .category-pill {
        display: inline-flex;
        align-items: center;
        margin-top: 12px;
        padding: 6px 9px;
        border-radius: 8px;
        background: var(--primary-soft);
        color: var(--primary-dark);
        font-size: 12px;
        font-weight: 900;
    }

    .meta,
    .card-meta {
        display: grid;
        gap: 8px;
        margin: 14px 0;
        color: var(--muted);
        font-size: 14px;
        font-weight: 700;
    }

    .job-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 14px;
        padding-top: 14px;
        border-top: 1px solid var(--border);
        color: var(--muted);
        font-size: 13px;
        font-weight: 800;
    }

    .grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .label {
        color: var(--muted);
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: 0;
    }

    .value {
        margin-top: 4px;
        font-weight: 800;
    }

    .due-overdue,
    .deadline.overdue {
        color: var(--red);
    }

    .deadline.today {
        color: var(--yellow);
    }

    .badge,
    .status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 28px;
        padding: 5px 9px;
        border-radius: 8px;
        font-size: 12px;
        line-height: 1;
        font-weight: 900;
        white-space: nowrap;
    }

    .badge.open,
    .badge.reopened,
    .badge.claimed,
    .badge.pending_assignment,
    .status.open,
    .status.reopened,
    .status.claimed,
    .status.pending_assignment,
    .status.in_progress,
    .status.ongoing,
    .status.assigned {
        background: var(--primary-soft);
        color: var(--primary-dark);
    }

    .badge.submitted,
    .status.submitted,
    .status.pending_admin_review,
    .status.pending,
    .status.pending_review,
    .status.ready_for_review,
    .status.on_hold {
        background: var(--yellow-soft);
        color: var(--yellow);
    }

    .badge.approved,
    .status.approved,
    .status.completed,
    .status.reviewed {
        background: var(--green-soft);
        color: var(--green);
    }

    .badge.returned,
    .status.returned,
    .status.needs_correction {
        background: var(--orange-soft);
        color: var(--orange);
    }

    .badge.rejected,
    .badge.overdue,
    .status.rejected,
    .status.overdue {
        background: var(--red-soft);
        color: var(--red);
    }

    .badge.closed,
    .status.closed,
    .status.not_started {
        background: var(--gray-soft);
        color: #475467;
    }

    .activity-item {
        text-decoration: none;
    }

    .activity-item strong {
        display: block;
        font-size: 14px;
        line-height: 1.25;
    }

    .activity-item span:not(.badge):not(.status) {
        display: block;
        margin-top: 4px;
        color: var(--muted);
        font-size: 12px;
        font-weight: 700;
        line-height: 1.35;
    }

    .empty-state,
    .empty {
        padding: 20px;
        color: var(--muted);
        font-size: 14px;
        font-weight: 700;
        text-align: center;
    }

    .notice,
    .alert {
        padding: 14px;
        margin-bottom: 14px;
    }

    .success,
    .alert.success {
        border-color: #bbf7d0;
        background: #f0fdf4;
        color: var(--green);
    }

    .error,
    .alert.error {
        border-color: #fecaca;
        background: #fef2f2;
        color: var(--red);
    }

    .locked,
    .alert.warning {
        border-color: #fde68a;
        background: #fffbeb;
        color: var(--yellow);
    }

    .admin-note,
    .update,
    .file {
        margin-top: 10px;
        padding: 12px;
        border-radius: 8px;
        border: 1px solid var(--border);
        background: #f9fbfd;
    }

    .admin-note {
        border-color: #fed7aa;
        background: #fff7ed;
        color: var(--orange);
        white-space: pre-line;
    }

    label {
        display: block;
        margin-bottom: 6px;
        color: var(--muted);
        font-size: 14px;
        font-weight: 800;
    }

    textarea,
    input[type="text"],
    input[type="number"],
    input[type="date"],
    input[type="file"],
    select {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 11px;
        font: inherit;
        background: #fff;
    }

    textarea {
        min-height: 130px;
        resize: vertical;
    }

    .form-row { margin-bottom: 14px; }

    .progress {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .bar {
        height: 8px;
        flex: 1;
        border-radius: 999px;
        background: #e5e7eb;
        overflow: hidden;
    }

    .bar span {
        display: block;
        height: 100%;
        background: var(--primary);
    }

    .file a {
        color: var(--primary);
        font-weight: 900;
    }

    .file img {
        display: block;
        width: 100%;
        max-height: 220px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 8px;
    }

    .bottom-nav {
        position: fixed;
        left: 50%;
        bottom: 12px;
        z-index: 30;
        width: min(460px, calc(100% - 24px));
        transform: translateX(-50%);
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 5px;
        padding: 7px;
        border: 1px solid rgba(217, 226, 236, 0.95);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.96);
        box-shadow: 0 18px 44px rgba(15, 23, 42, 0.18);
        backdrop-filter: blur(16px);
    }

    .bottom-nav a {
        min-height: 48px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        border-radius: 8px;
        color: var(--muted);
        font-size: 10px;
        font-weight: 900;
        text-decoration: none;
    }

    .bottom-nav b {
        display: grid;
        place-items: center;
        width: 22px;
        height: 22px;
        border-radius: 8px;
        background: var(--gray-soft);
        color: inherit;
        font-size: 10px;
    }

    .bottom-nav a.active {
        background: var(--primary);
        color: #fff;
    }

    .bottom-nav a.active b {
        background: rgba(255, 255, 255, 0.18);
    }

    .pagination {
        margin-top: 16px;
    }

    @media (min-width: 700px) {
        .app-shell {
            padding: 24px 24px 108px;
        }

        .app-header {
            margin: -24px -24px 22px;
            padding: 18px 24px;
        }

        .summary-grid,
        .action-grid,
        .card-grid,
        .job-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .activity-list,
        .list {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 1024px) {
        .summary-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .action-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .job-grid,
        .card-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .grid {
            grid-template-columns: 1fr;
        }

        .card-head,
        .job-top,
        .card-meta-row {
            flex-direction: column;
            align-items: flex-start;
        }

        .button.mobile-full {
            width: 100%;
        }
    }
</style>
