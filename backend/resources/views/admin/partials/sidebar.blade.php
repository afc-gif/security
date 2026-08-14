@php
    use App\Models\FinancePermission;

    $sidebarMode = trim($__env->yieldContent('admin_sidebar_mode'));
    $isDashboardSidebar = $sidebarMode === 'dashboard';
    $currentUser = auth()->user();
    $isFinanceUser = $currentUser?->isFinance() ?? false;
    $canViewFinance = $currentUser?->hasFinancePermission(FinancePermission::VIEW) ?? false;
    $financeInitials = collect(explode(' ', trim($currentUser?->name ?? 'User')))
        ->filter()
        ->take(2)
        ->map(fn ($part) => Illuminate\Support\Str::upper(Illuminate\Support\Str::substr($part, 0, 1)))
        ->join('');

    $dashboardUrl = route('admin.dashboard');
    $items = $isFinanceUser ? [
        'finance_main' => [
            ['key' => 'finance-overview', 'label' => 'Overview', 'route' => 'finance.dashboard', 'tab' => null, 'icon' => 'OV'],
            ['key' => 'finance-quotations', 'label' => 'Quotations', 'route' => 'finance.quotations.index', 'tab' => null, 'icon' => 'QT'],
            ['key' => 'finance-jobs', 'label' => 'Jobs', 'route' => 'finance.jobs.index', 'tab' => null, 'icon' => 'JB'],
            ['key' => 'finance-projects', 'label' => 'Projects', 'route' => 'finance.projects.index', 'tab' => null, 'icon' => 'PJ'],
            ['key' => 'finance-pos-sales', 'label' => 'POS Sales', 'route' => 'finance.pos-sales.index', 'tab' => null, 'icon' => 'PS'],
            ['key' => 'finance-office-expenses', 'label' => 'Office Expenses', 'route' => 'finance.office-expenses.index', 'tab' => null, 'icon' => 'OE'],
            ['key' => 'finance-analysis', 'label' => 'Analysis', 'route' => 'finance.analysis', 'tab' => null, 'icon' => 'AN'],
            ['key' => 'finance-reports', 'label' => 'Reports', 'route' => 'finance.reports.index', 'tab' => null, 'icon' => 'RP'],
        ],
    ] : [
        'overview' => [
            ['key' => 'overview', 'label' => 'Overview', 'route' => 'admin.dashboard', 'tab' => 'overview', 'icon' => 'OV'],
        ],
        'commerce' => [
            ['key' => 'categories', 'label' => 'Categories', 'route' => 'admin.solutions.index', 'tab' => 'categories', 'icon' => 'CA'],
            ['key' => 'products', 'label' => 'Products', 'route' => 'admin.products.index', 'tab' => 'menu', 'icon' => 'PR'],
            ['key' => 'installations', 'label' => 'Installations', 'route' => 'admin.installations.index', 'tab' => null, 'icon' => 'IN'],
            ['key' => 'orders', 'label' => 'Orders', 'route' => 'admin.orders.index', 'tab' => 'orders', 'icon' => 'OR'],
            ['key' => 'pos', 'label' => 'POS', 'route' => 'pos.index', 'tab' => 'pos', 'icon' => 'PS'],
        ],
        'operations' => [
            ['key' => 'operations-overview', 'label' => 'Overview', 'route' => 'admin.operations.overview', 'tab' => null, 'icon' => 'OV'],
            ['key' => 'clients', 'label' => 'Clients', 'route' => 'admin.clients.index', 'tab' => 'clients', 'icon' => 'CL'],
            ['key' => 'job-requests', 'label' => 'Job Requests', 'route' => 'admin.job-requests.index', 'tab' => 'job-requests', 'icon' => 'JR'],
            ['key' => 'field-categories', 'label' => 'Job Categories', 'route' => 'admin.service-categories.index', 'tab' => null, 'icon' => 'JC'],
            ['key' => 'job-inbox', 'label' => 'Job Inbox', 'route' => 'admin.job-inbox.index', 'tab' => 'job-inbox', 'icon' => 'JI'],
            ['key' => 'projects', 'label' => 'Projects', 'route' => 'admin.projects.index', 'tab' => 'projects', 'icon' => 'PJ'],
            // Legacy Tasks, Field Reports, and Inspections modules remain routed but are hidden from the main workflow navigation.
        ],
        'system' => [
            ['key' => 'users', 'label' => 'Users', 'route' => 'admin.users.index', 'tab' => 'users', 'icon' => 'US'],
            ['key' => 'health', 'label' => 'Health', 'route' => 'admin.dashboard', 'tab' => 'health', 'icon' => 'HE'],
            ['key' => 'site', 'label' => 'Public Site', 'route' => 'admin.dashboard', 'tab' => 'site', 'icon' => 'SI'],
        ],
    ];

    if ($canViewFinance && !$isFinanceUser) {
        $items['finance'] = [
            ['key' => 'finance', 'label' => 'Finance', 'route' => 'finance.dashboard', 'tab' => null, 'icon' => 'FN'],
        ];
    }

    $sectionLabels = [
        'finance_main' => 'Main',
        'overview' => null,
        'commerce' => 'Commerce',
        'operations' => 'Operations',
        'system' => 'System',
        'finance' => 'Finance',
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
            'field-categories' => request()->routeIs('admin.service-categories.*'),
            'operations-overview' => request()->routeIs('admin.operations.overview'),
            'job-inbox' => request()->routeIs('admin.job-inbox.*'),
            'projects' => request()->routeIs('admin.projects.*'),
            'tasks' => request()->routeIs('admin.tasks.*'),
            'field-reports' => request()->routeIs('admin.field-reports.*'),
            'users' => request()->routeIs('admin.users.*'),
            'finance' => request()->routeIs('finance.*'),
            'finance-overview' => request()->routeIs('finance.dashboard'),
            'finance-quotations' => request()->routeIs('finance.quotations.*'),
            'finance-jobs' => request()->routeIs('finance.jobs.*'),
            'finance-projects' => request()->routeIs('finance.projects.*') || request()->routeIs('finance.material-costs.*'),
            'finance-analysis' => request()->routeIs('finance.analysis.*') || request()->routeIs('finance.analysis'),
            'finance-pos-sales' => request()->routeIs('finance.pos-sales.*'),
            'finance-office-expenses' => request()->routeIs('finance.office-expenses.*'),
            'finance-reports' => request()->routeIs('finance.reports.*'),
            default => false,
        };
    };
@endphp

<aside class="sidebar admin-sidebar" id="sidebar">
    <div class="admin-brand">
        <img src="{{ asset('Artsci Logo REAL 1.webp') }}" alt="ARTSCI logo">
        <div class="brand-text">
            <span class="brand-name">ARTSCI</span>
            <span class="brand-tagline">{{ $isFinanceUser ? 'Finance' : 'Admin & POS' }}</span>
        </div>
    </div>

    <nav class="admin-nav" aria-label="Admin navigation">
        @foreach ($items as $section => $sectionItems)
            @if ($sectionLabels[$section])
                <div class="nav-section">{{ $sectionLabels[$section] }}</div>
            @endif

            @foreach ($sectionItems as $item)
                @if ($isDashboardSidebar && $item['tab'])
                    <button class="nav-btn {{ $item['key'] === 'overview' ? 'active' : '' }}" type="button" data-tab="{{ $item['tab'] }}">
                        <span class="nav-icon" aria-hidden="true">{{ $item['icon'] }}</span>
                        <span class="nav-label">{{ $item['label'] }}</span>
                    </button>
                @else
                    @php($href = $item['route'] === 'admin.dashboard' && $item['tab'] && $item['tab'] !== 'overview' ? $dashboardUrl . '#' . $item['tab'] : route($item['route']))
                    <a href="{{ $href }}" class="nav-item {{ $routeActive($item) ? 'active' : '' }}" @if($routeActive($item)) aria-current="page" @endif>
                        <span class="nav-icon" aria-hidden="true">{{ $item['icon'] }}</span>
                        <span class="nav-label">{{ $item['label'] }}</span>
                    </a>
                @endif
            @endforeach
        @endforeach
    </nav>

    <div class="admin-user">
        @if($isFinanceUser)
            <div class="nav-section" style="margin-top:0;">Account</div>
            <a href="#finance-account" class="nav-item">
                <span class="nav-icon" aria-hidden="true">ME</span>
                <span class="nav-label">My Profile</span>
            </a>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="nav-btn" style="width:100%;" aria-label="Logout" title="Logout">
                    <span class="nav-icon" aria-hidden="true">LO</span>
                    <span class="nav-label">Logout</span>
                </button>
            </form>
            <div class="finance-sidebar-person">
                <span class="finance-avatar" aria-hidden="true">{{ $financeInitials ?: 'FN' }}</span>
                <div class="min-w-0">
                    <div class="finance-sidebar-name">{{ $currentUser?->name ?? 'Finance user' }}</div>
                    <div class="finance-sidebar-role">Finance</div>
                </div>
            </div>
        @else
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
        @endif
    </div>
</aside>
