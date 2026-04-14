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
            padding: 10px 12px;
            box-shadow: var(--brand-shadow);
            cursor: pointer;
            z-index: 30;
        }

        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            padding: 18px;
            border-right: 1px solid var(--brand-border);
            background: rgba(255,255,255,0.96);
            backdrop-filter: blur(10px);
            box-shadow: 6px 0 30px rgba(0,0,0,0.05);
            display: grid;
            align-content: start;
            gap: 12px;
            transition: width 0.2s ease, transform 0.2s ease, padding 0.2s ease;
            z-index: 20;
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
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 20px;
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
        }

        .nav-section {
            margin-top: 10px;
            padding: 0 12px;
            color: var(--brand-muted);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .sidebar.collapsed .nav-section { display: none; }

        .nav-btn,
        .nav-item {
            border: 1px solid var(--brand-border);
            background: #fff;
            color: var(--brand-dark);
            padding: 11px 12px;
            border-radius: 8px;
            font-weight: 700;
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

        .admin-user {
            margin-top: 12px;
            display: grid;
            gap: 8px;
        }

        .admin-content {
            padding: 22px;
            min-width: 0;
        }

        @media (max-width: 960px) {
            .admin-shell { grid-template-columns: 1fr; }
            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                width: 260px;
                transform: translateX(-110%);
            }
            .sidebar.open { transform: translateX(0); }
            .sidebar.collapsed { width: 260px; padding: 18px; }
            .admin-content { padding: 72px 14px 18px; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="admin-shell">
        <button class="hamburger" id="toggleSidebar" type="button" aria-label="Toggle admin menu" aria-expanded="false">☰</button>
        @include('admin.partials.sidebar')

        <main class="admin-content">
            @yield('content')
        </main>
    </div>

    <script>
        (function () {
            const sidebar = document.getElementById('sidebar');
            const toggleSidebar = document.getElementById('toggleSidebar');
            if (!sidebar || !toggleSidebar || toggleSidebar.dataset.bound === '1') return;

            toggleSidebar.dataset.bound = '1';
            toggleSidebar.addEventListener('click', () => {
                const isMobile = window.innerWidth <= 960;
                if (isMobile) {
                    sidebar.classList.toggle('open');
                } else {
                    document.body.classList.toggle('collapsed');
                    sidebar.classList.toggle('collapsed');
                }
                toggleSidebar.setAttribute('aria-expanded', sidebar.classList.contains('open') ? 'true' : 'false');
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
