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
            z-index: 30;
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
            z-index: 20;
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
            z-index: 19;
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

        @media (max-width: 960px) {
            .admin-shell { grid-template-columns: 1fr; }
            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                height: 100vh;
                height: 100dvh;
                width: 260px;
                transform: translateX(-110%);
                transition: transform 0.3s ease;
            }
            .sidebar.open { transform: translateX(0); }
            .sidebar.open + .admin-sidebar-backdrop { display: block; }
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
                width: min(86vw, 300px);
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
                min-width: 640px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="admin-shell">
        <button class="hamburger" id="toggleSidebar" type="button" aria-label="Toggle admin menu" aria-expanded="false">☰</button>
        @include('admin.partials.sidebar')
        <div class="admin-sidebar-backdrop" id="adminSidebarBackdrop" aria-hidden="true"></div>

        <main class="admin-content">
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
                toggleSidebar.setAttribute('aria-expanded', 'false');
            };

            toggleSidebar.addEventListener('click', () => {
                const isMobile = window.innerWidth <= 960;
                if (isMobile) {
                    document.body.classList.remove('collapsed');
                    sidebar.classList.remove('collapsed');
                    const isOpen = sidebar.classList.toggle('open');
                    toggleSidebar.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                } else {
                    document.body.classList.toggle('collapsed');
                    sidebar.classList.toggle('collapsed');
                }
            });

            sidebarBackdrop?.addEventListener('click', closeMobileSidebar);
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
