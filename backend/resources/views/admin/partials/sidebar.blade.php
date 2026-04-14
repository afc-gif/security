@php
    $sidebarMode = trim($__env->yieldContent('admin_sidebar_mode'));
    $isDashboardSidebar = $sidebarMode === 'dashboard';

    $dashboardUrl = route('admin.dashboard');
    $items = [
        'overview' => [
            ['key' => 'overview', 'label' => 'Overview', 'route' => 'admin.dashboard', 'tab' => 'overview'],
        ],
        'commerce' => [
            ['key' => 'categories', 'label' => 'Categories', 'route' => 'admin.solutions.index', 'tab' => 'categories'],
            ['key' => 'products', 'label' => 'Products', 'route' => 'admin.products.index', 'tab' => 'menu'],
            ['key' => 'orders', 'label' => 'Orders', 'route' => 'admin.orders.index', 'tab' => 'orders'],
            ['key' => 'pos', 'label' => 'POS', 'route' => 'pos.index', 'tab' => 'pos'],
        ],
        'operations' => [
            ['key' => 'clients', 'label' => 'Clients', 'route' => 'admin.clients.index', 'tab' => 'clients'],
            ['key' => 'inspections', 'label' => 'Inspections', 'route' => 'admin.inspections.index', 'tab' => 'inspections'],
            ['key' => 'projects', 'label' => 'Projects', 'route' => 'admin.projects.index', 'tab' => 'projects'],
            ['key' => 'tasks', 'label' => 'Tasks', 'route' => 'admin.tasks.index', 'tab' => 'tasks'],
        ],
        'system' => [
            ['key' => 'users', 'label' => 'Users', 'route' => 'admin.users.index', 'tab' => 'users'],
            ['key' => 'health', 'label' => 'Health', 'route' => 'admin.dashboard', 'tab' => 'health'],
            ['key' => 'site', 'label' => 'Public Site', 'route' => 'admin.dashboard', 'tab' => 'site'],
        ],
    ];

    $sectionLabels = [
        'overview' => null,
        'commerce' => 'Commerce',
        'operations' => 'Operations',
        'system' => 'System',
    ];

    $routeActive = function (array $item): bool {
        return match ($item['key']) {
            'overview' => request()->routeIs('admin.dashboard'),
            'categories' => request()->routeIs('admin.solutions.*'),
            'products' => request()->routeIs('admin.products.*'),
            'orders' => request()->routeIs('admin.orders.*'),
            'pos' => request()->routeIs('pos.*'),
            'clients' => request()->routeIs('admin.clients.*'),
            'inspections' => request()->routeIs('admin.inspections.*'),
            'projects' => request()->routeIs('admin.projects.*'),
            'tasks' => request()->routeIs('admin.tasks.*'),
            'users' => request()->routeIs('admin.users.*'),
            default => false,
        };
    };
@endphp

<aside class="sidebar admin-sidebar" id="sidebar">
    <div class="admin-brand">
        <img src="{{ asset('Artsci Logo REAL 1.webp') }}" alt="ARTSCI logo">
        <div class="brand-text">
            <span class="brand-name">ARTSCI</span>
            <span class="brand-tagline">Admin & POS</span>
        </div>
    </div>

    <nav class="admin-nav" aria-label="Admin navigation">
        @foreach ($items as $section => $sectionItems)
            @if ($sectionLabels[$section])
                <div class="nav-section">{{ $sectionLabels[$section] }}</div>
            @endif

            @foreach ($sectionItems as $item)
                @if ($isDashboardSidebar)
                    <button class="nav-btn {{ $item['key'] === 'overview' ? 'active' : '' }}" type="button" data-tab="{{ $item['tab'] }}">
                        <span class="nav-label">{{ $item['label'] }}</span>
                    </button>
                @else
                    @php($href = $item['route'] === 'admin.dashboard' && $item['tab'] !== 'overview' ? $dashboardUrl . '#' . $item['tab'] : route($item['route']))
                    <a href="{{ $href }}" class="nav-item {{ $routeActive($item) ? 'active' : '' }}">
                        <span class="nav-label">{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
        @endforeach
    </nav>

    <div class="admin-user">
        <div class="admin-user-meta">
            Signed in as {{ auth()->user()->name ?? 'User' }}
        </div>
        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
            @csrf
            <button type="submit" class="nav-btn" style="width:100%; justify-content:center;">
                <span class="nav-label">Logout</span>
            </button>
        </form>
    </div>
</aside>
