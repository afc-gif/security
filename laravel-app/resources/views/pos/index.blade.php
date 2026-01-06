<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ARTSCI - Point of Sale</title>
    <style>
        :root {
            --primary-blue: #03A9F4;
            --primary-dark-blue: #0c6aa8;
            --primary-yellow: #FFEB3B;
            --white: #FFFFFF;
            --dark: #0c1724;
            --muted: #5a6472;
            --bg: #f5f8fd;
            --card: #ffffff;
            --border: #e7edf6;
            --success: #4CAF50;
            --error: #f44336;
            --shadow: 0 4px 12px rgba(3, 169, 244, 0.1);
            --shadow-lg: 0 8px 24px rgba(3, 169, 244, 0.15);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Manrope", "Inter", system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: var(--dark);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        .pos-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 24px;
            padding: 24px;
            max-width: 1600px;
            margin: 0 auto;
            min-height: 100vh;
        }

        /* ==================== SCANNER SECTION ==================== */
        .scanner-section {
            position: fixed;
            bottom: 20px;
            right: 420px;
            background: linear-gradient(135deg, var(--primary-dark-blue), var(--primary-blue));
            color: var(--white);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(3, 169, 244, 0.25);
            width: 350px;
            z-index: 100;
            max-height: 400px;
            display: flex;
            flex-direction: column;
        }

        .scanner-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .scanner-header h3 {
            font-size: 14px;
            font-weight: 700;
            margin: 0;
        }

        .scanner-toggle {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: var(--white);
            width: 24px;
            height: 24px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            transition: var(--transition);
        }

        .scanner-toggle:hover {
            background: rgba(255, 235, 59, 0.3);
        }

        .scanner-input {
            width: 100%;
            padding: 10px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            font-size: 14px;
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
            margin-bottom: 12px;
            transition: var(--transition);
        }

        .scanner-input::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .scanner-input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.2);
            border-color: var(--primary-yellow);
            box-shadow: 0 0 0 3px rgba(255, 235, 59, 0.2);
        }

        .scanner-status {
            display: none;
            padding: 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
            animation: slideIn 0.3s ease;
        }

        .scanner-status.show {
            display: block;
        }

        .scanner-status.success {
            background: rgba(76, 175, 80, 0.2);
            border: 1px solid var(--success);
            color: #a5d6a7;
        }

        .scanner-status.error {
            background: rgba(244, 67, 54, 0.2);
            border: 1px solid var(--error);
            color: #ef9a9a;
        }

        .scanner-history {
            flex-grow: 1;
            overflow-y: auto;
            margin-bottom: 12px;
            padding-right: 6px;
        }

        .scanner-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 8px;
            border-radius: 6px;
            margin-bottom: 8px;
            font-size: 11px;
            border-left: 3px solid var(--primary-yellow);
        }

        .scanner-item-name {
            font-weight: 600;
            color: var(--white);
            margin-bottom: 2px;
        }

        .scanner-item-code {
            color: rgba(255, 255, 255, 0.7);
            font-family: monospace;
        }

        .scanner-actions {
            display: flex;
            gap: 8px;
            font-size: 12px;
        }

        .scanner-actions button {
            flex: 1;
            padding: 8px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            color: var(--white);
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
        }

        .scanner-actions button:hover {
            background: rgba(255, 235, 59, 0.3);
            border-color: var(--primary-yellow);
        }

        .scanner-status.loading {
            background: rgba(3, 169, 244, 0.2);
            border: 1px solid rgba(255, 235, 59, 0.5);
            color: var(--primary-yellow);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ==================== RESPONSIVE ==================== */
        @media (max-width: 1400px) {
            .scanner-section {
                right: 20px;
                width: 320px;
            }
        }

        @media (max-width: 768px) {
            .scanner-section {
                position: static;
                width: 100%;
                max-height: none;
            }
        }

        /* ==================== HEADER ==================== */
        .pos-header {
            grid-column: 1 / -1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, var(--primary-dark-blue), var(--primary-blue));
            color: var(--white);
            padding: 20px 30px;
            border-radius: 16px;
            box-shadow: var(--shadow-lg);
            margin-bottom: 10px;
        }

        .pos-brand {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .pos-logo {
            width: 60px;
            height: 60px;
            background: var(--white);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
        }

        .pos-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .pos-title {
            display: flex;
            flex-direction: column;
        }

        .pos-title h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
        }

        .pos-title p {
            font-size: 12px;
            opacity: 0.9;
            margin-top: 4px;
        }

        .pos-clock {
            font-size: 32px;
            font-weight: 700;
            background: rgba(255, 235, 59, 0.2);
            padding: 8px 16px;
            border-radius: 8px;
            border: 2px solid var(--primary-yellow);
            color: var(--primary-yellow);
        }

        /* ==================== LEFT SECTION - PRODUCTS ==================== */
        .products-section {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .section-header h2 {
            font-size: 20px;
            font-weight: 700;
            color: var(--dark);
        }

        .search-box {
            position: relative;
            margin-bottom: 15px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            transition: var(--transition);
            background: var(--white);
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(3, 169, 244, 0.1);
        }

        .search-box svg {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: var(--muted);
        }

        .categories {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .category-btn {
            padding: 8px 16px;
            border: 2px solid var(--border);
            background: var(--white);
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            transition: var(--transition);
            color: var(--dark);
        }

        .category-btn:hover,
        .category-btn.active {
            background: var(--primary-blue);
            border-color: var(--primary-blue);
            color: var(--white);
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
        }

        .product-card {
            background: var(--card);
            border: 2px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 8px rgba(12, 23, 36, 0.04);
        }

        .product-card:hover {
            border-color: var(--primary-blue);
            box-shadow: var(--shadow);
            transform: translateY(-4px);
        }

        .product-image {
            width: 100%;
            height: 140px;
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-dark-blue));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 40px;
        }

        .product-info {
            padding: 12px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-name {
            font-weight: 600;
            font-size: 13px;
            color: var(--dark);
            margin-bottom: 6px;
        }

        .product-sku {
            font-size: 11px;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .product-price {
            font-size: 16px;
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 8px;
        }

        .product-stock {
            font-size: 11px;
            color: var(--success);
            margin-bottom: 8px;
        }

        .product-card button {
            width: 100%;
            padding: 8px;
            background: var(--primary-blue);
            color: var(--white);
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 12px;
            transition: var(--transition);
        }

        .product-card button:hover {
            background: var(--primary-dark-blue);
        }

        /* ==================== RIGHT SECTION - CART ==================== */
        .cart-section {
            position: sticky;
            top: 24px;
            display: flex;
            flex-direction: column;
            height: fit-content;
            max-height: calc(100vh - 48px);
        }

        .cart-header {
            background: linear-gradient(135deg, var(--primary-yellow), #fdd835);
            color: var(--dark);
            padding: 15px;
            border-radius: 12px 12px 0 0;
            font-weight: 700;
            font-size: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .cart-badge {
            background: var(--error);
            color: var(--white);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
        }

        .cart-items {
            flex-grow: 1;
            overflow-y: auto;
            background: var(--card);
            padding: 15px;
            border: 2px solid var(--border);
            border-top: none;
        }

        .cart-item {
            background: var(--bg);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid var(--primary-blue);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .cart-item-info {
            flex-grow: 1;
        }

        .cart-item-name {
            font-weight: 600;
            font-size: 13px;
            color: var(--dark);
            margin-bottom: 4px;
        }

        .cart-item-sku {
            font-size: 10px;
            color: var(--muted);
            margin-bottom: 6px;
        }

        .cart-item-price {
            font-size: 13px;
            font-weight: 700;
            color: var(--primary-blue);
        }

        .cart-item-qty {
            display: flex;
            gap: 5px;
            align-items: center;
            margin-top: 8px;
        }

        .qty-btn {
            width: 24px;
            height: 24px;
            border: 1px solid var(--border);
            background: var(--white);
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
            color: var(--primary-blue);
            transition: var(--transition);
        }

        .qty-btn:hover {
            background: var(--primary-blue);
            color: var(--white);
        }

        .qty-input {
            width: 30px;
            height: 24px;
            border: 1px solid var(--border);
            border-radius: 4px;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
        }

        .cart-item-remove {
            background: var(--error);
            color: var(--white);
            border: none;
            width: 24px;
            height: 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            align-self: center;
        }

        .cart-item-remove:hover {
            background: #d32f2f;
        }

        .cart-empty {
            text-align: center;
            padding: 30px 15px;
            color: var(--muted);
        }

        .cart-empty svg {
            width: 50px;
            height: 50px;
            margin-bottom: 10px;
            opacity: 0.5;
        }

        .cart-summary {
            background: var(--card);
            padding: 15px;
            border: 2px solid var(--border);
            border-top: none;
            border-radius: 0 0 12px 12px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 13px;
        }

        .summary-row.subtotal {
            color: var(--muted);
        }

        .summary-row.tax {
            color: var(--muted);
        }

        .summary-row.total {
            border-top: 2px solid var(--border);
            padding-top: 10px;
            font-size: 16px;
            font-weight: 700;
            color: var(--primary-blue);
        }

        .summary-value {
            font-weight: 600;
        }

        .payment-method {
            margin: 15px 0;
            display: flex;
            gap: 8px;
        }

        .payment-btn {
            flex: 1;
            padding: 10px;
            border: 2px solid var(--border);
            background: var(--white);
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: var(--transition);
            color: var(--dark);
        }

        .payment-btn.active {
            background: var(--primary-blue);
            border-color: var(--primary-blue);
            color: var(--white);
        }

        .checkout-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary-blue), var(--primary-dark-blue));
            color: var(--white);
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(3, 169, 244, 0.3);
        }

        .checkout-btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(3, 169, 244, 0.4);
        }

        .checkout-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .clear-cart-btn {
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            background: var(--border);
            color: var(--muted);
            border: none;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
        }

        .clear-cart-btn:hover {
            background: var(--error);
            color: var(--white);
        }

        /* ==================== RESPONSIVE ==================== */
        @media (max-width: 1200px) {
            .pos-container {
                grid-template-columns: 1fr;
            }

            .cart-section {
                position: static;
                max-height: none;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .pos-container {
                padding: 12px;
                gap: 16px;
            }

            .pos-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .pos-title h1 {
                font-size: 22px;
            }

            .pos-clock {
                font-size: 24px;
            }

            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }
        }

        /* ==================== SCROLLBAR ==================== */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-blue);
        }
    </style>
</head>
<body>
    <div class="pos-container">
        <!-- HEADER -->
        <div class="pos-header">
            <div class="pos-brand">
                <div class="pos-logo">
                    <img src="/Artsci Logo REAL 1.webp" alt="ARTSCI Logo">
                </div>
                <div class="pos-title">
                    <h1>ARTSCI</h1>
                    <p>Professional POS System</p>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 20px;">
                <div class="pos-clock" id="clock">00:00:00</div>
                <div style="display: flex; gap: 10px;">
                    <a href="{{ route('admin.dashboard') }}" style="background: rgba(255,235,59,0.3); border: 2px solid var(--primary-yellow); color: var(--primary-yellow); padding: 8px 16px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 12px; transition: all 0.3s;">📊 Admin</a>
                    <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" style="background: rgba(244,67,54,0.3); border: 2px solid #f44336; color: #f44336; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 12px; cursor: pointer; transition: all 0.3s;">🚪 Logout</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- LEFT: PRODUCTS -->
        <div class="products-section">
            <div class="section-header">
                <h2>📦 Products Catalog</h2>
            </div>

            <!-- Search -->
            <div class="search-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.35-4.35"></path>
                </svg>
                <input type="text" id="searchInput" placeholder="Search products...">
            </div>

            <!-- Categories -->
            <div class="categories">
                <button class="category-btn active" data-category="all">All Products</button>
                <button class="category-btn" data-category="cctv">CCTV Systems</button>
                <button class="category-btn" data-category="solar">Solar</button>
                <button class="category-btn" data-category="power">Power</button>
            </div>

            <!-- Products Grid -->
            <div class="products-grid" id="productsGrid">
                <!-- Products will be inserted here by JavaScript -->
            </div>
        </div>

        <!-- RIGHT: CART & CHECKOUT -->
        <div class="cart-section">
            <div class="cart-header">
                <span>🛒 Shopping Cart</span>
                <span class="cart-badge" id="cartCount">0</span>
            </div>

            <div class="cart-items" id="cartItems">
                <div class="cart-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <p>No items in cart</p>
                </div>
            </div>

            <div class="cart-summary">
                <div class="summary-row subtotal">
                    <span>Subtotal:</span>
                    <span class="summary-value" id="subtotal">₦0.00</span>
                </div>
                <div class="summary-row tax">
                    <span>Tax (7.5%):</span>
                    <span class="summary-value" id="tax">₦0.00</span>
                </div>
                <div class="summary-row total">
                    <span>Total:</span>
                    <span class="summary-value" id="total">₦0.00</span>
                </div>

                <div class="payment-method">
                    <button class="payment-btn active" data-method="cash">💰 Cash</button>
                    <button class="payment-btn" data-method="card">💳 Card</button>
                    <button class="payment-btn" data-method="mobile">📱 Mobile</button>
                </div>

                <button class="checkout-btn" id="checkoutBtn" disabled>Complete Sale</button>
                <button class="clear-cart-btn" id="clearCartBtn">Clear Cart</button>
            </div>
        </div>

        <!-- SCANNER SECTION -->
        <div class="scanner-section" id="scannerSection">
            <div class="scanner-header">
                <h3>📱 Barcode Scanner</h3>
                <button class="scanner-toggle" id="scannerToggle" title="Toggle scanner">−</button>
            </div>

            <input 
                type="text" 
                class="scanner-input" 
                id="scannerInput" 
                placeholder="Scan barcode here..."
                autocomplete="off"
            >

            <div class="scanner-status" id="scannerStatus"></div>

            <div class="scanner-history" id="scannerHistory">
                <div style="text-align: center; color: rgba(255,255,255,0.6); font-size: 12px; padding: 20px;">
                    No scans yet
                </div>
            </div>

            <div class="scanner-actions">
                <button id="clearScannerBtn">🗑️ Clear</button>
                <button id="toggleScannerDbBtn" title="Toggle between sample data and database">🔄 Mode</button>
            </div>
        </div>
    </div>

    <script>
        // Sample Products Database
        const products = [
            { id: 1, name: "Hikvision Camera", sku: "HK-001", price: 45000, category: "cctv", stock: 12, emoji: "📹" },
            { id: 2, name: "CCTV DVR", sku: "DVR-001", price: 65000, category: "cctv", stock: 8, emoji: "🎥" },
            { id: 3, name: "Solar Panel 400W", sku: "SOL-001", price: 180000, category: "solar", stock: 5, emoji: "☀️" },
            { id: 4, name: "Battery Bank 10kW", sku: "BAT-001", price: 450000, category: "solar", stock: 3, emoji: "🔋" },
            { id: 5, name: "Inverter 5kW", sku: "INV-001", price: 280000, category: "power", stock: 6, emoji: "⚡" },
            { id: 6, name: "Cable Reel 100m", sku: "CBL-001", price: 15000, category: "power", stock: 20, emoji: "🔌" },
            { id: 7, name: "Smart Thermostat", sku: "SMT-001", price: 35000, category: "power", stock: 14, emoji: "🌡️" },
            { id: 8, name: "Door Access Control", sku: "ACC-001", price: 95000, category: "cctv", stock: 7, emoji: "🚪" },
        ];

        let cart = [];
        let selectedPaymentMethod = "cash";

        // Initialize
        function init() {
            renderProducts(products);
            updateClock();
            setInterval(updateClock, 1000);
            setupEventListeners();
        }

        // Render Products
        function renderProducts(productsToShow) {
            const grid = document.getElementById("productsGrid");
            grid.innerHTML = productsToShow.map(product => `
                <div class="product-card">
                    <div class="product-image">${product.emoji}</div>
                    <div class="product-info">
                        <div class="product-name">${product.name}</div>
                        <div class="product-sku">${product.sku}</div>
                        <div class="product-price">₦${product.price.toLocaleString()}</div>
                        <div class="product-stock">Stock: ${product.stock}</div>
                        <button onclick="addToCart(${product.id})">Add to Cart</button>
                    </div>
                </div>
            `).join("");
        }

        // Add to Cart
        function addToCart(productId) {
            const product = products.find(p => p.id === productId);
            const cartItem = cart.find(item => item.id === productId);

            if (cartItem) {
                cartItem.quantity++;
            } else {
                cart.push({
                    ...product,
                    quantity: 1
                });
            }

            updateCart();
        }

        // Remove from Cart
        function removeFromCart(productId) {
            cart = cart.filter(item => item.id !== productId);
            updateCart();
        }

        // Update Quantity
        function updateQuantity(productId, quantity) {
            const cartItem = cart.find(item => item.id === productId);
            if (cartItem) {
                cartItem.quantity = Math.max(1, quantity);
                updateCart();
            }
        }

        // Update Cart Display
        function updateCart() {
            const cartItems = document.getElementById("cartItems");
            const cartCount = document.getElementById("cartCount");
            const checkoutBtn = document.getElementById("checkoutBtn");

            if (cart.length === 0) {
                cartItems.innerHTML = `
                    <div class="cart-empty">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        <p>No items in cart</p>
                    </div>
                `;
                checkoutBtn.disabled = true;
            } else {
                cartItems.innerHTML = cart.map(item => `
                    <div class="cart-item">
                        <div class="cart-item-info">
                            <div class="cart-item-name">${item.name}</div>
                            <div class="cart-item-sku">${item.sku}</div>
                            <div class="cart-item-price">₦${item.price.toLocaleString()}</div>
                            <div class="cart-item-qty">
                                <button class="qty-btn" onclick="updateQuantity(${item.id}, ${item.quantity - 1})">−</button>
                                <input type="number" class="qty-input" value="${item.quantity}" onchange="updateQuantity(${item.id}, this.value)">
                                <button class="qty-btn" onclick="updateQuantity(${item.id}, ${item.quantity + 1})">+</button>
                            </div>
                        </div>
                        <button class="cart-item-remove" onclick="removeFromCart(${item.id})">✕</button>
                    </div>
                `).join("");
                checkoutBtn.disabled = false;
            }

            // Update Summary
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const taxAmount = subtotal * 0.075;
            const total = subtotal + taxAmount;

            document.getElementById("cartCount").textContent = cart.length;
            document.getElementById("subtotal").textContent = "₦" + subtotal.toLocaleString();
            document.getElementById("tax").textContent = "₦" + taxAmount.toLocaleString();
            document.getElementById("total").textContent = "₦" + total.toLocaleString();
        }

        // Update Clock
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, "0");
            const minutes = String(now.getMinutes()).padStart(2, "0");
            const seconds = String(now.getSeconds()).padStart(2, "0");
            document.getElementById("clock").textContent = `${hours}:${minutes}:${seconds}`;
        }

        // Setup Event Listeners
        function setupEventListeners() {
            // Category Filter
            document.querySelectorAll(".category-btn").forEach(btn => {
                btn.addEventListener("click", function() {
                    document.querySelectorAll(".category-btn").forEach(b => b.classList.remove("active"));
                    this.classList.add("active");

                    const category = this.dataset.category;
                    const filtered = category === "all" ? products : products.filter(p => p.category === category);
                    renderProducts(filtered);
                });
            });

            // Search
            document.getElementById("searchInput").addEventListener("input", function(e) {
                const query = e.target.value.toLowerCase();
                const filtered = products.filter(p => 
                    p.name.toLowerCase().includes(query) || 
                    p.sku.toLowerCase().includes(query)
                );
                renderProducts(filtered);
            });

            // Payment Methods
            document.querySelectorAll(".payment-btn").forEach(btn => {
                btn.addEventListener("click", function() {
                    document.querySelectorAll(".payment-btn").forEach(b => b.classList.remove("active"));
                    this.classList.add("active");
                    selectedPaymentMethod = this.dataset.method;
                });
            });

            // Checkout
            document.getElementById("checkoutBtn").addEventListener("click", async function() {
                if (cart.length === 0) {
                    alert("Cart is empty!");
                    return;
                }

                const totalText = document.getElementById("total").textContent;
                const total = parseFloat(totalText.replace('$', ''));

                try {
                    // Send sale data to server
                    const response = await fetch('/api/pos/complete-sale', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({
                            items: cart,
                            total: total,
                            payment_method: selectedPaymentMethod
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        // Show receipt
                        alert(`Sale completed!\nOrder #${result.sale_id}\nTotal: $${total.toFixed(2)}\nSold by: ${result.salesperson}\n\nThank you for your purchase!`);
                        
                        // Redirect to receipt page
                        window.location.href = `/pos/receipt/${result.sale_id}`;
                    } else {
                        alert(`Error: ${result.error}`);
                    }
                } catch (error) {
                    alert(`Error completing sale: ${error.message}`);
                    console.error('Sale error:', error);
                }
            });

            // Clear Cart
            document.getElementById("clearCartBtn").addEventListener("click", function() {
                if (confirm("Are you sure you want to clear the cart?")) {
                    cart = [];
                    updateCart();
                }
            });

            // Scanner functionality
            setupScannerListeners();
        }

        // ==================== SCANNER FUNCTIONALITY ====================
        let scannerHistory = [];
        let useDatabaseMode = true;
        let isScannerMinimized = false;

        // Barcode lookup mapping for sample data (simulating product SKUs as barcodes)
        const barcodeMap = {
            "HK-001": 1,      // Hikvision Camera
            "DVR-001": 2,     // CCTV DVR
            "SOL-001": 3,     // Solar Panel
            "BAT-001": 4,     // Battery Bank
            "INV-001": 5,     // Inverter
            "CBL-001": 6,     // Cable Reel
            "SMT-001": 7,     // Smart Thermostat
            "ACC-001": 8,     // Door Access Control
        };

        function setupScannerListeners() {
            const scannerInput = document.getElementById("scannerInput");
            const scannerStatus = document.getElementById("scannerStatus");
            const clearScannerBtn = document.getElementById("clearScannerBtn");
            const toggleScannerDbBtn = document.getElementById("toggleScannerDbBtn");
            const scannerToggle = document.getElementById("scannerToggle");

            // Scanner input - captures barcode
            scannerInput.addEventListener("keydown", async function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    const barcode = this.value.trim();

                    if (!barcode) {
                        showScannerStatus("Please scan a barcode", "error");
                        return;
                    }

                    showScannerStatus("Processing barcode...", "loading");

                    try {
                        let product = null;

                        if (useDatabaseMode) {
                            // Fetch from database via API
                            product = await lookupProductByBarcode(barcode);
                        } else {
                            // Use sample data mapping
                            const productId = barcodeMap[barcode];
                            product = productId ? products.find(p => p.id === productId) : null;
                        }

                        if (product) {
                            addToCart(product.id);
                            addToScannerHistory(product.name, barcode, true);
                            showScannerStatus(`✓ Added: ${product.name}`, "success");
                        } else {
                            addToScannerHistory(`Unknown: ${barcode}`, barcode, false);
                            showScannerStatus("✗ Product not found", "error");
                        }
                    } catch (error) {
                        addToScannerHistory(`Error scanning: ${barcode}`, barcode, false);
                        showScannerStatus("✗ Scan error", "error");
                    }

                    // Clear input for next scan
                    this.value = "";
                    this.focus();
                }
            });

            // Clear scanner history
            clearScannerBtn.addEventListener("click", function() {
                scannerHistory = [];
                document.getElementById("scannerHistory").innerHTML = `
                    <div style="text-align: center; color: rgba(255,255,255,0.6); font-size: 12px; padding: 20px;">
                        No scans yet
                    </div>
                `;
            });

            // Toggle database mode
            toggleScannerDbBtn.addEventListener("click", function() {
                useDatabaseMode = !useDatabaseMode;
                const mode = useDatabaseMode ? "Database" : "Sample";
                this.title = `Current: ${mode} Mode`;
                showScannerStatus(`Switched to ${mode} mode`, "success");
            });

            // Toggle scanner visibility
            scannerToggle.addEventListener("click", function() {
                isScannerMinimized = !isScannerMinimized;
                const scannerHistory = document.getElementById("scannerHistory");
                const scannerActions = document.querySelector(".scanner-actions");
                const scannerInput = document.getElementById("scannerInput");

                if (isScannerMinimized) {
                    scannerInput.style.display = "none";
                    scannerHistory.style.display = "none";
                    scannerActions.style.display = "none";
                    this.textContent = "+";
                } else {
                    scannerInput.style.display = "block";
                    scannerHistory.style.display = "block";
                    scannerActions.style.display = "flex";
                    this.textContent = "−";
                }
            });

            // Focus scanner on load
            scannerInput.focus();
        }

        function addToScannerHistory(name, barcode, success) {
            scannerHistory.unshift({ name, barcode, success, time: new Date() });
            if (scannerHistory.length > 10) scannerHistory.pop();
            updateScannerDisplay();
        }

        function updateScannerDisplay() {
            const historyDiv = document.getElementById("scannerHistory");
            if (scannerHistory.length === 0) {
                historyDiv.innerHTML = `
                    <div style="text-align: center; color: rgba(255,255,255,0.6); font-size: 12px; padding: 20px;">
                        No scans yet
                    </div>
                `;
                return;
            }

            historyDiv.innerHTML = scannerHistory.map((item, idx) => `
                <div class="scanner-item" style="border-left-color: ${item.success ? '#4CAF50' : '#f44336'};">
                    <div class="scanner-item-name">${item.success ? '✓' : '✗'} ${item.name}</div>
                    <div class="scanner-item-code">${item.barcode}</div>
                </div>
            `).join("");
        }

        function showScannerStatus(message, type) {
            const statusDiv = document.getElementById("scannerStatus");
            statusDiv.textContent = message;
            statusDiv.className = `scanner-status show ${type}`;
            setTimeout(() => {
                statusDiv.classList.remove("show");
            }, 3000);
        }

        async function lookupProductByBarcode(barcode) {
            try {
                const response = await fetch(`/api/pos/barcode/${encodeURIComponent(barcode)}`);
                if (!response.ok) return null;
                return await response.json();
            } catch (error) {
                console.error("Barcode lookup error:", error);
                return null;
            }
        }

        // Start
        init();
    </script>
</body>
</html>
