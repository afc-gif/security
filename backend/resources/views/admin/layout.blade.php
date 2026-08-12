<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ARTSCI Admin Console')</title>
    <link rel="icon" type="image/webp" href="{{ asset('Artsci Logo REAL 1.webp') }}">
    <link rel="apple-touch-icon" href="{{ asset('Artsci Logo REAL 1.webp') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.4.0/axios.min.js"></script>
    @stack('head')
    <style>
        :root {
            --brand-dark: #0A1428;
            --brand-ink: #0f172a;
            --brand-soft: #F0F4F9;
            --brand-border: #E0E6EF;
            --brand-muted: #8A95A8;
            --brand-shadow: 0 18px 48px rgba(10, 20, 40, 0.12);
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Manrope', system-ui, -apple-system, sans-serif;
            color: var(--brand-ink);
            background:
                radial-gradient(circle at 12% 18%, rgba(3,169,244,0.12), transparent 30%),
                radial-gradient(circle at 88% 12%, rgba(10,20,40,0.08), transparent 22%),
                var(--brand-soft);
            min-height: 100vh;
            font-size: 15px;
            line-height: 1.5;
        }

        body.admin-sidebar-open {
            overflow: hidden;
        }

        a,
        button,
        input,
        select,
        textarea {
            font: inherit;
        }

        button,
        a {
            -webkit-tap-highlight-color: transparent;
        }

        .admin-shell {
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
            transition: grid-template-columns 0.2s ease;
        }

        body.collapsed .admin-shell { grid-template-columns: 72px 1fr; }

        .hamburger {
            display: block;
            position: fixed;
            top: 16px;
            left: 16px;
            background: #fff;
            border: 1px solid var(--brand-border);
            border-radius: 8px;
            width: 44px;
            height: 44px;
            padding: 0;
            box-shadow: var(--brand-shadow);
            cursor: pointer;
            z-index: 70;
            font-size: 18px;
            line-height: 1;
        }

        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            height: 100dvh;
            width: 260px;
            padding: 18px;
            border-right: 1px solid var(--brand-border);
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(10px);
            box-shadow: 6px 0 30px rgba(0,0,0,0.05);
            display: grid;
            grid-template-rows: auto minmax(0, 1fr) auto;
            align-content: stretch;
            gap: 12px;
            transition: width 0.3s ease, padding 0.3s ease;
            z-index: 60;
            overflow: hidden;
        }

        .sidebar.collapsed { width: 72px; padding: 12px; }

        .admin-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid var(--brand-border);
            background: #fff;
            box-shadow: 0 10px 24px rgba(0,0,0,0.06);
            transition: opacity 0.2s ease;
        }

        .sidebar.collapsed .admin-brand {
            opacity: 0;
            pointer-events: none;
            height: 0;
            padding: 0;
            margin: 0;
        }

        .admin-brand img {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            object-fit: contain;
        }

        .brand-name {
            display: block;
            margin: 0;
            font-family: 'Manrope', system-ui, -apple-system, sans-serif;
            font-size: 20px;
            font-weight: 800;
            color: var(--brand-dark);
        }

        .brand-tagline,
        .admin-user-meta {
            display: block;
            color: var(--brand-muted);
            font-size: 13px;
            margin: 2px 0 0;
        }

        .admin-nav {
            display: grid;
            gap: 6px;
            margin-top: 8px;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 2px 4px 8px 0;
            scrollbar-width: thin;
            scrollbar-color: rgba(10, 20, 40, 0.28) transparent;
        }

        .admin-nav::-webkit-scrollbar { width: 8px; }
        .admin-nav::-webkit-scrollbar-track { background: transparent; }
        .admin-nav::-webkit-scrollbar-thumb {
            background: rgba(10, 20, 40, 0.22);
            border-radius: 999px;
        }

        .admin-nav::-webkit-scrollbar-thumb:hover {
            background: rgba(10, 20, 40, 0.34);
        }

        .nav-section {
            margin-top: 10px;
            padding: 0 12px;
            color: var(--brand-muted);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .sidebar.collapsed .nav-section { display: none; }

        .nav-btn,
        .nav-item {
            border: 1px solid var(--brand-border);
            background: #fff;
            color: var(--brand-dark);
            min-height: 44px;
            width: 100%;
            padding: 10px 12px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            line-height: 1.25;
            text-align: left;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            transition: background 0.2s ease, color 0.2s ease;
        }

        .nav-btn.active,
        .nav-item.active {
            background: var(--brand-dark);
            color: #fff;
            box-shadow: var(--brand-shadow);
        }

        .nav-label { white-space: nowrap; }
        .sidebar.collapsed .nav-label { display: none; }

        .nav-icon {
            flex: 0 0 28px;
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--brand-soft);
            color: var(--brand-dark);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0;
        }

        .nav-btn.active .nav-icon,
        .nav-item.active .nav-icon {
            background: rgba(255,255,255,0.16);
            color: #fff;
        }

        .sidebar.collapsed .nav-btn,
        .sidebar.collapsed .nav-item {
            justify-content: center;
            padding-left: 8px;
            padding-right: 8px;
        }

        .admin-sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.42);
            z-index: 50;
        }

        .admin-user {
            margin-top: 0;
            padding-top: 12px;
            border-top: 1px solid var(--brand-border);
            display: grid;
            gap: 8px;
            background: rgba(255,255,255,0.96);
        }

        .sidebar.collapsed .admin-user-meta { display: none; }

        .admin-content {
            padding: 22px;
            min-width: 0;
        }

        .admin-content > * {
            min-width: 0;
        }

        .admin-content h1 {
            font-family: 'Manrope', system-ui, -apple-system, sans-serif;
            font-size: 28px;
            line-height: 1.2;
            font-weight: 800;
            letter-spacing: 0;
        }

        .admin-content h2 {
            font-family: 'Manrope', system-ui, -apple-system, sans-serif;
            font-size: 21px;
            line-height: 1.3;
            font-weight: 800;
            letter-spacing: 0;
        }

        .admin-content p,
        .admin-content td,
        .admin-content th,
        .admin-content label,
        .admin-content input,
        .admin-content select,
        .admin-content textarea {
            font-size: 14px;
        }

        .admin-content button,
        .admin-content .btn,
        .admin-content a[class*="bg-"],
        .admin-content a[class*="btn"],
        .admin-content input[type="submit"] {
            min-height: 40px;
            max-width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 8px;
            line-height: 1.2;
            text-align: center;
            white-space: normal;
        }

        .admin-content table {
            width: 100%;
        }

        .admin-content img,
        .admin-content svg,
        .admin-content video,
        .admin-content canvas {
            max-width: 100%;
        }

        .admin-content .container,
        .admin-content .max-w-7xl {
            width: 100%;
            max-width: 1280px;
        }

        .admin-content input,
        .admin-content select,
        .admin-content textarea {
            max-width: 100%;
        }

        .finance-mobile-topbar {
            display: none;
        }

        body.finance-shell .admin-shell {
            grid-template-columns: 244px minmax(0, 1fr);
        }

        body.finance-shell .sidebar {
            width: 244px;
            padding: 18px 16px;
            background: rgba(255,255,255,0.92);
            box-shadow: 8px 0 32px rgba(10,20,40,0.04);
        }

        body.finance-shell .admin-brand {
            border: 0;
            box-shadow: none;
            padding: 6px 8px 14px;
            border-bottom: 1px solid var(--brand-border);
            border-radius: 0;
        }

        body.finance-shell .admin-brand img {
            width: 42px;
            height: 42px;
        }

        body.finance-shell .brand-name {
            font-size: 19px;
        }

        body.finance-shell .admin-nav {
            gap: 4px;
            padding-right: 0;
        }

        body.finance-shell .nav-section {
            padding: 0 8px;
            margin-top: 8px;
        }

        body.finance-shell .nav-item,
        body.finance-shell .nav-btn {
            border-color: transparent;
            background: transparent;
            min-height: 40px;
            padding: 9px 10px;
        }

        body.finance-shell .nav-item:hover,
        body.finance-shell .nav-btn:hover {
            background: var(--brand-soft);
        }

        body.finance-shell .nav-item:focus-visible,
        body.finance-shell .nav-btn:focus-visible,
        body.finance-shell .hamburger:focus-visible {
            outline: 3px solid rgba(59, 130, 246, 0.35);
            outline-offset: 2px;
        }

        body.finance-shell .nav-item.active,
        body.finance-shell .nav-btn.active {
            background: var(--brand-dark);
            color: #fff;
            box-shadow: none;
        }

        body.finance-shell .admin-user {
            background: transparent;
        }

        body.finance-shell .admin-content {
            padding: 0;
            background: #f7f9fc;
        }

        body.finance-shell.collapsed .admin-shell {
            grid-template-columns: 72px minmax(0, 1fr);
        }

        body.finance-shell .sidebar.collapsed {
            width: 72px;
            padding: 12px;
        }

        body.finance-shell .finance-page {
            min-height: 100vh;
            background:
                linear-gradient(180deg, rgba(255,255,255,0.84), rgba(247,249,252,0.94) 220px),
                #f7f9fc;
            padding: 28px;
        }

        body.finance-shell .finance-wrap {
            width: 100%;
            max-width: 1180px;
            margin: 0 auto;
        }

        body.finance-shell .finance-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 18px;
            margin: 4px 0 26px;
        }

        body.finance-shell .finance-eyebrow {
            color: var(--brand-muted);
            font-size: 11px;
            line-height: 1;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        body.finance-shell .finance-title {
            margin: 8px 0 0;
            color: var(--brand-dark);
            font-size: clamp(28px, 4vw, 40px);
            line-height: 1.05;
            font-weight: 850;
            letter-spacing: 0;
        }

        body.finance-shell .finance-subtitle {
            margin: 8px 0 0;
            color: #64748b;
            font-size: 15px;
        }

        body.finance-shell .finance-panel {
            background: rgba(255,255,255,0.96);
            border: 1px solid rgba(226,232,240,0.92);
            border-radius: 14px;
            box-shadow: 0 18px 48px rgba(15,23,42,0.055);
            overflow: hidden;
        }

        body.finance-shell .finance-panel-flat {
            background: rgba(255,255,255,0.88);
            border: 1px solid rgba(226,232,240,0.88);
            border-radius: 14px;
        }

        body.finance-shell .finance-section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 18px 20px;
            border-bottom: 1px solid #eef2f7;
        }

        body.finance-shell .finance-section-title {
            color: var(--brand-dark);
            font-size: 17px;
            font-weight: 850;
            line-height: 1.2;
        }

        body.finance-shell .finance-muted {
            color: #64748b;
        }

        body.finance-shell .finance-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 24px;
        }

        body.finance-shell .finance-stat {
            padding: 18px;
            background: rgba(255,255,255,0.92);
            border: 1px solid rgba(226,232,240,0.9);
            border-radius: 14px;
        }

        body.finance-shell .finance-stat-label {
            color: #64748b;
            font-size: 11px;
            font-weight: 850;
            letter-spacing: 0.07em;
            text-transform: uppercase;
        }

        body.finance-shell .finance-stat-value {
            margin-top: 8px;
            color: var(--brand-dark);
            font-size: 30px;
            line-height: 1;
            font-weight: 850;
        }

        body.finance-shell .finance-list {
            display: grid;
        }

        body.finance-shell .finance-row {
            display: grid;
            grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr) 150px 92px;
            gap: 16px;
            align-items: center;
            padding: 16px 20px;
            border-top: 1px solid #f1f5f9;
        }

        body.finance-shell .finance-row:first-child {
            border-top: 0;
        }

        body.finance-shell .finance-row:hover {
            background: rgba(248,250,252,0.88);
        }

        body.finance-shell .finance-row-title {
            color: #111827;
            font-weight: 850;
            line-height: 1.25;
        }

        body.finance-shell .finance-row-meta {
            margin-top: 4px;
            color: #64748b;
            font-size: 13px;
        }

        body.finance-shell .finance-status {
            display: inline-flex;
            width: fit-content;
            align-items: center;
            border-radius: 999px;
            background: #f1f5f9;
            color: #334155;
            padding: 5px 9px;
            font-size: 12px;
            font-weight: 800;
        }

        body.finance-shell .finance-btn {
            display: inline-flex;
            min-height: 38px;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            padding: 9px 14px;
            font-size: 13px;
            font-weight: 850;
            text-decoration: none;
            transition: background 0.18s ease, border-color 0.18s ease, color 0.18s ease;
        }

        body.finance-shell .finance-btn-primary {
            background: var(--brand-dark);
            color: #fff;
        }

        body.finance-shell .finance-btn-primary:hover {
            background: #111f3b;
        }

        body.finance-shell .finance-btn-secondary {
            background: #fff;
            border: 1px solid #dbe3ee;
            color: #172033;
        }

        body.finance-shell .finance-btn-secondary:hover {
            background: #f8fafc;
        }

        body.finance-shell .finance-filter {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 220px auto;
            gap: 12px;
            align-items: end;
            margin-bottom: 18px;
            padding: 14px;
        }

        body.finance-shell .finance-filter-compact {
            grid-template-columns: 240px auto;
        }

        body.finance-shell .finance-field label {
            display: block;
            color: #475569;
            font-size: 12px;
            font-weight: 850;
            margin-bottom: 6px;
        }

        body.finance-shell .finance-field input,
        body.finance-shell .finance-field select,
        body.finance-shell .finance-field textarea {
            width: 100%;
            border: 1px solid #dbe3ee;
            border-radius: 10px;
            background: #fff;
            color: #111827;
            padding: 10px 12px;
            outline: none;
        }

        body.finance-shell .finance-field input:focus,
        body.finance-shell .finance-field select:focus,
        body.finance-shell .finance-field textarea:focus {
            border-color: #7aa7d9;
            box-shadow: 0 0 0 4px rgba(59,130,246,0.12);
        }

        @media (max-width: 960px) {
            .admin-shell { grid-template-columns: 1fr; }

            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                height: 100vh;
                height: 100dvh;
                width: min(86vw, 320px);
                max-width: 320px;
                padding: 16px;
                transform: translate3d(-105%, 0, 0);
                transition: transform 0.24s ease;
                box-shadow: 18px 0 48px rgba(10, 20, 40, 0.22);
                will-change: transform;
            }

            .sidebar.open { transform: translate3d(0, 0, 0); }
            .sidebar.open + .admin-sidebar-backdrop { display: block; }

            .sidebar.collapsed {
                width: min(86vw, 320px);
                max-width: 320px;
                padding: 16px;
            }

            .sidebar.collapsed .admin-brand {
                opacity: 1;
                pointer-events: auto;
                height: auto;
                padding: 10px;
                margin: 0;
            }
            .sidebar.collapsed .nav-label { display: inline; }
            .sidebar.collapsed .admin-user-meta { display: block; }
            .sidebar.collapsed .nav-btn,
            .sidebar.collapsed .nav-item {
                justify-content: flex-start;
                padding: 10px 12px;
            }
            .admin-content { padding: 72px 14px 18px; }

            .admin-content .min-h-screen {
                min-height: auto;
            }

            .admin-content .sticky {
                top: 0;
            }

            body.finance-shell .admin-shell {
                grid-template-columns: 1fr;
            }

            body.finance-shell .finance-mobile-topbar {
                display: flex;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                z-index: 45;
                min-height: 58px;
                align-items: center;
                padding: 8px 14px 8px 68px;
                border-bottom: 1px solid var(--brand-border);
                background: rgba(255,255,255,0.96);
                backdrop-filter: blur(10px);
            }

            body.finance-shell .hamburger {
                top: 8px;
                left: 12px;
                box-shadow: none;
                z-index: 75;
            }

            body.finance-shell .sidebar,
            body.finance-shell .sidebar.collapsed {
                width: min(86vw, 320px);
                max-width: 320px;
                padding: 16px;
            }

            body.finance-shell .admin-brand {
                border-bottom: 1px solid var(--brand-border);
                padding: 8px 6px 14px;
            }

            body.finance-shell .admin-content {
                padding: 76px 14px 18px;
            }

            body.finance-shell .finance-page {
                padding: 76px 14px 18px;
            }

            body.finance-shell .finance-header {
                align-items: flex-start;
                flex-direction: column;
                margin-top: 0;
            }

            body.finance-shell .finance-stats {
                grid-template-columns: 1fr;
            }

            body.finance-shell .finance-filter {
                grid-template-columns: 1fr;
                padding: 12px;
            }

            body.finance-shell .finance-filter-compact {
                grid-template-columns: 1fr;
            }

            body.finance-shell .finance-row {
                grid-template-columns: 1fr;
                gap: 9px;
                padding: 15px;
            }

            body.finance-shell .finance-row .finance-btn {
                width: 100%;
            }
        }

        @media (max-width: 640px) {
            body { font-size: 14px; }

            .hamburger {
                top: 12px;
                left: 12px;
                width: 42px;
                height: 42px;
            }

            .sidebar {
                width: min(88vw, 320px);
                padding: 14px;
            }

            .admin-brand img {
                width: 42px;
                height: 42px;
            }

            .brand-name { font-size: 18px; }
            .brand-tagline,
            .admin-user-meta { font-size: 12px; }

            .admin-content {
                padding: 68px 10px 16px;
            }

            .admin-content h1 { font-size: 23px; }
            .admin-content h2 { font-size: 19px; }

            .admin-content .container,
            .admin-content .max-w-7xl {
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            .admin-content button,
            .admin-content .btn,
            .admin-content a[class*="bg-"],
            .admin-content a[class*="btn"],
            .admin-content input[type="submit"] {
                min-height: 42px;
                width: 100%;
                padding-left: 12px;
                padding-right: 12px;
            }

            .admin-content table {
                display: block;
                width: 100%;
                min-width: 0;
                overflow-x: auto;
                white-space: nowrap;
                -webkit-overflow-scrolling: touch;
            }

            .admin-content thead,
            .admin-content tbody,
            .admin-content tr {
                width: 100%;
            }

            .admin-content th,
            .admin-content td {
                white-space: nowrap;
            }

            .admin-content form,
            .admin-content .form-actions,
            .admin-content .admin-header,
            .admin-content .admin-header-left,
            .admin-content [class*="justify-between"],
            .admin-content [class*="items-center"] {
                min-width: 0;
            }

            .admin-content .form-actions,
            .admin-content .admin-header {
                flex-direction: column;
                align-items: stretch;
            }

            .admin-content [class*="grid-cols-"],
            .admin-content .stats-grid,
            .admin-content .order-details-container {
                grid-template-columns: 1fr !important;
            }

            .admin-content .rounded-xl,
            .admin-content .rounded-lg {
                border-radius: 8px;
            }

            .admin-content [class*="px-6"],
            .admin-content [class*="p-6"] {
                padding-left: 12px !important;
                padding-right: 12px !important;
            }

            .admin-content [class*="py-6"],
            .admin-content [class*="p-6"] {
                padding-top: 12px !important;
                padding-bottom: 12px !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body class="{{ request()->routeIs('finance.*') ? 'finance-shell' : '' }}">
    <div class="admin-shell">
        <button class="hamburger" id="toggleSidebar" type="button" aria-label="Toggle admin menu" aria-expanded="false">☰</button>
        @include('admin.partials.sidebar')
        <div class="admin-sidebar-backdrop" id="adminSidebarBackdrop" aria-hidden="true"></div>
        @if(request()->routeIs('finance.*'))
            <div class="finance-mobile-topbar" aria-label="Finance header">
                <div>
                    <div style="font-weight:800;color:var(--brand-dark);line-height:1.1;">ARTSCI</div>
                    <div style="font-size:12px;color:var(--brand-muted);font-weight:700;">Finance</div>
                </div>
            </div>
        @endif

        <main class="admin-content">
            @if(($adminUnreadNotifications ?? collect())->count())
                <div class="mb-4 grid gap-3">
                    @foreach($adminUnreadNotifications as $notification)
                        <div class="rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-yellow-950 shadow-sm">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                <div>
                                    <div class="font-bold">{{ $notification->title }}</div>
                                    <div class="mt-1 whitespace-pre-line text-sm">{{ $notification->message }}</div>
                                </div>
                                <form method="POST" action="{{ route('admin.notifications.read', $notification) }}">
                                    @csrf
                                    <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white px-3 py-2 rounded-lg text-sm font-semibold">
                                        Mark Read
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            @yield('content')
        </main>
    </div>

    <script>
        (function () {
            const sidebar = document.getElementById('sidebar');
            const toggleSidebar = document.getElementById('toggleSidebar');
            const sidebarBackdrop = document.getElementById('adminSidebarBackdrop');
            if (!sidebar || !toggleSidebar || toggleSidebar.dataset.bound === '1') return;

            toggleSidebar.dataset.bound = '1';
            const closeMobileSidebar = () => {
                sidebar.classList.remove('open');
                document.body.classList.remove('admin-sidebar-open');
                toggleSidebar.setAttribute('aria-expanded', 'false');
                toggleSidebar.textContent = '☰';
            };

            toggleSidebar.addEventListener('click', () => {
                const isMobile = window.innerWidth <= 960;
                if (isMobile) {
                    document.body.classList.remove('collapsed');
                    sidebar.classList.remove('collapsed');
                    const isOpen = sidebar.classList.toggle('open');
                    document.body.classList.toggle('admin-sidebar-open', isOpen);
                    toggleSidebar.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                    toggleSidebar.textContent = isOpen ? '×' : '☰';
                } else {
                    document.body.classList.toggle('collapsed');
                    sidebar.classList.toggle('collapsed');
                }
            });

            sidebarBackdrop?.addEventListener('click', closeMobileSidebar);

            sidebar.querySelectorAll('a, button[data-tab]').forEach((item) => {
                item.addEventListener('click', () => {
                    if (window.innerWidth <= 960) closeMobileSidebar();
                });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') closeMobileSidebar();
            });

            window.addEventListener('resize', () => {
                if (window.innerWidth > 960) {
                    closeMobileSidebar();
                } else {
                    document.body.classList.remove('collapsed');
                    sidebar.classList.remove('collapsed');
                }
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
