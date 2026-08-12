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

        body.finance-shell .admin-shell {
            grid-template-columns: 276px minmax(0, 1fr);
        }

        body.finance-shell .sidebar {
            width: 276px;
            padding: 22px 20px;
            background:
                radial-gradient(circle at 18% 6%, rgba(107, 70, 255, 0.38), transparent 28%),
                linear-gradient(180deg, #071037 0%, #081433 54%, #0a1428 100%);
            color: #f8fbff;
            box-shadow: 18px 0 48px rgba(10,20,40,0.16);
        }

        body.finance-shell .admin-brand {
            border: 0;
            box-shadow: none;
            padding: 0 6px 26px;
            background: transparent;
            border-radius: 0;
        }

        body.finance-shell .admin-brand img {
            width: 50px;
            height: 50px;
        }

        body.finance-shell .brand-name {
            color: #fff;
            font-size: 22px;
            letter-spacing: 0;
        }

        body.finance-shell .brand-tagline {
            color: #8b5cf6;
            font-size: 13px;
            letter-spacing: 0.18em;
        }

        body.finance-shell .admin-nav {
            gap: 10px;
            padding-right: 0;
        }

        body.finance-shell .nav-section {
            padding: 0 8px;
            margin: 16px 0 4px;
            color: rgba(226, 232, 240, 0.74);
            letter-spacing: 0.06em;
        }

        body.finance-shell .nav-item,
        body.finance-shell .nav-btn {
            border-color: transparent;
            background: transparent;
            color: rgba(248, 250, 252, 0.88);
            min-height: 52px;
            padding: 13px 14px;
            border-radius: 8px;
        }

        body.finance-shell .nav-item:hover,
        body.finance-shell .nav-btn:hover {
            background: rgba(255,255,255,0.08);
            color: #fff;
        }

        body.finance-shell .nav-icon {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: rgba(255,255,255,0.08);
            color: #fff;
            font-size: 10px;
        }

        body.finance-shell .nav-item:focus-visible,
        body.finance-shell .nav-btn:focus-visible,
        body.finance-shell .hamburger:focus-visible {
            outline: 3px solid rgba(59, 130, 246, 0.35);
            outline-offset: 2px;
        }

        body.finance-shell .nav-item.active,
        body.finance-shell .nav-btn.active {
            background: linear-gradient(135deg, #4f24e8, #32138f);
            color: #fff;
            box-shadow: 0 16px 34px rgba(48, 19, 143, 0.35);
        }

        body.finance-shell .admin-user {
            background: transparent;
            border-top: 1px solid rgba(255,255,255,0.12);
            padding-top: 16px;
        }

        body.finance-shell .admin-user-meta {
            color: rgba(226,232,240,0.8);
        }

        body.finance-shell .finance-sidebar-person {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 18px;
            padding-top: 18px;
            border-top: 1px solid rgba(255,255,255,0.12);
            color: #fff;
        }

        body.finance-shell .finance-sidebar-name {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-weight: 850;
        }

        body.finance-shell .finance-sidebar-role {
            color: rgba(226,232,240,0.72);
            font-size: 13px;
            font-weight: 700;
        }

        body.finance-shell .admin-content {
            padding: 0;
            background: #f8fafc;
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
            background: #f8fafc;
            padding: 120px 34px 34px;
        }

        body.finance-shell .finance-wrap {
            width: 100%;
            max-width: 1220px;
            margin: 0 auto;
        }

        body.finance-shell .finance-mobile-topbar {
            display: flex;
            position: fixed;
            top: 0;
            left: 276px;
            right: 0;
            z-index: 45;
            min-height: 86px;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 16px 34px 16px 88px;
            border-bottom: 1px solid rgba(226,232,240,0.92);
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(12px);
            box-shadow: 0 8px 26px rgba(15,23,42,0.035);
        }

        body.finance-shell .finance-topbar-title {
            color: var(--brand-dark);
            font-size: 12px;
            font-weight: 850;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        body.finance-shell .finance-topbar-user {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 14px;
            min-width: 0;
            color: var(--brand-dark);
            text-align: right;
        }

        body.finance-shell .finance-topbar-name {
            max-width: 220px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-weight: 850;
        }

        body.finance-shell .finance-topbar-role {
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
        }

        body.finance-shell .finance-bell,
        body.finance-shell .finance-avatar {
            display: inline-flex;
            width: 42px;
            height: 42px;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            border-radius: 999px;
            font-weight: 850;
        }

        body.finance-shell .finance-bell {
            border: 1px solid #e2e8f0;
            background: #fff;
            color: #4f24e8;
        }

        body.finance-shell .finance-avatar {
            background: linear-gradient(135deg, #6d3df7, #3410b8);
            color: #fff;
        }

        body.finance-shell .finance-account-anchor {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
        }

        body.finance-shell .finance-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 18px;
            margin: 0 0 28px;
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
            font-size: clamp(28px, 3vw, 36px);
            line-height: 1.12;
            font-weight: 850;
            letter-spacing: 0;
        }

        body.finance-shell .finance-subtitle {
            margin: 8px 0 0;
            color: #475569;
            font-size: 18px;
        }

        body.finance-shell .finance-panel {
            background: #fff;
            border: 1px solid rgba(203,213,225,0.78);
            border-radius: 8px;
            box-shadow: 0 14px 34px rgba(15,23,42,0.055);
            overflow: hidden;
        }

        body.finance-shell .finance-panel-flat {
            background: #fff;
            border: 1px solid rgba(226,232,240,0.88);
            border-radius: 8px;
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
            font-size: 21px;
            font-weight: 850;
            line-height: 1.2;
            padding-left: 14px;
            position: relative;
        }

        body.finance-shell .finance-section-title::before {
            content: "";
            position: absolute;
            left: 0;
            top: 3px;
            bottom: 3px;
            width: 3px;
            border-radius: 999px;
            background: #4f24e8;
        }

        body.finance-shell .finance-muted {
            color: #64748b;
        }

        body.finance-shell .finance-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 24px;
            margin-bottom: 28px;
        }

        body.finance-shell .finance-stat {
            min-height: 160px;
            padding: 28px 24px;
            background: #fff;
            border: 1px solid rgba(203,213,225,0.82);
            border-radius: 8px;
            box-shadow: 0 14px 32px rgba(15,23,42,0.052);
        }

        body.finance-shell .finance-stat-inner {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        body.finance-shell .finance-stat-icon {
            display: inline-flex;
            width: 70px;
            height: 70px;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            border-radius: 16px;
            background: #f0eaff;
            color: #4f24e8;
            font-size: 20px;
            font-weight: 900;
        }

        body.finance-shell .finance-stat-label {
            color: #475569;
            font-size: 14px;
            font-weight: 850;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        body.finance-shell .finance-stat-value {
            margin-top: 8px;
            color: #020617;
            font-size: 36px;
            line-height: 1;
            font-weight: 850;
        }

        body.finance-shell .finance-stat-link,
        body.finance-shell .finance-card-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 24px;
            color: #3410b8;
            font-size: 15px;
            font-weight: 850;
            text-decoration: none;
        }

        body.finance-shell .finance-dashboard-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 24px;
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

        body.finance-shell .finance-dashboard-row {
            grid-template-columns: 54px minmax(0, 1fr) auto 76px;
            min-height: 96px;
        }

        body.finance-shell .finance-dashboard-row .finance-stat-icon {
            width: 54px;
            height: 54px;
            border-radius: 999px;
            font-size: 13px;
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
            background: #fff;
            border: 1px solid rgba(79,36,232,0.32);
            color: #3410b8;
        }

        body.finance-shell .finance-btn-primary:hover {
            background: #f4f0ff;
        }

        body.finance-shell .finance-btn-secondary {
            background: #fff;
            border: 0;
            color: #3410b8;
        }

        body.finance-shell .finance-btn-secondary:hover {
            background: #f4f0ff;
        }

        body.finance-shell .finance-dashboard-footer {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px 20px;
            border-top: 1px solid #e2e8f0;
        }

        body.finance-shell .finance-banner {
            display: flex;
            align-items: center;
            gap: 22px;
            margin-top: 34px;
            padding: 28px 30px;
            border-radius: 8px;
            background:
                radial-gradient(circle at 90% 30%, rgba(139,92,246,0.34), transparent 30%),
                linear-gradient(135deg, #091238, #3b0ca8 58%, #4f24e8);
            color: #fff;
            box-shadow: 0 18px 44px rgba(49, 19, 143, 0.22);
        }

        body.finance-shell .finance-banner-icon {
            display: inline-flex;
            width: 62px;
            height: 62px;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,0.34);
            color: #fff;
            font-weight: 900;
        }

        body.finance-shell .finance-banner-title {
            font-size: 18px;
            font-weight: 850;
        }

        body.finance-shell .finance-banner-text {
            margin-top: 4px;
            color: rgba(255,255,255,0.78);
            font-size: 16px;
        }

        body.finance-shell .finance-job-list {
            display: grid;
        }

        body.finance-shell .finance-job-row {
            display: grid;
            grid-template-columns: minmax(0, 1.45fr) minmax(160px, 0.8fr) minmax(140px, 0.65fr) minmax(130px, 0.7fr) 92px;
            gap: 18px;
            align-items: center;
            padding: 18px 20px;
            border-top: 1px solid #eef2f7;
        }

        body.finance-shell .finance-job-row:first-child {
            border-top: 0;
        }

        body.finance-shell .finance-job-row:hover {
            background: #fbfdff;
        }

        body.finance-shell .finance-job-label {
            color: #64748b;
            font-size: 11px;
            font-weight: 850;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        body.finance-shell .finance-job-value {
            margin-top: 5px;
            color: #0f172a;
            font-weight: 850;
        }

        body.finance-shell .finance-back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
            color: #3410b8;
            font-weight: 850;
            text-decoration: none;
        }

        /* Simplified Jobs List */
        body.finance-shell .finance-filter-bar {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 24px;
            padding: 16px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(15,23,42,0.06);
        }

        body.finance-shell .finance-filter-content {
            display: grid;
            grid-template-columns: minmax(200px, 1fr) 180px auto auto;
            gap: 12px;
            align-items: end;
        }

        body.finance-shell .finance-filter-input,
        body.finance-shell .finance-filter-select {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 11px 13px;
            font-size: 14px;
            background: #fff;
            color: #111827;
            outline: none;
            transition: border-color 0.2s ease;
        }

        body.finance-shell .finance-filter-input:focus,
        body.finance-shell .finance-filter-select:focus {
            border-color: #4f24e8;
            box-shadow: 0 0 0 3px rgba(79,36,232,0.1);
        }

        body.finance-shell .finance-empty-message {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 14px;
            padding: 48px 24px;
            text-align: center;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 12px;
            border: 1px solid rgba(226,232,240,0.6);
        }

        body.finance-shell .finance-empty-icon {
            font-size: 44px;
            line-height: 1;
        }

        body.finance-shell .finance-empty-title {
            color: #0a0e27;
            font-size: 18px;
            font-weight: 850;
        }

        body.finance-shell .finance-empty-text {
            color: #64748b;
            font-size: 14px;
            line-height: 1.5;
        }

        body.finance-shell .finance-jobs-list {
            display: grid;
            gap: 12px;
        }

        body.finance-shell .finance-job-card {
            display: flex;
            flex-direction: column;
            gap: 14px;
            padding: 18px;
            background: linear-gradient(135deg, #fff 0%, #f9fafb 100%);
            border: 1px solid rgba(203,213,225,0.6);
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(15,23,42,0.06);
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        body.finance-shell .finance-job-card:hover {
            border-color: rgba(79,36,232,0.3);
            box-shadow: 0 10px 24px rgba(79,36,232,0.12);
            transform: translateY(-2px);
        }

        body.finance-shell .finance-job-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            gap: 12px;
        }

        body.finance-shell .finance-job-title {
            color: #0a0e27;
            font-size: 16px;
            font-weight: 850;
            line-height: 1.3;
            margin: 0;
            flex: 1;
        }

        body.finance-shell .finance-job-info {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        body.finance-shell .finance-job-detail {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        body.finance-shell .finance-job-label {
            color: #64748b;
            font-size: 11px;
            font-weight: 850;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        body.finance-shell .finance-job-value {
            color: #0a0e27;
            font-size: 14px;
            font-weight: 850;
        }

        body.finance-shell .finance-job-detail-amount {
            text-align: right;
        }

        body.finance-shell .finance-job-value-amount {
            color: #4f24e8;
            font-size: 16px;
            font-weight: 850;
        }

        body.finance-shell .finance-pagination {
            margin-top: 24px;
            display: flex;
            justify-content: center;
        }

        /* Job Detail Page Simplified */
        body.finance-shell .finance-job-header-section {
            margin-bottom: 24px;
            padding-bottom: 24px;
            border-bottom: 1px solid #e2e8f0;
        }

        body.finance-shell .finance-job-page-title {
            font-size: 28px;
            font-weight: 850;
            color: #0a0e27;
            margin: 0 0 18px;
            line-height: 1.2;
        }

        body.finance-shell .finance-job-meta-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 20px;
        }

        body.finance-shell .finance-meta-item {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        body.finance-shell .finance-meta-label {
            font-size: 11px;
            font-weight: 850;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        body.finance-shell .finance-meta-value {
            font-size: 15px;
            font-weight: 850;
            color: #0a0e27;
        }

        body.finance-shell .finance-total-spent-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 24px;
            padding: 28px;
            background: linear-gradient(135deg, #4f24e8, #3410b8);
            border-radius: 14px;
            box-shadow: 0 12px 32px rgba(79,36,232,0.2);
            margin-bottom: 28px;
            position: relative;
            overflow: hidden;
        }

        body.finance-shell .finance-total-spent-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, rgba(255,255,255,0), rgba(255,255,255,0.2), rgba(255,255,255,0));
        }

        body.finance-shell .finance-total-label {
            color: rgba(255,255,255,0.8);
            font-size: 12px;
            font-weight: 850;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        body.finance-shell .finance-total-amount {
            color: #fff;
            font-size: 36px;
            font-weight: 900;
            line-height: 1;
            margin-top: 6px;
        }

        body.finance-shell .finance-total-pending {
            color: rgba(255,255,255,0.7);
            font-size: 12px;
            margin-top: 8px;
        }

        body.finance-shell .finance-btn-add-expense {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 11px 18px;
            background: #fff;
            color: #4f24e8;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 850;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        body.finance-shell .finance-btn-add-expense:hover {
            background: rgba(255,255,255,0.92);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        body.finance-shell .finance-btn-add-expense:active {
            transform: translateY(0);
        }

        /* Expenses Section */
        body.finance-shell .finance-expenses-section {
            display: grid;
            gap: 16px;
        }

        body.finance-shell .finance-section-header-simple {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        body.finance-shell .finance-section-title-simple {
            font-size: 18px;
            font-weight: 850;
            color: #0a0e27;
            margin: 0;
        }

        body.finance-shell .finance-expense-count {
            color: #64748b;
            font-size: 13px;
            font-weight: 700;
        }

        body.finance-shell .finance-empty-state-inline {
            padding: 32px 20px;
            text-align: center;
            background: #f8fafc;
            border: 1px solid rgba(226,232,240,0.6);
            border-radius: 10px;
            color: #64748b;
        }

        body.finance-shell .finance-empty-text {
            font-size: 14px;
            line-height: 1.5;
        }

        body.finance-shell .finance-expense-list-simple {
            display: grid;
            gap: 12px;
        }

        body.finance-shell .finance-expense-item {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 16px;
            background: #fff;
            border: 1px solid rgba(226,232,240,0.6);
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        body.finance-shell .finance-expense-item:hover {
            border-color: rgba(79,36,232,0.2);
            box-shadow: 0 4px 12px rgba(15,23,42,0.06);
        }

        body.finance-shell .finance-expense-info {
            flex: 1;
            min-width: 0;
        }

        body.finance-shell .finance-expense-category {
            font-size: 14px;
            font-weight: 850;
            color: #0a0e27;
        }

        body.finance-shell .finance-expense-description {
            font-size: 13px;
            color: #64748b;
            margin-top: 4px;
            line-height: 1.3;
        }

        body.finance-shell .finance-expense-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 8px;
        }

        body.finance-shell .finance-status-small {
            display: inline-flex;
            padding: 4px 8px;
            background: #f1f5f9;
            color: #334155;
            font-size: 10px;
            font-weight: 800;
            border-radius: 6px;
            text-transform: capitalize;
        }

        body.finance-shell .finance-expense-date-simple {
            font-size: 12px;
            color: #94a3b8;
        }

        body.finance-shell .finance-expense-amount-section {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 8px;
            white-space: nowrap;
        }

        body.finance-shell .finance-expense-amount-display {
            font-size: 16px;
            font-weight: 850;
            color: #4f24e8;
        }

        body.finance-shell .finance-btn-delete-expense {
            font-size: 11px;
            font-weight: 800;
            color: #dc2626;
            background: none;
            border: none;
            cursor: pointer;
            padding: 2px 6px;
            transition: color 0.2s ease;
        }

        body.finance-shell .finance-btn-delete-expense:hover {
            color: #991b1b;
        }

        /* Modal Styles */
        body.finance-shell .finance-modal-sheet {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            max-height: 92vh;
            overflow-y: auto;
            border-radius: 16px 16px 0 0;
            background: #fff;
            padding: 20px;
            box-shadow: 0 20px 60px rgba(15,23,42,0.15);
        }

        @media (min-width: 640px) {
            body.finance-shell .finance-modal-sheet {
                left: 50%;
                right: auto;
                bottom: auto;
                top: 50%;
                width: min(92vw, 520px);
                border-radius: 14px;
                transform: translate(-50%, -50%);
            }
        }

        body.finance-shell .finance-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
        }

        body.finance-shell .finance-modal-title {
            font-size: 20px;
            font-weight: 850;
            color: #0a0e27;
            margin: 0;
        }

        body.finance-shell .finance-modal-close-btn {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            border: none;
            border-radius: 10px;
            font-size: 24px;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        body.finance-shell .finance-modal-close-btn:hover {
            background: #e2e8f0;
            color: #334155;
        }

        body.finance-shell .finance-modal-form {
            display: grid;
            gap: 20px;
        }

        body.finance-shell .finance-form-group {
            display: grid;
            gap: 8px;
        }

        body.finance-shell .finance-form-label {
            font-size: 13px;
            font-weight: 850;
            color: #0a0e27;
            text-transform: none;
        }

        body.finance-shell .finance-form-input,
        body.finance-shell .finance-form-input-file {
            width: 100%;
            padding: 12px 13px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            background: #fff;
            color: #111827;
            outline: none;
            transition: border-color 0.2s ease;
        }

        body.finance-shell .finance-form-input:focus,
        body.finance-shell .finance-form-input-file:focus {
            border-color: #4f24e8;
            box-shadow: 0 0 0 3px rgba(79,36,232,0.1);
        }

        body.finance-shell .finance-form-input-file {
            padding: 8px 12px;
        }

        body.finance-shell .finance-form-help {
            font-size: 12px;
            color: #64748b;
            margin-top: 2px;
        }

        body.finance-shell .finance-modal-actions {
            display: flex;
            gap: 12px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
        }

        body.finance-shell .finance-success-alert {
            padding: 14px 16px;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-radius: 10px;
            color: #065f46;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 18px;
        }

        body.finance-shell .finance-error-alert {
            padding: 14px 16px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            color: #7f1d1d;
            font-size: 14px;
            margin-bottom: 18px;
        }

        body.finance-shell .finance-job-workspace {
            display: grid;
            gap: 18px;
        }

        body.finance-shell .finance-job-summary {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(260px, 340px);
            gap: 22px;
            align-items: stretch;
            padding: 24px;
        }

        body.finance-shell .finance-total-card {
            display: flex;
            min-height: 188px;
            flex-direction: column;
            justify-content: center;
            border-radius: 8px;
            background:
                radial-gradient(circle at 92% 18%, rgba(139,92,246,0.24), transparent 38%),
                linear-gradient(135deg, #071237, #35109c);
            padding: 26px;
            color: #fff;
        }

        body.finance-shell .finance-job-row {
            display: grid;
            grid-template-columns: minmax(0, 1.45fr) minmax(160px, 0.8fr) minmax(140px, 0.65fr) minmax(130px, 0.7fr) 92px;
            gap: 18px;
            align-items: center;
            padding: 18px 20px;
            border-top: 1px solid #eef2f7;
        }

        body.finance-shell .finance-job-row:first-child {
            border-top: 0;
        }

        body.finance-shell .finance-job-row:hover {
            background: #fbfdff;
        
            margin-top: 12px;
            font-size: clamp(32px, 4vw, 44px);
            line-height: 1;
            font-weight: 900;
        }

        body.finance-shell .finance-total-note {
            margin-top: 12px;
            color: rgba(255,255,255,0.76);
            font-size: 13px;
            font-weight: 700;
        }

        body.finance-shell .finance-expense-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 18px 20px;
            border-bottom: 1px solid #e2e8f0;
        }

        body.finance-shell .finance-expense-list {
            display: grid;
        }

        body.finance-shell .finance-expense-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(120px, auto);
            gap: 18px;
            align-items: center;
            padding: 18px 20px;
            border-top: 1px solid #f1f5f9;
        }

        body.finance-shell .finance-expense-row:first-child {
            border-top: 0;
        }

        body.finance-shell .finance-expense-amount {
            color: #020617;
            font-size: 18px;
            font-weight: 900;
            text-align: right;
        }

        body.finance-shell .finance-expense-date {
            margin-top: 5px;
            color: #64748b;
            font-size: 12px;
            font-weight: 800;
            text-align: right;
        }

        body.finance-shell .finance-expense-total {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 16px 20px;
            color: #020617;
            font-size: 18px;
            font-weight: 900;
        }

        body.finance-shell .finance-modal-card {
            border-radius: 8px;
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

        /* Premium Overview Card Styles */
        body.finance-shell .finance-section-container {
            margin-bottom: 28px;
        }

        body.finance-shell .finance-section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 18px;
            padding: 0 2px;
        }

        body.finance-shell .finance-overview-cards {
            display: grid;
            gap: 18px;
        }

        body.finance-shell .finance-overview-subsection {
            display: grid;
            gap: 12px;
        }

        body.finance-shell .finance-subsection-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 2px;
        }

        body.finance-shell .finance-subsection-title {
            color: #0a0e27;
            font-size: 16px;
            font-weight: 850;
            letter-spacing: 0.02em;
        }

        body.finance-shell .finance-count-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 8px;
            background: #f0eaff;
            color: #4f24e8;
            font-size: 12px;
            font-weight: 850;
        }

        body.finance-shell .finance-card-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        body.finance-shell .finance-overview-card {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding: 16px;
            background: linear-gradient(135deg, #fff 0%, #f9fafb 100%);
            border: 1px solid rgba(203,213,225,0.6);
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(15,23,42,0.06);
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        body.finance-shell .finance-overview-card::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, rgba(255,255,255,0), rgba(255,255,255,1), rgba(255,255,255,0));
        }

        body.finance-shell .finance-overview-card:hover {
            border-color: rgba(79,36,232,0.3);
            box-shadow: 0 10px 24px rgba(79,36,232,0.12);
            transform: translateY(-2px);
        }

        body.finance-shell .finance-overview-card:active {
            transform: translateY(0);
        }

        body.finance-shell .finance-card-icon {
            display: inline-flex;
            width: 44px;
            height: 44px;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: linear-gradient(135deg, #f0eaff 0%, #e8e0ff 100%);
            color: #4f24e8;
            font-size: 13px;
            font-weight: 900;
            flex-shrink: 0;
        }

        body.finance-shell .finance-card-content {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 0;
            flex: 1;
        }

        body.finance-shell .finance-card-title {
            color: #0a0e27;
            font-size: 14px;
            font-weight: 850;
            line-height: 1.3;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        body.finance-shell .finance-card-client {
            color: #64748b;
            font-size: 12px;
            line-height: 1.3;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        body.finance-shell .finance-card-status {
            display: inline-flex;
            width: fit-content;
            padding: 5px 10px;
            border-radius: 8px;
            background: #f1f5f9;
            color: #334155;
            font-size: 11px;
            font-weight: 800;
            text-transform: capitalize;
            margin-top: 2px;
        }

        body.finance-shell .finance-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 40px 20px;
            text-align: center;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 12px;
            border: 1px solid rgba(226,232,240,0.6);
        }

        body.finance-shell .finance-empty-icon {
            font-size: 40px;
            line-height: 1;
        }

        body.finance-shell .finance-empty-title {
            color: #0a0e27;
            font-size: 16px;
            font-weight: 850;
        }

        body.finance-shell .finance-empty-text {
            color: #64748b;
            font-size: 13px;
            line-height: 1.4;
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
                left: 0;
                min-height: 72px;
                padding: 10px 14px 10px 68px;
            }

            body.finance-shell .hamburger {
                top: 13px;
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
                padding: 0 6px 22px;
            }

            body.finance-shell .admin-content {
                padding: 0;
            }

            body.finance-shell .finance-page {
                padding: 96px 14px 18px;
            }

            body.finance-shell .finance-header {
                align-items: flex-start;
                flex-direction: column;
                margin-top: 0;
            }

            body.finance-shell .finance-stats {
                grid-template-columns: 1fr;
                gap: 14px;
            }

            body.finance-shell .finance-dashboard-grid {
                grid-template-columns: 1fr;
                gap: 14px;
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

            body.finance-shell .finance-dashboard-row {
                grid-template-columns: 48px minmax(0, 1fr);
            }

            body.finance-shell .finance-dashboard-row .finance-status,
            body.finance-shell .finance-dashboard-row .finance-btn {
                grid-column: 1 / -1;
            }

            body.finance-shell .finance-job-row {
                grid-template-columns: 1fr;
                gap: 12px;
                padding: 15px;
            }

            body.finance-shell .finance-job-row .finance-btn {
                width: 100%;
            }

            body.finance-shell .finance-job-summary {
                grid-template-columns: 1fr;
                gap: 14px;
                padding: 18px;
            }

            body.finance-shell .finance-job-meta {
                grid-template-columns: 1fr;
                gap: 12px;
                margin-top: 18px;
            }

            body.finance-shell .finance-expense-head {
                align-items: stretch;
                flex-direction: column;
            }

            body.finance-shell .finance-expense-head .finance-btn {
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

            /* Premium Mobile Finance Shell Styles */
            body.finance-shell .finance-mobile-topbar {
                min-height: 70px;
                padding: 12px 14px 12px 64px;
                border-bottom: 1px solid rgba(226,232,240,0.5);
                backdrop-filter: blur(10px);
                background: rgba(255,255,255,0.92);
            }

            body.finance-shell .finance-topbar-title {
                display: none;
            }

            body.finance-shell .finance-topbar-user {
                gap: 10px;
            }

            body.finance-shell .finance-topbar-name {
                max-width: 100px;
                font-size: 13px;
                font-weight: 850;
            }

            body.finance-shell .finance-bell {
                display: none;
            }

            body.finance-shell .finance-topbar-role {
                display: none;
            }

            body.finance-shell .finance-avatar {
                width: 40px;
                height: 40px;
                font-size: 14px;
                box-shadow: 0 4px 12px rgba(79,36,232,0.24);
            }

            body.finance-shell .hamburger {
                top: 14px;
                left: 12px;
                width: 40px;
                height: 40px;
                border-radius: 10px;
                background: rgba(255,255,255,0.95);
                border: 1px solid rgba(226,232,240,0.6);
                font-size: 19px;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 2px 8px rgba(15,23,42,0.08);
            }

            body.finance-shell .sidebar,
            body.finance-shell .sidebar.collapsed {
                width: min(85vw, 300px);
                max-width: 300px;
                padding: 16px;
                border-radius: 0;
            }

            body.finance-shell .admin-brand {
                padding: 0 6px 20px 8px;
            }

            body.finance-shell .admin-brand img {
                width: 42px;
                height: 42px;
            }

            body.finance-shell .brand-name {
                font-size: 18px;
                margin-left: 2px;
            }

            body.finance-shell .brand-tagline {
                font-size: 11px;
                margin-left: 2px;
            }

            body.finance-shell .nav-item,
            body.finance-shell .nav-btn {
                min-height: 46px;
                padding: 11px 13px;
                font-size: 14px;
                border-radius: 10px;
                margin-bottom: 6px;
                transition: all 0.2s ease;
            }

            body.finance-shell .nav-item:active,
            body.finance-shell .nav-btn:active {
                background: rgba(79,36,232,0.08);
            }

            body.finance-shell .nav-icon {
                width: 28px;
                height: 28px;
                font-size: 10px;
                border-radius: 8px;
            }

            body.finance-shell .finance-page {
                padding: 88px 12px 24px 12px;
                background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            }

            body.finance-shell .finance-wrap {
                max-width: 100%;
            }

            body.finance-shell .finance-header {
                margin-bottom: 24px;
                padding: 0 2px;
            }

            body.finance-shell .finance-eyebrow {
                font-size: 10px;
                letter-spacing: 0.12em;
                color: #4f24e8;
                font-weight: 900;
                text-transform: uppercase;
            }

            body.finance-shell .finance-title {
                font-size: 26px;
                line-height: 1.18;
                margin-top: 8px;
                color: #0a0e27;
            }

            body.finance-shell .finance-subtitle {
                margin-top: 8px;
                font-size: 14px;
                line-height: 1.5;
                color: #64748b;
            }

            /* Premium Stats Grid Mobile */
            body.finance-shell .finance-stats {
                gap: 12px;
                margin-bottom: 20px;
                display: grid;
                grid-template-columns: 1fr;
            }

            body.finance-shell .finance-stat {
                min-height: auto;
                padding: 18px 16px;
                border: none;
                background: linear-gradient(135deg, #fff 0%, #f9fafb 100%);
                box-shadow: 0 8px 24px rgba(15,23,42,0.08);
                border-radius: 14px;
                position: relative;
                overflow: hidden;
            }

            body.finance-shell .finance-stat::before {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 1px;
                background: linear-gradient(90deg, rgba(255,255,255,0), rgba(255,255,255,1), rgba(255,255,255,0));
            }

            body.finance-shell .finance-stat-inner {
                gap: 14px;
                align-items: flex-start;
            }

            body.finance-shell .finance-stat-icon {
                width: 50px;
                height: 50px;
                border-radius: 14px;
                font-size: 14px;
                background: linear-gradient(135deg, #f0eaff 0%, #e8e0ff 100%);
                box-shadow: 0 4px 12px rgba(79,36,232,0.15);
                flex-shrink: 0;
            }

            body.finance-shell .finance-stat-label {
                font-size: 11px;
                letter-spacing: 0.04em;
                color: #64748b;
                font-weight: 850;
                text-transform: uppercase;
            }

            body.finance-shell .finance-stat-value {
                margin-top: 6px;
                font-size: 28px;
                color: #0a0e27;
                font-weight: 850;
                line-height: 1;
            }

            body.finance-shell .finance-stat-link,
            body.finance-shell .finance-card-link {
                margin-top: 14px;
                font-size: 12px;
                font-weight: 850;
                display: inline-flex;
                color: #4f24e8;
            }

            /* Premium Panel Styles Mobile */
            body.finance-shell .finance-dashboard-grid {
                gap: 14px;
            }

            body.finance-shell .finance-panel {
                border-radius: 14px;
                border: none;
                background: #fff;
                box-shadow: 0 8px 24px rgba(15,23,42,0.08);
                overflow: hidden;
            }

            body.finance-shell .finance-panel::before {
                content: "";
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 1px;
                background: linear-gradient(90deg, rgba(255,255,255,0), rgba(255,255,255,1), rgba(255,255,255,0));
            }

            body.finance-shell .finance-section-head {
                padding: 16px 16px;
                background: #fafbfc;
                border-bottom: 1px solid #f1f5f9;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            body.finance-shell .finance-section-title {
                font-size: 18px;
                padding-left: 12px;
                position: relative;
            }

            body.finance-shell .finance-section-title::before {
                left: -2px;
                width: 4px;
            }

            body.finance-shell .finance-section-head > .finance-card-link {
                display: none;
            }

            body.finance-shell .finance-row {
                gap: 10px;
                padding: 14px 16px;
                border-top: 1px solid #f9fafb;
            }

            body.finance-shell .finance-row:first-child {
                border-top: 0;
            }

            body.finance-shell .finance-row:hover {
                background: rgba(248,250,252,0.5);
            }

            body.finance-shell .finance-dashboard-row {
                grid-template-columns: 48px minmax(0, 1fr);
                min-height: auto;
                gap: 12px;
                align-items: center;
            }

            body.finance-shell .finance-dashboard-row .finance-stat-icon {
                width: 48px;
                height: 48px;
                font-size: 14px;
            }

            body.finance-shell .finance-row-title {
                font-size: 14px;
                font-weight: 850;
                color: #0a0e27;
                line-height: 1.3;
            }

            body.finance-shell .finance-row-meta {
                margin-top: 3px;
                font-size: 12px;
                color: #64748b;
                line-height: 1.3;
            }

            body.finance-shell .finance-status {
                display: inline-flex;
                padding: 6px 10px;
                border-radius: 8px;
                font-size: 11px;
                font-weight: 800;
                background: #f1f5f9;
                color: #334155;
                text-transform: capitalize;
            }

            body.finance-shell .finance-btn {
                min-height: 40px;
                padding: 10px 14px;
                font-size: 12px;
                border-radius: 10px;
                font-weight: 850;
                transition: all 0.2s ease;
            }

            body.finance-shell .finance-btn-primary {
                background: linear-gradient(135deg, #4f24e8, #3410b8);
                color: #fff;
                border: none;
                box-shadow: 0 4px 12px rgba(79,36,232,0.24);
            }

            body.finance-shell .finance-btn-primary:active {
                transform: scale(0.96);
                box-shadow: 0 2px 8px rgba(79,36,232,0.18);
            }

            body.finance-shell .finance-btn-secondary {
                background: #f1f5f9;
                border: 1px solid #e2e8f0;
                color: #4f24e8;
            }

            body.finance-shell .finance-btn-secondary:active {
                background: #e8edf5;
            }

            body.finance-shell .finance-dashboard-footer {
                padding: 14px 16px;
                border-top: 1px solid #f1f5f9;
                text-align: center;
            }

            body.finance-shell .finance-banner {
                margin-top: 28px;
                padding: 20px 16px;
                border-radius: 14px;
                gap: 16px;
                box-shadow: 0 12px 32px rgba(49, 19, 143, 0.18);
            }

            body.finance-shell .finance-banner-icon {
                width: 54px;
                height: 54px;
                font-size: 20px;
            }

            body.finance-shell .finance-banner-title {
                font-size: 16px;
                font-weight: 850;
            }

            body.finance-shell .finance-banner-text {
                font-size: 14px;
                margin-top: 3px;
            }

            body.finance-shell .finance-filter {
                grid-template-columns: 1fr;
                gap: 12px;
                padding: 14px;
                margin-bottom: 16px;
                border-radius: 14px;
                background: #fff;
                box-shadow: 0 4px 12px rgba(15,23,42,0.06);
            }

            body.finance-shell .finance-filter-compact {
                grid-template-columns: 1fr;
            }

            body.finance-shell .finance-field label {
                font-size: 11px;
                margin-bottom: 7px;
                font-weight: 850;
            }

            body.finance-shell .finance-field input,
            body.finance-shell .finance-field select,
            body.finance-shell .finance-field textarea {
                border-radius: 10px;
                padding: 11px 13px;
                font-size: 14px;
                border: 1px solid #e2e8f0;
                background: #fff;
            }

            body.finance-shell .finance-field input:focus,
            body.finance-shell .finance-field select:focus,
            body.finance-shell .finance-field textarea:focus {
                border-color: #4f24e8;
                box-shadow: 0 0 0 3px rgba(79,36,232,0.1);
                background: #fff;
            }

            body.finance-shell .finance-row-title {
                font-size: 15px;
            }

            body.finance-shell .finance-row-meta {
                font-size: 12px;
                line-height: 1.35;
            }

            body.finance-shell .finance-status {
                padding: 4px 8px;
                font-size: 11px;
            }

            body.finance-shell .finance-btn {
                min-height: 34px;
                border-radius: 8px;
                padding: 7px 10px;
                font-size: 12px;
            }

            body.finance-shell .finance-dashboard-footer {
                padding: 12px 14px;
            }

            body.finance-shell .finance-banner {
                align-items: flex-start;
                gap: 12px;
                margin-top: 16px;
                padding: 16px;
            }

            body.finance-shell .finance-banner-icon {
                width: 44px;
                height: 44px;
                font-size: 12px;
            }

            body.finance-shell .finance-banner-title {
                font-size: 15px;
            }

            body.finance-shell .finance-banner-text {
                font-size: 13px;
                line-height: 1.4;
            }

            body.finance-shell .finance-filter {
                gap: 9px;
                margin-bottom: 12px;
                padding: 10px;
            }

            body.finance-shell .finance-back-link {
                margin-bottom: 12px;
                font-size: 13px;
            }

            body.finance-shell .finance-job-title {
                font-size: 23px;
            }

            body.finance-shell .finance-job-label {
                font-size: 10px;
            }

            body.finance-shell .finance-job-value {
                font-size: 14px;
            }

            body.finance-shell .finance-total-card {
                min-height: 132px;
                padding: 18px;
            }

            body.finance-shell .finance-total-label {
                font-size: 10px;
            }

            body.finance-shell .finance-total-value {
                margin-top: 8px;
                font-size: 30px;
            }

            body.finance-shell .finance-total-note {
                margin-top: 8px;
                font-size: 12px;
            }

            body.finance-shell .finance-expense-head {
                padding: 14px;
            }

            body.finance-shell .finance-expense-row {
                grid-template-columns: 1fr;
                gap: 10px;
                padding: 14px;
            }

            body.finance-shell .finance-expense-amount,
            body.finance-shell .finance-expense-date {
                text-align: left;
            }

            body.finance-shell .finance-expense-amount {
                font-size: 17px;
            }

            body.finance-shell .finance-expense-total {
                align-items: flex-start;
                flex-direction: column;
                gap: 6px;
                padding: 14px;
                font-size: 16px;
            }

            body.finance-shell .finance-modal-card {
                max-height: 88vh;
                padding: 16px;
            }

            body.finance-shell .finance-field label {
                font-size: 11px;
                margin-bottom: 5px;
            }

            body.finance-shell .finance-field input,
            body.finance-shell .finance-field select,
            body.finance-shell .finance-field textarea {
                min-height: 38px;
                border-radius: 8px;
                padding: 8px 10px;
                font-size: 14px;
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

            /* Mobile Overview Cards */
            body.finance-shell .finance-section-container {
                margin-bottom: 20px;
            }

            body.finance-shell .finance-section-header {
                padding: 0;
                margin-bottom: 14px;
            }

            body.finance-shell .finance-overview-cards {
                gap: 14px;
            }

            body.finance-shell .finance-overview-subsection {
                gap: 10px;
            }

            body.finance-shell .finance-subsection-header {
                padding: 0;
                margin-bottom: 8px;
            }

            body.finance-shell .finance-subsection-title {
                font-size: 15px;
                letter-spacing: 0.01em;
            }

            body.finance-shell .finance-count-badge {
                width: 24px;
                height: 24px;
                font-size: 11px;
            }

            body.finance-shell .finance-card-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            body.finance-shell .finance-overview-card {
                flex-direction: row;
                align-items: center;
                padding: 14px;
                gap: 12px;
                border-radius: 10px;
                box-shadow: 0 4px 12px rgba(15,23,42,0.06);
            }

            body.finance-shell .finance-overview-card:hover {
                box-shadow: 0 8px 18px rgba(79,36,232,0.1);
            }

            body.finance-shell .finance-card-icon {
                width: 42px;
                height: 42px;
                font-size: 13px;
                flex-shrink: 0;
            }

            body.finance-shell .finance-card-content {
                flex: 1;
                min-width: 0;
            }

            body.finance-shell .finance-card-title {
                font-size: 13px;
                -webkit-line-clamp: 1;
                line-clamp: 1;
            }

            body.finance-shell .finance-card-client {
                font-size: 11px;
            }

            body.finance-shell .finance-card-status {
                padding: 4px 8px;
                font-size: 10px;
            }

            body.finance-shell .finance-empty-state {
                padding: 30px 16px;
                gap: 10px;
            }

            body.finance-shell .finance-empty-icon {
                font-size: 32px;
            }

            body.finance-shell .finance-empty-title {
                font-size: 15px;
            }

            body.finance-shell .finance-empty-text {
                font-size: 12px;
            }
        }

        @media (max-width: 380px) {
            body.finance-shell .finance-mobile-topbar {
                padding-right: 8px;
            }

            body.finance-shell .finance-topbar-name {
                max-width: 92px;
            }

            body.finance-shell .finance-page {
                padding-left: 8px;
                padding-right: 8px;
            }

            body.finance-shell .finance-title {
                font-size: 21px;
            }

            body.finance-shell .finance-stat {
                padding: 12px;
            }

            body.finance-shell .finance-section-head,
            body.finance-shell .finance-row {
                padding-left: 12px;
                padding-right: 12px;
            }

            /* Mobile Jobs List */
            body.finance-shell .finance-filter-bar {
                grid-template-columns: 1fr;
                padding: 12px;
                margin-bottom: 16px;
            }

            body.finance-shell .finance-filter-content {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            body.finance-shell .finance-job-card {
                padding: 14px;
                gap: 12px;
            }

            body.finance-shell .finance-job-header {
                flex-direction: column;
                align-items: flex-start;
            }

            body.finance-shell .finance-job-title {
                font-size: 15px;
            }

            body.finance-shell .finance-job-info {
                width: 100%;
                grid-template-columns: 1fr;
                gap: 10px;
            }

            body.finance-shell .finance-job-detail {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                padding-bottom: 10px;
                border-bottom: 1px solid #f1f5f9;
            }

            body.finance-shell .finance-job-detail:last-child {
                border-bottom: none;
                padding-bottom: 0;
            }

            body.finance-shell .finance-job-detail-amount {
                text-align: right;
                border-bottom: none !important;
                padding-bottom: 0 !important;
            }

            /* Mobile Job Detail Page */
            body.finance-shell .finance-job-header-section {
                margin-bottom: 20px;
                padding-bottom: 16px;
            }

            body.finance-shell .finance-job-page-title {
                font-size: 22px;
                margin-bottom: 14px;
            }

            body.finance-shell .finance-job-meta-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            body.finance-shell .finance-total-spent-card {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
                padding: 18px;
                margin-bottom: 18px;
            }

            body.finance-shell .finance-btn-add-expense {
                width: 100%;
                justify-content: center;
            }

            body.finance-shell .finance-section-header-simple {
                margin-bottom: 8px;
            }

            body.finance-shell .finance-section-title-simple {
                font-size: 16px;
            }

            body.finance-shell .finance-expense-item {
                flex-direction: column;
                gap: 10px;
                padding: 12px;
            }

            body.finance-shell .finance-expense-amount-section {
                width: 100%;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
            }

            /* Mobile Modal */
            body.finance-shell .finance-modal-sheet {
                padding: 16px;
                border-radius: 14px 14px 0 0;
            }

            body.finance-shell .finance-modal-header {
                margin-bottom: 16px;
                gap: 12px;
            }

            body.finance-shell .finance-modal-form {
                gap: 16px;
            }

            body.finance-shell .finance-form-input,
            body.finance-shell .finance-form-input-file {
                padding: 11px 12px;
                font-size: 14px;
            }

            body.finance-shell .finance-modal-actions {
                flex-direction: column;
                gap: 10px;
            }

            body.finance-shell .finance-modal-actions .finance-btn {
                width: 100%;
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
                @php
                    $financeUser = auth()->user();
                    $financeName = $financeUser?->name ?? 'Finance user';
                    $financeInitials = collect(explode(' ', trim($financeName)))
                        ->filter()
                        ->take(2)
                        ->map(fn ($part) => Illuminate\Support\Str::upper(Illuminate\Support\Str::substr($part, 0, 1)))
                        ->join('');
                @endphp
                <div class="finance-topbar-title">ARTSCI Finance</div>
                <div class="finance-topbar-user">
                    <span class="finance-bell" aria-hidden="true">FN</span>
                    <div>
                        <div class="finance-topbar-name">{{ $financeName }}</div>
                        <div class="finance-topbar-role">Finance</div>
                    </div>
                    <span class="finance-avatar" aria-hidden="true">{{ $financeInitials ?: 'FN' }}</span>
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
