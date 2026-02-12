<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ARTSCI Admin Console</title>
    <link rel="icon" href="{{ asset('Artsci Logo REAL 1.webp') }}" type="image/webp">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        :root {
            --brand-blue: #03A9F4;
            --brand-blue-strong: #0285C2;
            --brand-dark: #0A1428;
            --brand-ink: #0f172a;
            --brand-surface: #ffffff;
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
            display: grid;
            grid-template-columns: 260px 1fr;
            min-height: 100vh;
            transition: grid-template-columns 0.2s ease;
        }
        body.collapsed { grid-template-columns: 72px 1fr; }
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
        }
        .sidebar.collapsed { width: 72px; padding: 12px; }
        .brand {
            display: flex; align-items: center; gap: 12px; padding: 10px; border-radius: 12px;
            border: 1px solid var(--brand-border); background: #fff; box-shadow: 0 10px 24px rgba(0,0,0,0.06);
            transition: opacity 0.2s ease;
        }
        .sidebar.collapsed .brand { opacity: 0; pointer-events: none; height: 0; padding: 0; margin: 0; }
        .brand img { width: 48px; height: 48px; border-radius: 12px; object-fit: contain; }
        .brand-title { margin: 0; font-family: 'Playfair Display', Georgia, serif; font-size: 20px; }
        .muted { color: var(--brand-muted); font-size: 14px; margin: 2px 0 0; }
        .hamburger {
            display: block;
            position: fixed;
            top: 16px;
            left: 16px;
            background: #fff;
            border: 1px solid var(--brand-border);
            border-radius: 12px;
            padding: 10px 12px;
            box-shadow: var(--brand-shadow);
            cursor: pointer;
            z-index: 30;
        }
        nav { display: grid; gap: 6px; margin-top: 8px; }
        .nav-btn {
            border: 1px solid var(--brand-border);
            background: #fff;
            color: var(--brand-dark);
            padding: 11px 12px;
            border-radius: 12px;
            font-weight: 700;
            text-align: left;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: background 0.2s ease, color 0.2s ease;
        }
        .nav-btn.active { background: var(--brand-dark); color: #fff; box-shadow: var(--brand-shadow); }
        .nav-label { white-space: nowrap; }
        .sidebar.collapsed .nav-label { display: none; }
        main { padding: 22px; display: grid; gap: 18px; }
        .hero {
            background: linear-gradient(135deg, rgba(3,169,244,0.15), rgba(255,255,255,0.95));
            border: 1px solid var(--brand-border);
            border-radius: 18px;
            padding: 18px;
            box-shadow: var(--brand-shadow);
        }
        .hero-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; }
        button { border: none; border-radius: 12px; padding: 10px 14px; font-weight: 700; cursor: pointer; transition: transform 0.1s ease; }
        button:active { transform: translateY(1px); }
        .btn-primary { background: var(--brand-dark); color: #fff; }
        .btn-ghost { background: #fff; color: var(--brand-dark); border: 1px solid var(--brand-border); }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-top: 12px; }
        .stat { background: #fff; border: 1px solid var(--brand-border); border-radius: 14px; padding: 14px; box-shadow: 0 8px 24px rgba(0,0,0,0.05); }
        .stat h3 { margin: 0; font-size: 26px; }
        .stat span { color: var(--brand-muted); font-size: 13px; }
        .panels { display: grid; gap: 16px; }
        .panel { display: none; }
        .panel.active { display: block; }
        .card { background: #fff; border: 1px solid var(--brand-border); border-radius: 16px; padding: 16px; box-shadow: var(--brand-shadow); }
        .card h2 { margin: 0 0 8px; font-size: 18px; font-family: 'Playfair Display', Georgia, serif; }
        form { display: grid; gap: 8px; margin-top: 10px; }
        label { font-size: 13px; color: var(--brand-muted); }
        input, textarea, select {
            width: 100%; padding: 10px 12px;
            border: 1px solid var(--brand-border); border-radius: 12px;
            font: inherit; background: #fff;
        }
        .list { display: grid; gap: 8px; max-height: 380px; overflow: auto; }
        .item { border: 1px solid var(--brand-border); border-radius: 14px; padding: 12px; background: #fff; display: flex; justify-content: space-between; align-items: center; gap: 10px; }
        .item h4 { margin: 0 0 4px; font-size: 15px; }
        .pill { display: inline-flex; align-items: center; gap: 6px; padding: 5px 10px; border-radius: 999px; border: 1px solid var(--brand-border); font-size: 12px; color: var(--brand-muted); }
        .price { font-weight: 700; color: var(--brand-dark); }
        .row { display: flex; gap: 8px; flex-wrap: wrap; }
        .thumb { width: 64px; height: 64px; object-fit: cover; border-radius: 12px; border: 1px solid var(--brand-border); background: #fff; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; background: #fff; border: 1px solid var(--brand-border); border-radius: 14px; overflow: hidden; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid var(--brand-border); }
        th { color: var(--brand-muted); font-weight: 700; background: #f8fbff; }
        tr:last-child td { border-bottom: none; }
        .hidden { display: none; }
        .grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 14px; }
        .frame-wrap { border: 1px solid var(--brand-border); border-radius: 14px; overflow: hidden; box-shadow: var(--brand-shadow); background: #fff; }
        iframe { width: 100%; height: 70vh; border: none; }
        .menu-card { border: 1px solid var(--brand-border); border-radius: 16px; padding: 12px; background: #fff; box-shadow: 0 10px 24px rgba(0,0,0,0.04); display: grid; gap: 8px; }
        .menu-card-head { display:flex; justify-content:space-between; gap:8px; align-items:flex-start; flex-wrap:wrap; }
        .menu-card-title { margin:0; font-size:16px; }
        .menu-tags { display:flex; gap:6px; flex-wrap:wrap; }
        .menu-pill { border: 1px solid var(--brand-border); border-radius: 999px; padding: 4px 10px; font-size:12px; color: rgba(10,20,40,0.7); background:#fff; }
        .menu-pill.sold { border-color:#fca5a5; color:#b91c1c; background:#fef2f2; }
        .menu-pill.active { border-color:#bbf7d0; color:#166534; background:#f0fdf4; }
        .menu-meta { font-size:13px; color: rgba(10,20,40,0.7); }
        .menu-actions { display:flex; gap:6px; flex-wrap:wrap; }
        .alert-wrap { position: relative; }
        .alert-bell { background: #fff; border: 1px solid var(--brand-border); border-radius: 12px; padding: 8px 10px; font-size: 18px; cursor: pointer; position: relative; }
        .alert-badge { position: absolute; top: -6px; right: -6px; background: #ef4444; color: #fff; font-size: 11px; font-weight: 700; width: 20px; height: 20px; border-radius: 999px; display: flex; align-items: center; justify-content: center; }
        .alert-dropdown { position: absolute; right: 0; top: calc(100% + 8px); width: 320px; background: #fff; border: 1px solid var(--brand-border); border-radius: 14px; box-shadow: var(--brand-shadow); display: none; z-index: 40; max-height: 420px; overflow: auto; }
        .alert-wrap.open .alert-dropdown { display: block; }
        .alert-head { padding: 12px 14px; font-weight: 700; border-bottom: 1px solid var(--brand-border); position: sticky; top: 0; background: #fff; }
        .alert-list { display: grid; }
        .alert-item { padding: 12px 14px; border-bottom: 1px solid var(--brand-border); display: flex; gap: 10px; align-items: flex-start; }
        .alert-item:last-child { border-bottom: none; }
        .alert-title { font-weight: 700; font-size: 13px; }
        .alert-meta { font-size: 12px; color: var(--brand-muted); margin-top: 4px; }
        .alert-actions { margin-left: auto; }
        .alert-btn { border: none; background: transparent; color: #2563eb; font-weight: 700; font-size: 12px; cursor: pointer; }
        .alert-empty { padding: 12px 14px; color: var(--brand-muted); font-size: 12px; }
        .alert-footer { padding: 10px 14px; border-top: 1px solid var(--brand-border); text-align: center; background: #f8fafc; }
        .alert-footer a { color: #2563eb; font-weight: 700; font-size: 12px; text-decoration: none; }
        @media (max-width: 960px) {
            body { grid-template-columns: 1fr; }
            .sidebar { position: fixed; left: 0; top: 0; width: 260px; transform: translateX(-110%); z-index: 20; }
            .sidebar.open { transform: translateX(0); }
            .sidebar.collapsed { width: 260px; padding: 18px; }
        }
    </style>
</head>
<body>
    <button class="hamburger" id="toggleSidebar">☰</button>
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <img src="{{ asset('Artsci Logo REAL 1.webp') }}" alt="ARTSCI Logo">
            <div>
                <p class="brand-title">ARTSCI</p>
                <p class="muted">Admin & POS</p>
            </div>
        </div>
        <nav>
            <button class="nav-btn active" data-tab="overview"><span class="nav-label">Overview</span></button>
            <button class="nav-btn" data-tab="categories"><span class="nav-label">Categories</span></button>
            <button class="nav-btn" data-tab="menu"><span class="nav-label">Products</span></button>
            <button class="nav-btn" data-tab="users"><span class="nav-label">Users</span></button>
            <button class="nav-btn" data-tab="orders"><span class="nav-label">Orders</span></button>
            <button class="nav-btn" data-tab="pos"><span class="nav-label">POS</span></button>
            <button class="nav-btn" data-tab="health"><span class="nav-label">Health</span></button>
            <button class="nav-btn" data-tab="site"><span class="nav-label">Public Site</span></button>
        </nav>
        <div style="margin-top:12px;">
            <div class="muted" style="margin-bottom:8px;">
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

    <main>
        <div class="hero">
            <div class="hero-top">
                <div>
                    <p class="muted" style="margin:0 0 4px;">Dashboard</p>
                    <h1 style="margin:0; font-family:'Playfair Display', Georgia, serif;">Today at a glance</h1>
                </div>
                <div class="actions">
                    <div class="alert-wrap" id="adminAlertWrap">
                        <button class="alert-bell" id="adminAlertBell" title="Stock Alerts">
                            🔔
                            <span id="adminAlertBadge" class="alert-badge hidden">0</span>
                        </button>
                        <div id="adminAlertDropdown" class="alert-dropdown">
                            <div class="alert-head">📦 Stock Alerts</div>
                            <div id="adminAlertList" class="alert-list">
                                <div class="alert-empty">Loading alerts...</div>
                            </div>
                            <div class="alert-footer">
                                <a href="/admin/stock-alerts">View All</a>
                            </div>
                        </div>
                    </div>
                    <button class="btn-ghost" onclick="switchTab('pos')">Open POS</button>
                    <button class="btn-primary" onclick="switchTab('menu')">Add Product</button>
                </div>
            </div>
            <div class="stats">
                <div class="stat"><h3 id="statCategories">0</h3><span>Categories</span></div>
                <div class="stat"><h3 id="statItems">0</h3><span>Products</span></div>
                <div class="stat"><h3 id="statOrders">0</h3><span>Orders today</span></div>
                <div class="stat"><h3 id="statRevenue">₦0</h3><span>Revenue today</span></div>
            </div>
        </div>

        <div class="panels">
            <section class="panel active" data-section="overview">
                <div class="card">
                    <h2>Overview</h2>
                    <p class="muted">Quick links to start.</p>
                    <div class="row" style="gap:10px; flex-wrap:wrap;">
                        <button class="btn-primary" onclick="switchTab('menu')">Manage Products</button>
                        <button class="btn-ghost" onclick="switchTab('categories')">Manage Categories</button>
                        <button class="btn-ghost" onclick="switchTab('orders')">View Orders</button>
                        <button class="btn-ghost" onclick="switchTab('pos')">Open POS</button>
                    </div>
                </div>
            </section>

            <section class="panel" data-section="categories">
                <div class="card">
                    <h2>Categories</h2>
                    <p class="muted">Create and manage categories.</p>
                    <div class="grid-2">
                        <div class="list" id="categoryList"></div>
                        <form id="categoryForm">
                            <label>Name</label>
                            <input name="name" placeholder="e.g. CCTV" required />
                            <label>Description</label>
                            <input name="description" placeholder="Optional" />
                            <button class="btn-primary" type="submit">Add Category</button>
                        </form>
                    </div>
                </div>
            </section>

            <section class="panel" data-section="menu">
                <div class="card">
                    <h2>Products</h2>
                    <p class="muted">Add items and toggle availability.</p>
                    <div class="grid-2">
                        <div class="list" id="menuList"></div>
                        <form id="menuForm">
                            <label>Name</label>
                            <input name="name" placeholder="Product name" required />
                            <label>Barcode (Optional)</label>
                            <input name="barcode" placeholder="Scan or enter barcode (leave blank to auto-generate)" />
                            <label>Description</label>
                            <textarea name="description" rows="2" placeholder="Optional"></textarea>
                            <label>Price (NGN)</label>
                            <input name="price" type="number" step="0.01" min="0" required />
                            <label>Stock Quantity</label>
                            <input name="stock" type="number" min="0" value="0" />
                            <label>Category</label>
                            <select name="category_id" id="menuCategorySelect" required>
                                <option value="" disabled selected>Select category</option>
                            </select>
                            <label>Image</label>
                            <input name="image" type="file" accept="image/*" />
                            <label style="display:flex; align-items:center; gap:8px; margin-top:10px; cursor:pointer;">
                                <input name="display_on_website" type="checkbox" value="1" checked style="width:18px; height:18px; cursor:pointer;" />
                                <span>Display on Website</span>
                            </label>
                            <small class="muted" style="display:block; margin-top:4px;">Uncheck to hide from public website (will remain in POS)</small>
                            <button class="btn-primary" type="submit">Add Product</button>
                        </form>
                    </div>
                </div>
            </section>

            <section class="panel" data-section="users">
                <div class="card">
                    <h2>Users</h2>
                    <p class="muted">Approve accounts and assign roles.</p>
                    <div class="list" id="usersList"></div>
                </div>
            </section>

            <section class="panel" data-section="orders">
                <div class="card">
                    <div style="display:flex; justify-content:space-between; gap:10px; align-items:flex-start; flex-wrap:wrap;">
                        <div>
                            <h2 style="margin:0 0 4px;">Orders</h2>
                            <p class="muted" style="margin:0;">Today and recent sales. Seller is recorded per order.</p>
                        </div>
                        <div class="row" style="gap:8px; align-items:center; flex-wrap:wrap;">
                            <select id="ordersExportRange" style="padding:8px 10px; border-radius:10px; border:1px solid var(--brand-border);">
                                <option value="weekly">Last 7 days</option>
                                <option value="monthly" selected>Last 30 days</option>
                            </select>
                            <button class="btn-ghost" id="ordersExportBtn" style="white-space:nowrap;">Download CSV</button>
                            <button class="btn-ghost" id="deleteSelectedOrdersBtn" style="white-space:nowrap;" disabled>Delete Selected</button>
                            <button class="btn-ghost" id="purgeOrdersBtn" style="white-space:nowrap;">Delete test orders</button>
                        </div>
                    </div>

                    <div style="display:grid; gap:12px; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); margin-top:12px;">
                        <div style="border:1px solid var(--brand-border); border-radius:12px; padding:12px; background:#fff;">
                            <div class="muted" style="font-size:12px; margin-bottom:6px;">Revenue (last 7 days)</div>
                            <div id="ordersChart" >
                                <div id="ordersChartBars" style="display:flex; align-items:flex-end; gap:8px; height:120px;"></div>
                                <div id="ordersChartLabels" style="display:flex; gap:8px; justify-content:space-between; font-size:11px; color:var(--brand-muted); margin-top:6px;"></div>
                            </div>
                        </div>
                        <div style="border:1px solid var(--brand-border); border-radius:12px; padding:12px; background:#fff;">
                            <div class="muted" style="font-size:12px;">Snapshot</div>
                            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap:10px; margin-top:8px;">
                                <div class="stat" style="margin:0; box-shadow:none; border-color:var(--brand-border);">
                                    <h3 id="statOrders">0</h3><span>Orders today</span>
                                </div>
                                <div class="stat" style="margin:0; box-shadow:none; border-color:var(--brand-border);">
                                    <h3 id="statRevenue">₦0</h3><span>Revenue today</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="frame-wrap" style="margin-top:12px; border-color:var(--brand-border); box-shadow:none;">
                        <div style="overflow:auto; max-height:420px;">
                            <table id="ordersTable" style="margin:0;">
                                <thead>
                                    <tr>
                                        <th style="width:28px;">
                                            <input type="checkbox" id="ordersSelectAll" />
                                        </th>
                                        <th>Code</th>
                                        <th>Seller</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Channel</th>
                                        <th>When</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <div id="deleteOrdersModal" style="position:fixed; inset:0; background:rgba(0,0,0,0.45); display:none; align-items:center; justify-content:center; z-index:9999;">
                        <div style="background:#fff; padding:18px 20px; border-radius:14px; width:min(420px, 92vw); box-shadow:0 18px 40px rgba(0,0,0,0.25);">
                            <div style="font-weight:800; font-size:16px; margin-bottom:6px;">Delete selected orders?</div>
                            <div id="deleteOrdersModalText" class="muted" style="font-size:13px; margin-bottom:14px;">This action cannot be undone.</div>
                            <div style="display:flex; gap:10px; justify-content:flex-end;">
                                <button class="btn-ghost" id="deleteOrdersCancelBtn">Cancel</button>
                                <button class="btn-primary" id="deleteOrdersConfirmBtn">Yes, delete</button>
                            </div>
                        </div>
                    </div>
                    <div id="ordersUndoToast" style="position:fixed; right:16px; bottom:16px; z-index:9999; padding:12px 14px; border-radius:10px; font-weight:700; box-shadow:0 16px 38px rgba(0,0,0,0.18); background:#0A1428; color:#fff; display:none; align-items:center; gap:10px;">
                        <span id="ordersUndoText">Orders will be deleted.</span>
                        <button id="ordersUndoBtn" style="border:none; background:#fff; color:#111; padding:6px 10px; border-radius:8px; font-weight:700; cursor:pointer;">Undo</button>
                    </div>
                </div>
            </section>

            <section class="panel" data-section="pos">
                <div class="card">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:10px; flex-wrap:wrap;">
                        <div>
                            <h2 style="margin:0 0 4px;">POS</h2>
                            <p class="muted" style="margin:0;">Scan, add, and print quickly with consistent layout.</p>
                        </div>
                        <div style="border:1px solid var(--brand-border); border-radius:12px; padding:10px 12px; background:#fff; min-width:180px; text-align:right;">
                            <div class="muted" style="font-size:12px;">Cart total</div>
                            <div style="font-weight:800; color:var(--brand-dark); font-size:18px;" id="posCartTotal">₦0</div>
                        </div>
                    </div>

                    <div style="display:grid; gap:12px; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); margin-top:12px;">
                        <div style="border:1px solid var(--brand-border); border-radius:14px; padding:12px; background:#fff;">
                            <label style="display:block; font-size:13px; color:var(--brand-muted); margin-bottom:6px;">Scan / Enter barcode</label>
                            <input id="posBarcodeInput" placeholder="Focus here and scan" style="width:100%; padding:12px; border-radius:12px; border:1px solid var(--brand-border); font-size:16px;" />
                            <small class="muted" id="posScanStatus" style="display:block; margin-top:6px;">Ready to scan.</small>
                            <div id="posSuggestions" style="margin-top:8px;"></div>
                            <div id="posLookupResult" class="item" style="display:none; flex-direction:column; align-items:flex-start; margin-top:10px;"></div>
                            <div id="posSavedCustomers" style="margin-top:10px; display:flex; gap:6px; flex-wrap:wrap;"></div>
                        </div>
                        <div style="border:1px solid var(--brand-border); border-radius:14px; padding:12px; background:#fff;">
                            <div style="display:flex; justify-content:space-between; align-items:center; gap:8px;">
                                <h3 style="margin:0;">POS Cart</h3>
                                <span class="pill">Auto-print</span>
                            </div>
                            <div id="posCartList" class="list" style="margin-top:8px;"></div>
                            <div style="margin-top:10px; border-top:1px dashed var(--brand-border); padding-top:10px; display:grid; gap:6px;">
                                <div style="display:flex; justify-content:space-between;"><span class="muted">Subtotal</span><strong id="posSubtotal">₦0</strong></div>
                                <div style="display:flex; gap:8px; align-items:center; justify-content:space-between;">
                                    <span class="muted">Discount</span>
                                    <input id="posDiscount" type="number" min="0" step="1" value="0" style="width:120px; padding:8px; border-radius:10px; border:1px solid var(--brand-border);" />
                                </div>
                                <div style="display:flex; gap:8px; align-items:center; justify-content:space-between;">
                                    <span class="muted">Tax / Fee</span>
                                    <input id="posTax" type="number" min="0" step="1" value="0" style="width:120px; padding:8px; border-radius:10px; border:1px solid var(--brand-border);" />
                                </div>
                                <div style="display:flex; justify-content:space-between; align-items:center; font-weight:700;">
                                    <span>Total</span><span id="posGrandTotal">₦0</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div style="display:grid; gap:12px; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-top:12px;">
                        <div style="display:grid; gap:6px;">
                            <label style="font-size:13px; color:var(--brand-muted);">Customer name (optional)</label>
                            <input id="posCustomerName" placeholder="Walk-in" style="padding:10px; border-radius:12px; border:1px solid var(--brand-border);" />
                        </div>
                        <div style="display:grid; gap:6px;">
                            <label style="font-size:13px; color:var(--brand-muted);">Customer phone</label>
                            <input id="posCustomerPhone" placeholder="080..." style="padding:10px; border-radius:12px; border:1px solid var(--brand-border);" />
                        </div>
                        <div style="display:grid; gap:6px;">
                            <label style="font-size:13px; color:var(--brand-muted);">Payment method</label>
                            <select id="posPaymentMethod" style="padding:10px; border-radius:12px; border:1px solid var(--brand-border);">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="transfer">Transfer</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-top:10px; display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                        <button class="btn-primary" id="posCheckoutBtn">Complete Sale & Print Receipt</button>
                        <small class="muted">Saves order with seller info and prints a receipt.</small>
                        <button class="btn-ghost" id="posParkBtn">Park ticket</button>
                    </div>

                    <div style="margin-top:12px; border:1px solid var(--brand-border); border-radius:12px; padding:10px; background:#fff;">
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:8px;">
                            <strong>Parked tickets</strong>
                            <small class="muted">Hold & resume orders</small>
                        </div>
                        <div id="posParkedList" class="list" style="margin-top:8px;"></div>
                    </div>
                </div>
            </section>

            <section class="panel" data-section="health">
                <div class="card">
                    <h2>API Health</h2>
                    <p class="muted">Live check of the backend.</p>
                    <p id="healthDetail" class="muted">Checking...</p>
                </div>
            </section>

            <section class="panel" data-section="site">
                <div class="card">
                    <h2>Public Site Preview</h2>
                    <p class="muted">Keep visuals aligned with the marketing site.</p>
                    <div class="frame-wrap">
                        <iframe src="/solutions" title="Public site preview"></iframe>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <script>
    (function () {
        try {
        if (!window.fetch) {
            alert('Your browser is too old to run the admin. Please use a modern browser.');
            return;
        }

        const sidebar = document.getElementById('sidebar');
        const toggleSidebar = document.getElementById('toggleSidebar');
        const navBtns = Array.from(document.querySelectorAll('.nav-btn'));
        const panels = Array.from(document.querySelectorAll('.panel'));

        const categoryList = document.getElementById('categoryList');
        const categoryForm = document.getElementById('categoryForm');
        const menuList = document.getElementById('menuList');
        const menuForm = document.getElementById('menuForm');
        const menuCategorySelect = document.getElementById('menuCategorySelect');
        const usersList = document.getElementById('usersList');
        const ordersTableBody = document.querySelector('#ordersTable tbody');
        const ordersChartBars = document.getElementById('ordersChartBars');
        const ordersChartLabels = document.getElementById('ordersChartLabels');
        const purgeOrdersBtn = document.getElementById('purgeOrdersBtn');
        const ordersExportBtn = document.getElementById('ordersExportBtn');
        const ordersExportRange = document.getElementById('ordersExportRange');
        const ordersSelectAll = document.getElementById('ordersSelectAll');
        const deleteSelectedOrdersBtn = document.getElementById('deleteSelectedOrdersBtn');
        const deleteOrdersModal = document.getElementById('deleteOrdersModal');
        const deleteOrdersModalText = document.getElementById('deleteOrdersModalText');
        const deleteOrdersCancelBtn = document.getElementById('deleteOrdersCancelBtn');
        const deleteOrdersConfirmBtn = document.getElementById('deleteOrdersConfirmBtn');
        const ordersUndoToast = document.getElementById('ordersUndoToast');
        const ordersUndoText = document.getElementById('ordersUndoText');
        const ordersUndoBtn = document.getElementById('ordersUndoBtn');
        const statCategories = document.getElementById('statCategories');
        const statItems = document.getElementById('statItems');
        const statOrders = document.getElementById('statOrders');
        const statRevenue = document.getElementById('statRevenue');
        const healthDetail = document.getElementById('healthDetail');
        const roles = ['admin', 'pos', 'user'];
        const posBarcodeInput = document.getElementById('posBarcodeInput');
        const posLookupResult = document.getElementById('posLookupResult');
        const posScanStatus = document.getElementById('posScanStatus');
        const posSuggestions = document.getElementById('posSuggestions');
        const posCartList = document.getElementById('posCartList');
        const posCartTotal = document.getElementById('posCartTotal');
        const posCustomerName = document.getElementById('posCustomerName');
        const posCustomerPhone = document.getElementById('posCustomerPhone');
        const posPaymentMethod = document.getElementById('posPaymentMethod');
        const posCheckoutBtn = document.getElementById('posCheckoutBtn');
        const posDiscount = document.getElementById('posDiscount');
        const posTax = document.getElementById('posTax');
        const posSubtotal = document.getElementById('posSubtotal');
        const posGrandTotal = document.getElementById('posGrandTotal');
        const posParkBtn = document.getElementById('posParkBtn');
        const posParkedList = document.getElementById('posParkedList');
        const posSavedCustomers = document.getElementById('posSavedCustomers');
        const barcodeCache = {};
        let menuCacheReady = false;
        let lastSummary = null;
        let posCart = [];
        let lastLookup = null;
        let isInteracting = false;
        let interactionTimeout;
        let posLookupInFlight = false;
        let scanDebounce = null;
        let ordersCache = [];
        const selectedOrderIds = new Set();
        let deletePending = false;
        let deleteOrdersTimer = null;
        let pendingDeleteIds = [];

        const createPoller = (task, intervalMs, options = {}) => {
            const { immediate = true, runWhileHidden = false, onError = null } = options;
            let timer = null;
            let running = false;

            const shouldRun = () => {
                if (runWhileHidden) return true;
                if (document.visibilityState === 'hidden') return false;
                return true;
            };

            const tick = async () => {
                if (running || !shouldRun()) return;
                running = true;
                try {
                    await task();
                } catch (err) {
                    if (onError) {
                        onError(err);
                    } else {
                        console.warn('Poller task failed', err);
                    }
                } finally {
                    running = false;
                }
            };

            const start = () => {
                if (timer) return;
                if (immediate) tick();
                timer = setInterval(tick, intervalMs);
            };

            const stop = () => {
                if (timer) {
                    clearInterval(timer);
                    timer = null;
                }
            };

            document.addEventListener('visibilitychange', () => {
                if (timer && shouldRun()) tick();
            });

            return { start, stop, isRunning: () => !!timer };
        };

        const markInteracting = () => {
            isInteracting = true;
            clearTimeout(interactionTimeout);
            interactionTimeout = setTimeout(() => { isInteracting = false; }, 2000);
        };

        const loadSavedCustomers = () => {
            try {
                const data = JSON.parse(localStorage.getItem('pos_saved_customers') || '[]');
                return Array.isArray(data) ? data.slice(0, 6) : [];
            } catch { return []; }
        };

        const persistSavedCustomers = (list) => {
            localStorage.setItem('pos_saved_customers', JSON.stringify(list.slice(0, 6)));
        };

        const loadParkedTickets = () => {
            try {
                const data = JSON.parse(localStorage.getItem('pos_parked_tickets') || '[]');
                return Array.isArray(data) ? data : [];
            } catch { return []; }
        };

        const persistParkedTickets = (list) => {
            localStorage.setItem('pos_parked_tickets', JSON.stringify(list));
        };

        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : null;
        const adminAlertWrap = document.getElementById('adminAlertWrap');
        const adminAlertBell = document.getElementById('adminAlertBell');
        const adminAlertBadge = document.getElementById('adminAlertBadge');
        const adminAlertList = document.getElementById('adminAlertList');

        const escapeAttr = (value = '') => String(value)
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        const apiFetch = (url, options = {}) => {
            const headers = {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                ...(options.headers || {}),
            };
            return fetch(url, {
                credentials: 'same-origin',
                cache: options.cache ?? 'no-store',
                ...options,
                headers,
            });
        };

        if (adminAlertBell && adminAlertWrap) {
            adminAlertBell.addEventListener('click', (e) => {
                e.stopPropagation();
                adminAlertWrap.classList.toggle('open');
            });
            document.addEventListener('click', (e) => {
                if (!adminAlertWrap.contains(e.target)) {
                    adminAlertWrap.classList.remove('open');
                }
            });
        }

        if (adminAlertList) {
            adminAlertList.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-ack-alert]');
                if (!btn) return;
                const alertId = btn.getAttribute('data-ack-alert');
                if (!alertId) return;
                acknowledgeAdminAlert(alertId);
            });
        }

        const loadAdminAlerts = () => {
            if (!adminAlertList) return;
            apiFetch('/api/stock-alerts')
                .then(r => r.json())
                .then(alerts => {
                    if (!Array.isArray(alerts) || alerts.length === 0) {
                        adminAlertBadge?.classList.add('hidden');
                        adminAlertList.innerHTML = '<div class="alert-empty">✅ No active alerts</div>';
                        return;
                    }
                    adminAlertBadge?.classList.remove('hidden');
                    if (adminAlertBadge) adminAlertBadge.textContent = alerts.length;
                    adminAlertList.innerHTML = alerts.map(alert => `
                        <div class="alert-item">
                            <div>
                                <div class="alert-title">${alert.product_name}</div>
                                <div class="alert-meta">
                                    ${alert.alert_type === 'out_of_stock' ? '🔴 Out of Stock' : '🟡 Low Stock (' + alert.current_stock + ' left)'}
                                </div>
                                <div class="alert-meta">${alert.barcode || ''}</div>
                            </div>
                            <div class="alert-actions">
                                <button class="alert-btn" data-ack-alert="${alert.id}">Mark as read</button>
                            </div>
                        </div>
                    `).join('');
                })
                .catch(err => console.error('Error loading alerts:', err));
        };

        const acknowledgeAdminAlert = (id) => {
            return apiFetch(`/api/stock-alerts/${id}/acknowledge`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
            })
            .then(r => r.json())
            .then(result => {
                if (result && result.success) loadAdminAlerts();
            })
            .catch(err => console.error('Error acknowledging alert:', err));
        };

        const setBusy = (btn, busy) => {
            if (!btn) return;
            btn.disabled = busy;
            if (busy) {
                btn.dataset.originalText = btn.dataset.originalText || btn.textContent;
                btn.textContent = 'Working...';
            } else if (btn.dataset.originalText) {
                btn.textContent = btn.dataset.originalText;
            }
        };

        const toast = (message, tone = 'ok') => {
            const el = document.createElement('div');
            el.textContent = message;
            el.style.position = 'fixed';
            el.style.right = '16px';
            el.style.bottom = '16px';
            el.style.zIndex = '9999';
            el.style.padding = '12px 14px';
            el.style.borderRadius = '10px';
            el.style.fontWeight = '700';
            el.style.boxShadow = '0 16px 38px rgba(0,0,0,0.18)';
            el.style.background = tone === 'error' ? '#fee2e2' : '#0A1428';
            el.style.color = tone === 'error' ? '#991b1b' : '#fff';
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 2600);
        };

        const safeRequest = async (url, options = {}) => {
            const res = await apiFetch(url, options);
            if (!res.ok) {
                let message = `Request failed (${res.status})`;
                try {
                    const data = await res.clone().json();
                    if (data && data.message) message = data.message;
                } catch (err) {
                    const text = await res.text().catch(() => '');
                    if (text) message = text;
                }
                throw new Error(message);
            }
            return res;
        };

        const runAction = async (btn, fn) => {
            setBusy(btn, true);
            try {
                await fn();
            } catch (e) {
                toast(e.message || 'Could not complete that action.', 'error');
                console.error(e);
            } finally {
                setBusy(btn, false);
            }
        };

        if (toggleSidebar) {
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
        }

        function switchTab(tab) {
            navBtns.forEach(b => b.classList.toggle('active', b.dataset.tab === tab));
            panels.forEach(p => p.classList.toggle('active', p.dataset.section === tab));
            if (window.innerWidth <= 960) {
                sidebar.classList.remove('open');
                toggleSidebar.setAttribute('aria-expanded', 'false');
            }
        }
        window.switchTab = switchTab;
        navBtns.forEach(btn => btn.addEventListener('click', () => switchTab(btn.dataset.tab)));

        async function checkHealth() {
            try {
                const res = await apiFetch('/api/health');
                if (!res.ok) throw new Error('bad status');
                healthDetail.textContent = 'API responding normally.';
            } catch (e) {
                healthDetail.textContent = 'API not reachable. Check server.';
            }
        }

        function renderCategories(categories) {
            categoryList.innerHTML = categories.map(cat => `
                <div class="item">
                    <div>
                        <h4>${cat.name}</h4>
                        <small class="muted">${cat.description || ''}</small>
                    </div>
                    <span class="pill" style="border-color:${cat.is_active ? '#bbf7d0' : '#fca5a5'};color:${cat.is_active ? '#166534' : '#b91c1c'}">
                        ${cat.is_active ? 'Active' : 'Inactive'}
                    </span>
                    <div class="row" style="gap:6px;">
                        <button class="btn-ghost" onclick="deleteCategory(${cat.id}, this)">Delete</button>
                    </div>
                </div>
            `).join('');

            menuCategorySelect.innerHTML = `<option value="" disabled selected>Select category</option>` + categories.map(cat => `<option value="${cat.id}">${cat.name}</option>`).join('');
            statCategories.textContent = categories.length;
        }

        function renderMenu(items) {
            const cards = items.map(item => {
                const safeBarcode = escapeAttr(item.barcode || '');
                const safeName = escapeAttr(item.name || '');
                if (item.barcode) {
                    barcodeCache[item.barcode] = item;
                }
                return `
                    <div class="menu-card">
                        <div class="menu-card-head">
                            <div style="display:flex; gap:10px; align-items:center;">
                                ${item.image_url ? `<img class="thumb" src="${item.image_url}" alt="${item.name}">` : ''}
                                <div>
                                    <p class="menu-card-title">${item.name}</p>
                                    <div class="menu-tags">
                                        <span class="menu-pill">₦${Number(item.price).toLocaleString()}</span>
                                        <span class="menu-pill">${item.category && item.category.name ? item.category.name : 'Uncategorized'}</span>
                                        <span class="menu-pill ${item.is_sold_out ? 'sold' : 'active'}">${item.is_sold_out ? 'Sold Out' : 'Available'}</span>
                                        <span class="menu-pill">${item.stock === 0 ? 'Sold Out' : `Stock: ${item.stock}`}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="menu-tags">
                                <span class="menu-pill">Barcode: ${item.barcode || 'Not set'}</span>
                                <button class="btn-ghost" ${item.barcode ? '' : 'disabled'} data-action="copy" data-barcode="${safeBarcode}">Copy</button>
                                <button class="btn-ghost" ${item.barcode ? '' : 'disabled'} data-action="download" data-barcode="${safeBarcode}" data-name="${safeName}">Download</button>
                                <button class="btn-ghost" onclick="regenBarcode(${item.id}, this)">Regenerate</button>
                            </div>
                        </div>
                        <p class="menu-meta">${item.description || 'No description yet.'}</p>
                        <div class="menu-actions">
                            <button class="btn-ghost" onclick="toggleSoldOut(${item.id}, this)">${item.is_sold_out ? 'Mark Available' : 'Mark Sold Out'}</button>
                            <button class="btn-ghost" onclick="editMenuItem(${item.id}, ${JSON.stringify(item).replace(/"/g, '&quot;')}, this)">Edit</button>
                            <button class="btn-ghost" onclick="deleteMenuItem(${item.id}, this)">Delete</button>
                        </div>
                    </div>
                `;
            });
            menuList.innerHTML = cards.join('');
            statItems.textContent = items.length;
        }

        if (menuList) {
            menuList.addEventListener('click', (e) => {
                const btn = e.target.closest('button[data-action]');
                if (!btn) return;
                const { action, barcode = '', name = '' } = btn.dataset;
                if (action === 'copy') {
                    copyBarcode(barcode);
                }
                if (action === 'download') {
                    printBarcode(barcode, name);
                }
            });
        }

        function renderOrders(orders) {
            ordersCache = orders;
            let revenue = 0;
            ordersTableBody.innerHTML = orders.map(o => {
                revenue += Number(o.total || 0);
                const checked = selectedOrderIds.has(o.id) ? 'checked' : '';
                return `
                    <tr>
                        <td><input type="checkbox" class="order-select" data-order-id="${o.id}" ${checked}></td>
                        <td>${o.code || o.id}</td>
                        <td>${o.creator && o.creator.name ? o.creator.name : '—'}</td>
                        <td>${o.status}</td>
                        <td>₦${Number(o.total).toLocaleString()}</td>
                        <td>${o.channel || 'pos'}</td>
                        <td>${new Date(o.created_at).toLocaleString()}</td>
                    </tr>
                `;
            }).join('');
            updateSelectAllState();
            updateSelectedOrdersUI();
        }

        const updateSelectAllState = () => {
            if (!ordersSelectAll) return;
            const visibleIds = ordersCache.map(o => o.id);
            const selectedVisible = visibleIds.filter(id => selectedOrderIds.has(id));
            ordersSelectAll.checked = visibleIds.length > 0 && selectedVisible.length === visibleIds.length;
            ordersSelectAll.indeterminate = selectedVisible.length > 0 && selectedVisible.length < visibleIds.length;
        };

        const updateSelectedOrdersUI = () => {
            if (!deleteSelectedOrdersBtn) return;
            const count = selectedOrderIds.size;
            deleteSelectedOrdersBtn.disabled = count === 0 || deletePending;
            deleteSelectedOrdersBtn.textContent = count ? `Delete Selected (${count})` : 'Delete Selected';
        };

        if (ordersSelectAll) {
            ordersSelectAll.addEventListener('change', (e) => {
                if (e.target.checked) {
                    ordersCache.forEach(o => selectedOrderIds.add(o.id));
                } else {
                    ordersCache.forEach(o => selectedOrderIds.delete(o.id));
                }
                renderOrders(ordersCache);
            });
        }

        if (ordersTableBody) {
            ordersTableBody.addEventListener('change', (e) => {
                const checkbox = e.target.closest('.order-select');
                if (!checkbox) return;
                const id = Number(checkbox.getAttribute('data-order-id'));
                if (!Number.isNaN(id)) {
                    if (checkbox.checked) {
                        selectedOrderIds.add(id);
                    } else {
                        selectedOrderIds.delete(id);
                    }
                    updateSelectAllState();
                    updateSelectedOrdersUI();
                }
            });
        }

        function upsertOrderCache(order) {
            if (!order) return;
            ordersCache = [order, ...ordersCache.filter(o => o.id !== order.id)];
            renderOrders(ordersCache);
        }

        window.shareOrderWhatsapp = (id) => {
            const order = ordersCache.find(o => o.id === id);
            if (!order) {
                alert('Order not found yet.');
                return;
            }
            const items = (order.items || []).map(item => `• ${item.quantity}x ${item.name}`).join('\n');
            const lines = [
                `Order ${order.code || order.id} (${order.channel || 'pos'})`,
                order.customer_name || order.customer_phone ? `Customer: ${order.customer_name || 'Walk-in'}${order.customer_phone ? ' · ' + order.customer_phone : ''}` : '',
                `Total: ₦${Number(order.total || 0).toLocaleString()} (${order.status})`,
                order.customer_name ? `Customer: ${order.customer_name || 'Walk-in'}${order.customer_phone ? ' · ' + order.customer_phone : ''}` : '',
                items ? 'Items:\n' + items : '',
            ].filter(Boolean).join('\n');
            const url = `https://wa.me/?text=${encodeURIComponent(lines)}`;
            const win = window.open(url, '_blank');
            if (!win) {
                navigator.clipboard?.writeText(lines).then(() => alert('Copied to clipboard. Paste in WhatsApp.'));
            }
        };

        function renderSummary(summary) {
            if (summary) {
                lastSummary = summary;
                statOrders.textContent = summary.today_orders ?? 0;
                statRevenue.textContent = '₦' + Number(summary.today_revenue || 0).toLocaleString();
                renderChart(summary.series || []);
            }
        }

        function renderChart(series) {
            if (!ordersChartBars || !ordersChartLabels) return;
            if (!Array.isArray(series) || !series.length) {
                ordersChartBars.innerHTML = '<div class="muted">No data</div>';
                ordersChartLabels.innerHTML = '';
                return;
            }
            const maxRevenue = Math.max(...series.map(s => Number(s.revenue || 0)), 1);
            ordersChartBars.innerHTML = series.map(s => {
                const height = Math.max(4, (Number(s.revenue || 0) / maxRevenue) * 100);
                return `<div title="₦${Number(s.revenue || 0).toLocaleString()}" style="flex:1; min-width:10px; background:var(--brand-dark); height:${height}%; border-radius:6px 6px 2px 2px;"></div>`;
            }).join('');
            ordersChartLabels.innerHTML = series.map(s => {
                const label = s.day ? s.day.slice(5) : '';
                return `<span style="flex:1; text-align:center;">${label}</span>`;
            }).join('');
        }

        async function loadCategories() {
            try {
                const res = await safeRequest('/api/categories');
                const data = await res.json();
                renderCategories(data);
            } catch (e) {
                console.error(e);
                categoryList.innerHTML = '<div class="muted">Could not load categories.</div>';
            }
        }

        async function loadMenu() {
            try {
                const res = await safeRequest('/api/menu-items');
                const data = await res.json();
                Object.keys(barcodeCache).forEach(key => delete barcodeCache[key]);
                data.forEach(item => {
                    if (item.barcode) {
                        barcodeCache[item.barcode] = item;
                    }
                });
                menuCacheReady = true;
                renderMenu(data);
            } catch (e) {
                console.error(e);
                menuList.innerHTML = '<div class="muted">Could not load products.</div>';
            }
        }

        async function loadOrders() {
            try {
                const res = await safeRequest('/api/orders');
                const payload = await res.json();
                const data = payload.data || payload;
                renderOrders(data);
                if (!lastSummary) {
                    const fallbackRev = data.reduce((sum, o) => sum + Number(o.total || 0), 0);
                    statOrders.textContent = data.length;
                    statRevenue.textContent = '₦' + fallbackRev.toLocaleString();
                }
            } catch (e) {
                console.error(e);
                ordersTableBody.innerHTML = '<tr><td colspan="7">Could not load orders.</td></tr>';
            }
        }

        async function loadOrderSummary() {
            try {
                const res = await safeRequest('/api/orders/summary');
                const data = await res.json();
                renderSummary(data);
            } catch (e) {
                console.error('Could not load summary', e);
            }
        }

        async function loadUsers() {
            if (!usersList) return;
            try {
                const res = await safeRequest('/api/users');
                const data = await res.json();
                renderUsers(data);
            } catch (e) {
                console.error(e);
                usersList.innerHTML = '<div class="muted">Could not load users.</div>';
            }
        }

        function renderUsers(users) {
            if (!usersList) return;
            usersList.innerHTML = users.map(u => `
                <div class="item" style="align-items:flex-start;">
                    <div>
                        <h4>${u.name}</h4>
                        <div class="muted">${u.email}</div>
                        <div class="row" style="gap:6px;margin-top:6px;">
                            <span class="pill" style="border-color:${u.is_active ? '#bbf7d0' : '#fca5a5'};color:${u.is_active ? '#166534' : '#b91c1c'}">
                                ${u.is_active ? 'Active' : 'Pending'}
                            </span>
                            <span class="pill">Role: ${u.role || 'user'}</span>
                        </div>
                    </div>
                    <div class="row" style="gap:6px;">
                        <select onchange="updateUserRole(${u.id}, this.value)" value="${u.role || 'user'}">
                            ${roles.map(r => `<option value="${r}" ${r === (u.role || 'user') ? 'selected' : ''}>${r}</option>`).join('')}
                        </select>
                        <button class="btn-ghost" onclick="toggleUserActive(${u.id}, ${u.is_active ? 'false' : 'true'})">
                            ${u.is_active ? 'Deactivate' : 'Approve'}
                        </button>
                        <button class="btn-ghost" onclick="deleteUser(${u.id})">Delete</button>
                    </div>
                </div>
            `).join('');
        }

        function setPosStatus(message, tone = 'muted') {
            if (!posScanStatus) return;
            posScanStatus.textContent = message;
            posScanStatus.style.color = tone === 'error' ? '#b91c1c' : 'rgba(0,0,0,0.6)';
        }

        function renderPosCart() {
            if (!posCartList || !posCartTotal) return;
            if (!posCart.length) {
                posCartList.innerHTML = '<div class="item">Cart is empty.</div>';
                posCartTotal.textContent = '₦0';
                if (posSubtotal) posSubtotal.textContent = '₦0';
                if (posGrandTotal) posGrandTotal.textContent = '₦0';
                return;
            }

            const total = computePosTotal();
            posCartList.innerHTML = posCart.map((item, index) => {
                const line = item.price * item.qty;
                return `
                    <div class="item" style="align-items:center;">
                        <div>
                            <h4>${item.name}</h4>
                            <div class="row" style="gap:6px;">
                                <span class="pill">Barcode: ${item.barcode || 'n/a'}</span>
                                <span class="pill">₦${Number(item.price).toLocaleString()} × ${item.qty}</span>
                                <span class="pill">Line: ₦${Number(line).toLocaleString()}</span>
                            </div>
                        </div>
                        <div class="row" style="gap:6px;">
                            <button class="btn-ghost" onclick="updatePosQty(${index}, 'dec')">-</button>
                            <button class="btn-ghost" onclick="updatePosQty(${index}, 'inc')">+</button>
                            <button class="btn-ghost" onclick="updatePosQty(${index}, 'remove')">Remove</button>
                        </div>
                    </div>
                `;
            }).join('');

            posCartTotal.textContent = '₦' + total.toLocaleString();
            renderTotals();
        }

        function addToPosCart(item) {
            const existing = posCart.find((i) => i.id === item.id);
            if (existing) {
                existing.qty += 1;
            } else {
                posCart.push({
                    id: item.id,
                    name: item.name,
                    price: Number(item.price) || 0,
                    barcode: item.barcode,
                    qty: 1,
                });
            }
            renderPosCart();
        }

        function computePosTotal() {
            return posCart.reduce((sum, item) => sum + (item.price * item.qty), 0);
        }

        function computeGrandTotal() {
            const subtotal = computePosTotal();
            const discount = Number(posDiscount ? posDiscount.value : 0) || 0;
            const tax = Number(posTax ? posTax.value : 0) || 0;
            return Math.max(0, subtotal - discount + tax);
        }

        function renderTotals() {
            const subtotal = computePosTotal();
            const grand = computeGrandTotal();
            if (posSubtotal) posSubtotal.textContent = '₦' + subtotal.toLocaleString();
            if (posGrandTotal) posGrandTotal.textContent = '₦' + grand.toLocaleString();
            if (posCartTotal) posCartTotal.textContent = '₦' + grand.toLocaleString();
        }

        window.updatePosQty = (index, action) => {
            const item = posCart[index];
            if (!item) return;
            if (action === 'inc') item.qty += 1;
            if (action === 'dec') item.qty = Math.max(1, item.qty - 1);
            if (action === 'remove') posCart.splice(index, 1);
            renderPosCart();
        };

        function showLookupResult(item) {
            if (!posLookupResult) return;
            lastLookup = item;
            posLookupResult.style.display = 'flex';
            posLookupResult.innerHTML = `
                <div style="display:flex; flex-direction:column; gap:6px;">
                    <h4 style="margin:0;">${item.name}</h4>
                    <div class="row" style="gap:6px; flex-wrap:wrap;">
                        <span class="pill">Price: ₦${Number(item.price).toLocaleString()}</span>
                        <span class="pill">Barcode: ${item.barcode}</span>
                        <span class="pill">${item.category && item.category.name ? item.category.name : 'Uncategorized'}</span>
                    </div>
                    <button class="btn-primary" data-add-pos-item>Add to cart</button>
                </div>
            `;
        }

        function showLookupError(message) {
            if (!posLookupResult) return;
            lastLookup = null;
            posLookupResult.style.display = 'flex';
            posLookupResult.innerHTML = `<div class="muted">${message}</div>`;
        }

        const renderSuggestions = (items) => {
            if (!posSuggestions) return;
            if (!items.length) {
                posSuggestions.innerHTML = '';
                return;
            }
            posSuggestions.innerHTML = items.map(item => `
                <button class="btn-ghost" data-suggest-id="${item.id}" data-suggest-item='${JSON.stringify(item)}' style="display:block; width:100%; text-align:left; padding:8px 10px; margin-top:4px;">
                    ${item.name} <span class="muted">(${item.barcode || 'no barcode'})</span>
                </button>
            `).join('');
        };

        const findNameMatches = async (term) => {
            if (!term || term.length < 2) return [];
            try {
                const res = await apiFetch(`/api/menu-items/search?q=${encodeURIComponent(term)}`);
                if (!res.ok) return [];
                const items = await res.json();
                return Array.isArray(items) ? items.slice(0, 5) : [];
            } catch (e) {
                console.warn('Name search failed', e);
                return [];
            }
        };

        async function lookupBarcode(barcode, { addToCartOnSuccess = false } = {}) {
            if (!barcode) return;

            if (barcodeCache[barcode]) {
                const item = barcodeCache[barcode];
                showLookupResult(item);
                if (addToCartOnSuccess) {
                    addToPosCart(item);
                    setPosStatus(`Added ${item.name}. Ready for next scan.`);
                    if (posBarcodeInput) {
                        posBarcodeInput.value = '';
                        posBarcodeInput.focus();
                    }
                } else {
                    setPosStatus('Found. Price pulled live; add to cart.');
                }
                return;
            }

            if (posLookupInFlight) return;
            posLookupInFlight = true;
            setPosStatus('Looking up barcode...');
            try {
                const res = await apiFetch(`/api/menu-items/lookup?barcode=${encodeURIComponent(barcode)}`);
                if (!res.ok) {
                    const msg = res.status === 404
                        ? 'No item found for this barcode.'
                        : 'Could not look up this barcode.';
                    showLookupError(msg);
                    setPosStatus(msg, 'error');
                    return;
                }
                const item = await res.json();
                if (item.barcode) barcodeCache[item.barcode] = item;
                showLookupResult(item);
                if (addToCartOnSuccess) {
                    addToPosCart(item);
                    setPosStatus(`Added ${item.name}. Ready for next scan.`);
                    if (posBarcodeInput) {
                        posBarcodeInput.value = '';
                        posBarcodeInput.focus();
                    }
                } else {
                    setPosStatus('Found. Price pulled live; add to cart.');
                }
            } catch (e) {
                showLookupError('Lookup failed. Check connection.');
                setPosStatus('Lookup failed.', 'error');
            } finally {
                posLookupInFlight = false;
                if (posBarcodeInput) posBarcodeInput.select();
            }
        }

        categoryForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = new FormData(categoryForm);
            const submitBtn = categoryForm.querySelector('button[type="submit"]');
            await runAction(submitBtn, async () => {
                const res = await safeRequest('/api/categories', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        name: form.get('name'),
                        description: form.get('description') || null,
                        is_active: true,
                    }),
                });
                if (res.ok) toast('Category added');
                categoryForm.reset();
                await loadCategories();
            });
        });

        menuForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = new FormData(menuForm);
            if (form.get('image') && form.get('image').size === 0) {
                form.delete('image');
            }
            if ((form.get('barcode') || '').trim() === '') {
                form.delete('barcode');
            }
            if (!form.get('category_id')) {
                toast('Select a category before saving.', 'error');
                return;
            }
            const submitBtn = menuForm.querySelector('button[type="submit"]');
            await runAction(submitBtn, async () => {
                const res = await safeRequest('/api/menu-items', { method: 'POST', body: form });
                if (res.ok) toast('Product added');
                menuForm.reset();
                await Promise.all([loadMenu(), loadCategories()]);
            });
        });

        if (posLookupResult) posLookupResult.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-add-pos-item]');
            if (!btn || !lastLookup) return;
            addToPosCart(lastLookup);
        });

        if (posSuggestions) {
            posSuggestions.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-suggest-id]');
                if (!btn) return;
                const itemData = btn.getAttribute('data-suggest-item');
                let item;
                try {
                    item = JSON.parse(itemData);
                } catch {
                    return;
                }
                renderSuggestions([]);
                showLookupResult(item);
                addToPosCart(item);
                setPosStatus(`Added ${item.name}. Ready for next scan.`);
                if (posBarcodeInput) {
                    posBarcodeInput.value = '';
                    posBarcodeInput.focus();
                }
            });
        }

        if (posBarcodeInput) posBarcodeInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const code = e.target.value.trim();
                if (!code) return;
                const isName = /^[a-zA-Z\s]+$/.test(code);
                if (isName) {
                    if (lastLookup) {
                        addToPosCart(lastLookup);
                        setPosStatus(`Added ${lastLookup.name}. Ready for next scan.`);
                        if (posBarcodeInput) {
                            posBarcodeInput.value = '';
                            posBarcodeInput.focus();
                        }
                    } else {
                        setPosStatus('No match yet. Keep typing the product name.', 'error');
                    }
                    return;
                }
                lookupBarcode(code, { addToCartOnSuccess: true });
            }
        });

        if (posBarcodeInput) posBarcodeInput.addEventListener('input', (e) => {
            const code = e.target.value.trim();
            clearTimeout(scanDebounce);
            if (!code) {
                setPosStatus('Ready to scan.');
                renderSuggestions([]);
                return;
            }
            const isName = /^[a-zA-Z\s]+$/.test(code);
            if (isName) {
                if (code.length < 2) {
                    setPosStatus('Keep typing the product name.');
                    renderSuggestions([]);
                    return;
                }
                scanDebounce = setTimeout(async () => {
                    const matches = await findNameMatches(code);
                    if (!matches.length) {
                        showLookupError('No item matches that name.');
                        setPosStatus('No match found.', 'error');
                        renderSuggestions([]);
                        return;
                    }
                    showLookupResult(matches[0]);
                    setPosStatus('Found. Press Enter or click Add to cart.');
                    renderSuggestions(matches);
                }, 200);
                return;
            }
            if (code.length < 3) {
                setPosStatus('Keep typing or scan the barcode.');
                renderSuggestions([]);
                return;
            }
            scanDebounce = setTimeout(() => lookupBarcode(code, { addToCartOnSuccess: false }), 200);
        });

        if (posCheckoutBtn) posCheckoutBtn.addEventListener('click', async () => {
            if (!posCart.length) {
                alert('Cart is empty. Scan an item first.');
                return;
            }
            const payload = {
                channel: 'pos',
                customer_name: posCustomerName ? posCustomerName.value : null,
                customer_phone: posCustomerPhone ? posCustomerPhone.value : null,
                items: posCart.map(item => ({
                    menu_item_id: item.id,
                    quantity: item.qty,
                    price: item.price,
                })),
                payment: {
                    amount: computeGrandTotal(),
                    method: posPaymentMethod ? posPaymentMethod.value : 'cash',
                    reference: `POS-${Date.now()}`,
                },
                discount: Number(posDiscount ? posDiscount.value : 0) || 0,
                tax: Number(posTax ? posTax.value : 0) || 0,
            };

            await runAction(posCheckoutBtn, async () => {
                const res = await safeRequest('/api/orders', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                });
                const order = await res.json();
                alert(`Sale recorded. Order code: ${order.code || 'pending'}.`);
                openPosReceipt(order);
                posCart = [];
                renderPosCart();
                saveRecentCustomer(payload.customer_name, payload.customer_phone);
                if (posBarcodeInput) {
                    posBarcodeInput.value = '';
                    posBarcodeInput.focus();
                }
                await Promise.all([loadOrders(), loadOrderSummary()]);
            });
        });

        if (posDiscount) posDiscount.addEventListener('input', renderTotals);
        if (posTax) posTax.addEventListener('input', renderTotals);

        const saveRecentCustomer = (name, phone) => {
            if (!name && !phone) return;
            const list = loadSavedCustomers();
            const existingIndex = list.findIndex(c => c.name === name && c.phone === phone);
            if (existingIndex >= 0) list.splice(existingIndex, 1);
            list.unshift({ name: name || 'Walk-in', phone: phone || '' });
            persistSavedCustomers(list);
            renderSavedCustomers();
        };

        const renderSavedCustomers = () => {
            if (!posSavedCustomers) return;
            const list = loadSavedCustomers();
            if (!list.length) {
                posSavedCustomers.innerHTML = '';
                return;
            }
            posSavedCustomers.innerHTML = list.map(c => `
                <button class="btn-ghost" data-fill-name="${c.name || ''}" data-fill-phone="${c.phone || ''}" style="font-size:12px; padding:6px 10px; border-radius:999px;">
                    ${c.name || 'Walk-in'}${c.phone ? ' · ' + c.phone : ''}
                </button>
            `).join('');
        };

        if (posSavedCustomers) posSavedCustomers.addEventListener('click', (e) => {
            const btn = e.target.closest('button[data-fill-name]');
            if (!btn) return;
            if (posCustomerName) posCustomerName.value = btn.getAttribute('data-fill-name') || '';
            if (posCustomerPhone) posCustomerPhone.value = btn.getAttribute('data-fill-phone') || '';
        });

        const renderParkedTickets = () => {
            if (!posParkedList) return;
            const list = loadParkedTickets();
            if (!list.length) {
                posParkedList.innerHTML = '<div class="item">No parked tickets.</div>';
                return;
            }
            posParkedList.innerHTML = list.map((t, idx) => `
                <div class="item" style="align-items:center;">
                    <div>
                        <strong>${t.name || 'Walk-in'}</strong>
                        <div class="muted" style="font-size:12px;">${new Date(t.created_at).toLocaleTimeString()}</div>
                        <div class="muted" style="font-size:12px;">Items: ${t.cart.length}</div>
                    </div>
                    <div class="row" style="gap:6px;">
                        <button class="btn-ghost" data-resume="${idx}">Resume</button>
                        <button class="btn-ghost" data-drop="${idx}">Delete</button>
                    </div>
                </div>
            `).join('');
        };

        const parkCurrentTicket = () => {
            if (!posCart.length) {
                alert('Nothing to park.');
                return;
            }
            const list = loadParkedTickets();
            list.unshift({
                created_at: Date.now(),
                cart: posCart.map(i => ({ ...i })),
                name: posCustomerName ? posCustomerName.value : '',
                phone: posCustomerPhone ? posCustomerPhone.value : '',
                method: posPaymentMethod ? posPaymentMethod.value : 'cash',
                discount: Number(posDiscount ? posDiscount.value : 0) || 0,
                tax: Number(posTax ? posTax.value : 0) || 0,
            });
            persistParkedTickets(list.slice(0, 10));
            posCart = [];
            renderPosCart();
            if (posBarcodeInput) posBarcodeInput.value = '';
            renderParkedTickets();
        };

        if (posParkBtn) posParkBtn.addEventListener('click', parkCurrentTicket);

        if (posParkedList) posParkedList.addEventListener('click', (e) => {
            const resume = e.target.closest('[data-resume]');
            const drop = e.target.closest('[data-drop]');
            const list = loadParkedTickets();
            if (resume) {
                const idx = Number(resume.getAttribute('data-resume'));
                const ticket = list[idx];
                if (ticket) {
                    posCart = ticket.cart || [];
                    if (posCustomerName) posCustomerName.value = ticket.name || '';
                    if (posCustomerPhone) posCustomerPhone.value = ticket.phone || '';
                    if (posPaymentMethod) posPaymentMethod.value = ticket.method || 'cash';
                    if (posDiscount) posDiscount.value = ticket.discount || 0;
                    if (posTax) posTax.value = ticket.tax || 0;
                    renderPosCart();
                    renderTotals();
                    if (posBarcodeInput) posBarcodeInput.focus();
                }
            }
            if (drop) {
                const idx = Number(drop.getAttribute('data-drop'));
                if (!Number.isNaN(idx)) {
                    list.splice(idx, 1);
                    persistParkedTickets(list);
                    renderParkedTickets();
                }
            }
        });

        if (purgeOrdersBtn) purgeOrdersBtn.addEventListener('click', async () => {
            if (!confirm('Delete all orders? This cannot be undone.')) return;
            await runAction(purgeOrdersBtn, async () => {
                await safeRequest('/api/orders/purge', { method: 'POST' });
                await Promise.all([loadOrders(), loadOrderSummary()]);
            });
        });

        const openDeleteOrdersModal = () => {
            if (!deleteOrdersModal || !deleteOrdersModalText) return;
            deleteOrdersModalText.textContent = `Delete ${selectedOrderIds.size} selected order(s)? This action cannot be undone.`;
            deleteOrdersModal.style.display = 'flex';
        };

        const closeDeleteOrdersModal = () => {
            if (!deleteOrdersModal) return;
            deleteOrdersModal.style.display = 'none';
        };

        const showUndoToast = (count) => {
            if (!ordersUndoToast || !ordersUndoText) return;
            ordersUndoText.textContent = `${count} order(s) will be deleted.`;
            ordersUndoToast.style.display = 'flex';
        };

        const hideUndoToast = () => {
            if (!ordersUndoToast) return;
            ordersUndoToast.style.display = 'none';
        };

        const scheduleDeleteOrders = () => {
            if (deleteOrdersTimer) clearTimeout(deleteOrdersTimer);
            const count = pendingDeleteIds.length;
            deletePending = true;
            updateSelectedOrdersUI();
            showUndoToast(count);
            deleteOrdersTimer = setTimeout(async () => {
                await runAction(deleteSelectedOrdersBtn, async () => {
                    for (const id of pendingDeleteIds) {
                        await safeRequest(`/api/orders/${id}`, { method: 'DELETE' });
                        selectedOrderIds.delete(id);
                    }
                    pendingDeleteIds = [];
                    await Promise.all([loadOrders(), loadOrderSummary()]);
                });
                deletePending = false;
                updateSelectedOrdersUI();
                hideUndoToast();
            }, 5000);
        };

        const undoDeleteOrders = () => {
            if (deleteOrdersTimer) clearTimeout(deleteOrdersTimer);
            deleteOrdersTimer = null;
            pendingDeleteIds = [];
            deletePending = false;
            updateSelectedOrdersUI();
            hideUndoToast();
        };

        if (deleteSelectedOrdersBtn) deleteSelectedOrdersBtn.addEventListener('click', () => {
            if (selectedOrderIds.size === 0) return;
            openDeleteOrdersModal();
        });

        if (deleteOrdersCancelBtn) deleteOrdersCancelBtn.addEventListener('click', closeDeleteOrdersModal);
        if (deleteOrdersModal) deleteOrdersModal.addEventListener('click', (e) => {
            if (e.target === deleteOrdersModal) closeDeleteOrdersModal();
        });

        if (deleteOrdersConfirmBtn) deleteOrdersConfirmBtn.addEventListener('click', () => {
            closeDeleteOrdersModal();
            pendingDeleteIds = Array.from(selectedOrderIds);
            if (!pendingDeleteIds.length) return;
            scheduleDeleteOrders();
        });

        if (ordersUndoBtn) ordersUndoBtn.addEventListener('click', undoDeleteOrders);

        if (ordersExportBtn) ordersExportBtn.addEventListener('click', async () => {
            const range = ordersExportRange ? ordersExportRange.value : 'monthly';
            const url = `/api/orders/export?range=${encodeURIComponent(range)}`;
            try {
                const res = await apiFetch(url);
                if (!res.ok) throw new Error('Export failed');
                const blob = await res.blob();
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = `orders-${range}-${Date.now()}.csv`;
                document.body.appendChild(link);
                link.click();
                link.remove();
                toast('CSV downloaded');
            } catch (err) {
                toast('Could not download CSV', 'error');
                console.error(err);
            }
        });

        function openPosReceipt(order) {
            try {
                const receiptWindow = window.open('', 'pos-receipt');
                if (!receiptWindow) return;
                const itemsHtml = (order.items || []).map(item => `
                    <div class="item-row">
                        <span class="item-name">${item.name || 'Product #' + item.product_id}</span>
                        <span class="item-qty">${item.quantity}</span>
                        <span class="item-price">₦${Number(item.unit_price || item.price || 0).toLocaleString()}</span>
                    </div>
                `).join('');
                const totalAmount = Number(order.total_amount || order.total || 0);
                const orderDate = new Date().toLocaleString('en-US', { month: 'short', day: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                
                receiptWindow.document.write(`
                    <!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Receipt - Order #${order.id}</title>
                        <style>
                            * {
                                margin: 0;
                                padding: 0;
                                box-sizing: border-box;
                            }
                            body {
                                font-family: "Courier New", monospace;
                                background: #f5f5f5;
                                padding: 20px;
                                color: #000;
                                line-height: 1.35;
                                -webkit-print-color-adjust: exact;
                                print-color-adjust: exact;
                            }
                            .receipt-container {
                                width: 80mm;
                                background: white;
                                margin: 0 auto;
                                padding: 22px 20px;
                                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                                position: relative;
                                overflow: hidden;
                            }
                            .receipt-watermark {
                                position: absolute;
                                top: 50%;
                                left: 50%;
                                transform: translate(-50%, -50%);
                                width: 74mm;
                                max-width: 94%;
                                max-height: 72%;
                                height: auto;
                                object-fit: contain;
                                opacity: 0.13;
                                filter: grayscale(1) contrast(260%) brightness(0.2);
                                z-index: 1;
                                pointer-events: none;
                            }
                            .receipt-container > :not(.receipt-watermark) {
                                position: relative;
                                z-index: 2;
                            }
                            .receipt-header {
                                text-align: center;
                                border-bottom: 2.5px solid #000;
                                padding-bottom: 12px;
                                margin-bottom: 16px;
                            }
                            .receipt-title {
                                width: 58mm;
                                max-width: 96%;
                                height: auto;
                                object-fit: contain;
                                margin: 0 auto 8px;
                                display: block;
                                filter: grayscale(1) contrast(280%) brightness(0);
                                image-rendering: -webkit-optimize-contrast;
                            }
                            .receipt-header p {
                                font-size: 12px;
                                color: #000;
                                font-weight: 600;
                            }
                            .receipt-company {
                                font-size: 11px;
                                color: #000;
                                margin-top: 6px;
                                line-height: 1.45;
                            }
                            .receipt-info {
                                font-size: 12px;
                                margin-bottom: 15px;
                                border-bottom: 1.5px dotted #000;
                                padding-bottom: 11px;
                            }
                            .receipt-info div {
                                display: flex;
                                justify-content: space-between;
                                margin-bottom: 5px;
                            }
                            .receipt-info label {
                                font-weight: bold;
                            }
                            .receipt-salesperson {
                                background: #fff;
                                border: 1.5px solid #000;
                                padding: 10px;
                                border-radius: 4px;
                                margin-bottom: 15px;
                                font-size: 12px;
                                text-align: center;
                            }
                            .receipt-salesperson strong {
                                display: block;
                                margin-bottom: 3px;
                                font-size: 13px;
                            }
                            .receipt-items {
                                font-size: 12px;
                                margin-bottom: 15px;
                                border-bottom: 1.5px dotted #000;
                                padding-bottom: 11px;
                            }
                            .item-header {
                                display: flex;
                                justify-content: space-between;
                                border-bottom: 1.5px solid #000;
                                padding-bottom: 5px;
                                margin-bottom: 5px;
                                font-weight: bold;
                            }
                            .item-row {
                                display: flex;
                                justify-content: space-between;
                                margin-bottom: 6px;
                            }
                            .item-name {
                                flex: 1;
                                word-break: break-word;
                                padding-right: 6px;
                            }
                            .item-qty {
                                width: 34px;
                                text-align: center;
                                font-weight: 700;
                            }
                            .item-price {
                                width: 58px;
                                text-align: right;
                                font-weight: 700;
                            }
                            .receipt-totals {
                                font-size: 13px;
                                margin-bottom: 15px;
                                border-bottom: 2.5px solid #000;
                                padding-bottom: 10px;
                            }
                            .total-row {
                                display: flex;
                                justify-content: space-between;
                                margin-bottom: 6px;
                            }
                            .total-row.grand-total {
                                font-weight: bold;
                                font-size: 15px;
                                margin-top: 5px;
                            }
                            .receipt-footer {
                                text-align: center;
                                font-size: 11px;
                                color: #000;
                                font-weight: 600;
                                margin-bottom: 15px;
                            }
                            .receipt-divider {
                                border-top: 1.5px dashed #000;
                                margin: 10px 0;
                            }
                            @media print {
                                body {
                                    background: white;
                                    padding: 0;
                                }
                                .receipt-container {
                                    width: 100%;
                                    box-shadow: none;
                                    padding: 2mm 1.8mm;
                                }
                                .receipt-watermark {
                                    opacity: 0.16 !important;
                                    filter: grayscale(1) contrast(280%) brightness(0.18) !important;
                                }
                                .receipt-title {
                                    width: 60mm;
                                    max-width: 98%;
                                    filter: grayscale(1) contrast(320%) brightness(0) !important;
                                }
                                * {
                                    color: #000 !important;
                                    -webkit-print-color-adjust: exact;
                                    print-color-adjust: exact;
                                }
                            }
                        </style>
                    </head>
                    <body>
                        <div class="receipt-container">
                            <img src="{{ asset('head.png') }}" alt="" class="receipt-watermark">
                            <!-- Header -->
                            <div class="receipt-header">
                                <img src="{{ asset('head.png') }}" alt="ARTSCI" class="receipt-title">
                                <p>Receipt</p>
                                <div class="receipt-company">
                                    Beside Anti-cultism Sars Road, PortHarcourt, Rivers State, Nigeria<br>
                                    Phone: 090160450776 · Email: support@artsci.com.ng<br>
                                    Instagram: @artsci_official
                                </div>
                                <p>Order #${order.id}</p>
                            </div>

                            <!-- Order Info -->
                            <div class="receipt-info">
                                <div>
                                    <label>Date:</label>
                                    <span>${orderDate}</span>
                                </div>
                                <div>
                                    <label>Order:</label>
                                    <span>#${order.id}</span>
                                </div>
                                <div>
                                    <label>Payment:</label>
                                    <span>${(order.payment_method || 'CASH').toUpperCase()}</span>
                                </div>
                            </div>

                            <!-- Salesperson Info -->
                            <div class="receipt-salesperson">
                                <strong>Sold By:</strong>
                                {{ auth()->user()->name ?? 'POS user' }}
                            </div>

                            <!-- Items -->
                            <div class="receipt-items">
                                <div class="item-header">
                                    <span class="item-name">Item</span>
                                    <span class="item-qty">Qty</span>
                                    <span class="item-price">Total</span>
                                </div>
                                ${itemsHtml}
                            </div>

                            <!-- Totals -->
                            <div class="receipt-totals">
                                <div class="total-row grand-total">
                                    <span>TOTAL:</span>
                                    <span>₦${totalAmount.toLocaleString()}</span>
                                </div>
                            </div>

                            <div class="receipt-divider"></div>

                            <!-- Footer -->
                            <div class="receipt-footer">
                                <p>Thank you for your purchase!</p>
                                <p>${new Date().toLocaleString()}</p>
                            </div>
                        </div>
                        <script>
                            window.onload = function(){ 
                                setTimeout(() => window.print(), 800); 
                            };
                        <\/script>
                    </body>
                    </html>
                `);
                receiptWindow.document.close();
            } catch (e) {
                console.error('Could not open receipt', e);
            }
        }

        window.toggleSoldOut = async (id, btn) => {
            await runAction(btn, async () => {
                await safeRequest(`/api/menu-items/${id}/toggle-sold-out`, { method: 'POST' });
                await loadMenu();
            });
        };

        window.deleteCategory = async (id, btn) => {
            if (!confirm('Delete this category? Items will remain uncategorized.')) return;
            await runAction(btn, async () => {
                await safeRequest(`/api/categories/${id}`, { method: 'DELETE' });
                await Promise.all([loadCategories(), loadMenu()]);
            });
        };

        window.deleteMenuItem = async (id, btn) => {
            if (!confirm('Delete this product?')) return;
            await runAction(btn, async () => {
                await safeRequest(`/api/menu-items/${id}`, { method: 'DELETE' });
                await loadMenu();
            });
        };

        window.copyBarcode = async (code) => {
            if (!code) {
                alert('This item does not have a barcode yet.');
                return;
            }
            const text = String(code);
            try {
                if (navigator && navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(text);
                    alert('Barcode copied to clipboard.');
                    return;
                }
                throw new Error('Clipboard API unavailable');
            } catch (err) {
                try {
                    const input = document.createElement('input');
                    input.value = text;
                    document.body.appendChild(input);
                    input.select();
                    document.execCommand('copy');
                    input.remove();
                    alert('Barcode copied to clipboard.');
                } catch (e) {
                    alert(`Barcode: ${text}`);
                }
            }
        };

        let barcodeLibPromise = null;
        const ensureBarcodeLib = () => {
            if (typeof JsBarcode !== 'undefined') return Promise.resolve();
            if (!barcodeLibPromise) {
                barcodeLibPromise = new Promise((resolve, reject) => {
                    const script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js';
                    script.onload = () => resolve();
                    script.onerror = () => reject(new Error('Could not load barcode library.'));
                    document.head.appendChild(script);
                });
            }
            return barcodeLibPromise;
        };

        window.printBarcode = async (code, name = '') => {
            if (!code) {
                alert('This item does not have a barcode yet.');
                return;
            }
            try {
                await ensureBarcodeLib();
            } catch (e) {
                alert('Barcode generator not loaded. Please check your connection and retry.');
                return;
            }
            try {
                const canvas = document.createElement('canvas');
                const safeCode = String(code);
                const safeName = String(name || 'Product');
                JsBarcode(canvas, safeCode, {
                    format: 'code128',
                    width: 2,
                    height: 80,
                    displayValue: true,
                    fontSize: 14,
                    margin: 10,
                });
                const link = document.createElement('a');
                const slug = safeName.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '') || 'item';
                link.download = `${slug}-${safeCode}.png`;
                link.href = canvas.toDataURL('image/png');
                document.body.appendChild(link);
                link.click();
                link.remove();
            } catch (e) {
                console.error(e);
                alert('Could not generate barcode image. Please try again.');
            }
        };

        window.regenBarcode = async (id, btn) => {
            if (!confirm('Regenerate barcode? Printed labels with the old code will stop working.')) return;
            await runAction(btn, async () => {
                await safeRequest(`/api/menu-items/${id}/regenerate-barcode`, { method: 'POST' });
                await loadMenu();
            });
        };

        window.editMenuItem = async (id, item, btn) => {
            const name = prompt('Name', item.name);
            if (name === null || name.trim() === '') return;
            const barcodePrompt = prompt('Barcode (leave blank to keep)', item.barcode || '');
            if (barcodePrompt === null) return;
            const barcode = barcodePrompt.trim();
            const priceInput = prompt('Price (NGN)', item.price);
            const price = Number(priceInput);
            if (Number.isNaN(price)) {
                alert('Invalid price');
                return;
            }
            const stockInput = prompt('Stock Quantity', item.stock ?? 0);
            const stockValue = stockInput === null || stockInput === '' ? 0 : Number(stockInput);
            if (Number.isNaN(stockValue) || stockValue < 0) {
                alert('Invalid stock quantity');
                return;
            }
            const descriptionPrompt = prompt('Description', item.description || '');
            const description = descriptionPrompt === null ? '' : descriptionPrompt;
            const category_id = prompt('Category ID (leave blank to unset)', item.category_id || '') || null;
            const payload = {
                name,
                price,
                stock: stockValue,
                description: description || null,
                category_id: category_id || null,
            };
            if (barcode) {
                payload.barcode = barcode;
            }
            await runAction(btn, async () => {
                await safeRequest(`/api/menu-items/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                });
                await loadMenu();
            });
        };

        window.updateUserRole = async (id, role) => {
            await runAction(null, async () => {
                await safeRequest(`/api/users/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ role }),
                });
                await loadUsers();
            });
        };

        window.toggleUserActive = async (id, active) => {
            await runAction(null, async () => {
                await safeRequest(`/api/users/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ is_active: active }),
                });
                await loadUsers();
            });
        };

        window.deleteUser = async (id) => {
            if (!confirm('Delete this user account?')) return;
            await runAction(null, async () => {
                await safeRequest(`/api/users/${id}`, { method: 'DELETE' });
                await loadUsers();
            });
        };

        async function init() {
            await checkHealth();
            renderPosCart();
            if (posBarcodeInput) posBarcodeInput.focus();
            await Promise.all([loadCategories(), loadMenu(), loadOrders(), loadOrderSummary(), loadUsers()]);
            renderSavedCustomers();
            renderParkedTickets();
            loadAdminAlerts();
            setInterval(loadAdminAlerts, 30000);

            const refresh = async () => {
                if (isInteracting) return;
                await Promise.all([loadCategories(), loadMenu(), loadOrders(), loadOrderSummary(), loadUsers()]);
            };
            const refreshPoller = createPoller(refresh, 5000, {
                onError: (err) => console.warn('Admin refresh failed', err),
            });
            refreshPoller.start();

            document.addEventListener('focusin', markInteracting);
            document.addEventListener('input', markInteracting);
            document.addEventListener('mousedown', markInteracting);
        }

        init();
        } catch (err) {
            console.error('Admin UI failed to init', err);
            alert('The admin interface failed to load. Please refresh or use a modern browser.');
        }
    })();
    </script>
</body>
</html>
