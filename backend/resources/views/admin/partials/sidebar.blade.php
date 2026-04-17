@php
    $sidebarMode = trim($__env->yieldContent('admin_sidebar_mode'));
    $isDashboardSidebar = $sidebarMode === 'dashboard';

    $dashboardUrl = route('admin.dashboard');
    $items = [
        'overview' => [
            ['key' => 'overview', 'label' => 'Overview', 'route' => 'admin.dashboard', 'tab' => 'overview', 'icon' => 'OV'],
        ],
        'commerce' => [
            ['key' => 'categories', 'label' => 'Categories', 'route' => 'admin.solutions.index', 'tab' => 'categories', 'icon' => 'CA'],
            ['key' => 'products', 'label' => 'Products', 'route' => 'admin.products.index', 'tab' => 'menu', 'icon' => 'PR'],
            ['key' => 'orders', 'label' => 'Orders', 'route' => 'admin.orders.index', 'tab' => 'orders', 'icon' => 'OR'],
            ['key' => 'pos', 'label' => 'POS', 'route' => 'pos.index', 'tab' => 'pos', 'icon' => 'PS'],
        ],
        'operations' => [
            ['key' => 'clients', 'label' => 'Clients', 'route' => 'admin.clients.index', 'tab' => 'clients', 'icon' => 'CL'],
            ['key' => 'job-requests', 'label' => 'Job Requests', 'route' => 'admin.job-requests.index', 'tab' => 'job-requests', 'icon' => 'JR'],
            ['key' => 'projects', 'label' => 'Projects', 'route' => 'admin.projects.index', 'tab' => 'projects', 'icon' => 'PJ'],
            ['key' => 'tasks', 'label' => 'Tasks', 'route' => 'admin.tasks.index', 'tab' => 'tasks', 'icon' => 'TK'],
            ['key' => 'field-reports', 'label' => 'Field Reports', 'route' => 'admin.field-reports.index', 'tab' => 'field-reports', 'icon' => 'FR'],
        ],
        'system' => [
            ['key' => 'users', 'label' => 'Users', 'route' => 'admin.users.index', 'tab' => 'users', 'icon' => 'US'],
            ['key' => 'health', 'label' => 'Health', 'route' => 'admin.dashboard', 'tab' => 'health', 'icon' => 'HE'],
            ['key' => 'site', 'label' => 'Public Site', 'route' => 'admin.dashboard', 'tab' => 'site', 'icon' => 'SI'],
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
            'job-requests' => request()->routeIs('admin.job-requests.*'),
            'projects' => request()->routeIs('admin.projects.*'),
            'tasks' => request()->routeIs('admin.tasks.*'),
            'field-reports' => request()->routeIs('admin.field-reports.*'),
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
                        <span class="nav-icon" aria-hidden="true">{{ $item['icon'] }}</span>
                        <span class="nav-label">{{ $item['label'] }}</span>
                    </button>
                @else
                    @php($href = $item['route'] === 'admin.dashboard' && $item['tab'] !== 'overview' ? $dashboardUrl . '#' . $item['tab'] : route($item['route']))
                    <a href="{{ $href }}" class="nav-item {{ $routeActive($item) ? 'active' : '' }}">
                        <span class="nav-icon" aria-hidden="true">{{ $item['icon'] }}</span>
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
            <button type="submit" class="nav-btn" style="width:100%; justify-content:center;" aria-label="Logout" title="Logout">
                <span class="nav-icon" aria-hidden="true">LO</span>
                <span class="nav-label">Logout</span>
            </button>
        </form>
    </div>
</aside>
