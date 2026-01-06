<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ARTSCI - Point of Sale</title>
    <style>
        :root {
            --bg: #f4f5f7;
            --surface: #ffffff;
            --border: #d6d9de;
            --text: #0f172a;
            --muted: #6b7280;
            --accent: #2563eb;
            --accent-strong: #1d4ed8;
            --success: #15803d;
            --error: #b91c1c;
            --shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Inter", "Segoe UI", system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.5;
        }

        .pos-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
            padding: 24px;
            max-width: 1500px;
            margin: 0 auto;
            min-height: 100vh;
        }

        /* SCANNER */
        .scanner-section {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--surface);
            color: var(--text);
            padding: 14px;
            border-radius: 10px;
            box-shadow: var(--shadow);
            width: 320px;
            z-index: 100;
            max-height: 440px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            border: 1px solid var(--border);
        }

        .scanner-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .scanner-header h3 {
            font-size: 14px;
            font-weight: 700;
            color: var(--text);
        }

        .scanner-toggle {
            background: var(--bg);
            border: 1px solid var(--border);
            color: var(--text);
            width: 24px;
            height: 24px;
            border-radius: 6px;
            cursor: pointer;
        }

        .scanner-input {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 14px;
            background: var(--bg);
            color: var(--text);
        }

        .scanner-input:focus {
            outline: none;
            border-color: var(--accent);
            background: #eef2ff;
        }

        .scanner-status {
            display: none;
            padding: 8px 10px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
        }

        .scanner-status.show {
            display: block;
        }

        .scanner-status.success {
            background: #ecfdf3;
            border: 1px solid #bbf7d0;
            color: var(--success);
        }

        .scanner-status.error {
            background: #fef2f2;
            border: 1px solid #fecdd3;
            color: var(--error);
        }

        .scanner-status.loading {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: var(--accent-strong);
        }

        .scanner-history {
            flex-grow: 1;
            overflow-y: auto;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 8px;
            background: var(--bg);
        }

        .scanner-item {
            background: var(--surface);
            padding: 8px;
            border-radius: 8px;
            margin-bottom: 8px;
            font-size: 11px;
            border: 1px solid var(--border);
            border-left: 4px solid var(--accent);
        }

        .scanner-item-name {
            font-weight: 600;
            color: var(--text);
            margin-bottom: 2px;
        }

        .scanner-item-code {
            color: var(--muted);
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
            border: 1px solid var(--border);
            background: var(--bg);
            color: var(--text);
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .scanner-actions button:hover {
            border-color: var(--accent);
            color: var(--accent);
        }

        /* RESPONSIVE */
        @media (max-width: 1400px) {
            .pos-container {
                grid-template-columns: 1fr 360px;
            }
        }

        @media (max-width: 1100px) {
            .pos-container {
                grid-template-columns: 1fr;
            }
            .cart-section {
                position: static;
                max-height: none;
            }
            .scanner-section {
                position: static;
                width: 100%;
                max-height: none;
                box-shadow: none;
            }
        }

        @media (max-width: 640px) {
            .pos-container {
                padding: 16px;
            }
        }

        /* HEADER */
        .pos-header {
            grid-column: 1 / -1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--surface);
            color: var(--text);
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            margin-bottom: 10px;
        }

        .pos-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .pos-logo {
            width: 52px;
            height: 52px;
            background: var(--bg);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 6px;
            border: 1px solid var(--border);
        }

        .pos-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .pos-title {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .pos-title h1 {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
        }

        .pos-title p {
            font-size: 12px;
            color: var(--muted);
        }

        .pos-clock {
            font-size: 20px;
            font-weight: 700;
            background: var(--bg);
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid var(--border);
            color: var(--text);
        }

        /* LEFT SECTION - PRODUCTS */
        .products-section {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .section-header h2 {
            font-size: 18px;
            font-weight: 700;
            color: var(--text);
        }

        .search-box {
            position: relative;
            margin-bottom: 12px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            background: var(--surface);
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--accent);
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
            margin-bottom: 12px;
        }

        .category-btn {
            padding: 8px 16px;
            border: 1px solid var(--border);
            background: var(--surface);
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
            color: var(--text);
        }

        .category-btn:hover,
        .category-btn.active {
            background: var(--accent);
            border-color: var(--accent);
            color: #fff;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
        }

        .product-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            box-shadow: var(--shadow);
        }

        .product-card:hover {
            border-color: var(--accent);
        }

        .product-image {
            width: 100%;
            height: 140px;
            background: var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--muted);
            font-size: 20px;
            font-weight: 700;
        }

        .product-info {
            padding: 12px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-name {
            font-weight: 600;
            font-size: 14px;
            color: var(--text);
            margin-bottom: 6px;
        }

        .product-sku {
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .product-price {
            font-size: 16px;
            font-weight: 700;
            color: var(--accent-strong);
            margin-bottom: 8px;
        }

        .product-stock {
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 10px;
        }

        .product-card button {
            width: 100%;
            padding: 10px;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 12px;
        }

        .product-card button:hover {
            background: var(--accent-strong);
        }

        /* RIGHT SECTION - CART */
        .cart-section {
            position: sticky;
            top: 24px;
            display: flex;
            flex-direction: column;
            height: fit-content;
            max-height: calc(100vh - 48px);
        }

        .cart-header {
            background: var(--surface);
            color: var(--text);
            padding: 14px;
            border-radius: 12px 12px 0 0;
            font-weight: 700;
            font-size: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 1px solid var(--border);
        }

        .cart-badge {
            background: var(--bg);
            color: var(--text);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid var(--border);
        }

        .cart-items {
            flex-grow: 1;
            overflow-y: auto;
            background: var(--surface);
            padding: 14px;
            border: 1px solid var(--border);
            border-top: none;
        }

        .cart-item {
            background: var(--surface);
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 10px;
            border: 1px solid var(--border);
            border-left: 4px solid var(--accent);
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
            color: var(--text);
            margin-bottom: 4px;
        }

        .cart-item-sku {
            font-size: 12px;
            color: var(--muted);
        }

        .cart-item-price {
            font-weight: 700;
            color: var(--accent-strong);
            margin: 6px 0;
        }

        .cart-item-qty {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cart-item-qty button {
            width: 28px;
            height: 28px;
            border: 1px solid var(--border);
            background: var(--bg);
            color: var(--text);
            border-radius: 6px;
            cursor: pointer;
        }

        .cart-item-qty input {
            padding: 6px;
            border-radius: 6px;
            width: 50px;
            text-align: center;
            border: 1px solid var(--border);
            background: var(--surface);
        }

        .cart-item-remove {
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            font-size: 18px;
        }

        .cart-summary {
            background: var(--surface);
            padding: 16px;
            border: 1px solid var(--border);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .summary-row.total {
            font-weight: 700;
            font-size: 16px;
        }

        .summary-row.total .summary-value {
            color: var(--accent-strong);
        }

        .summary-value {
            font-weight: 700;
            color: var(--text);
        }

        .payment-method {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-top: 16px;
        }

        .payment-btn {
            border: 1px solid var(--border);
            background: var(--surface);
            padding: 10px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            font-size: 13px;
            color: var(--text);
        }

        .payment-btn.active {
            background: var(--accent);
            color: #fff;
            border-color: var(--accent);
        }

        .checkout-btn {
            width: 100%;
            padding: 12px;
            background: var(--accent);
            color: #fff;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 700;
            margin-top: 14px;
        }

        .checkout-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .checkout-btn:hover:not(:disabled) {
            background: var(--accent-strong);
        }

        .clear-cart-btn {
            width: 100%;
            padding: 10px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            margin-top: 10px;
            color: var(--muted);
        }

        /* EMPTY STATE */
        .cart-empty {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            color: var(--muted);
            padding: 30px 10px;
        }

        .cart-empty svg {
            width: 42px;
            height: 42px;
        }

        .cart-empty p {
            font-size: 13px;
            color: var(--muted);
        }

        .section-header button {
            border: 1px solid var(--border);
            background: var(--surface);
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            color: var(--text);
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
            <div style="display: flex; align-items: center; gap: 16px;">
                <div class="pos-clock" id="clock">00:00:00</div>
                <div style="display: flex; gap: 8px;">
                    <a href="{{ route('admin.dashboard') }}" style="border: 1px solid var(--border); background: var(--surface); color: var(--text); padding: 8px 14px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 12px;">Admin</a>
                    <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" style="border: 1px solid var(--border); background: var(--surface); color: var(--text); padding: 8px 14px; border-radius: 8px; font-weight: 600; font-size: 12px; cursor: pointer;">Logout</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- LEFT: PRODUCTS -->
        <div class="products-section">
            <div class="section-header">
                <h2>Products</h2>
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
                <span>Cart</span>
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
                    <button class="payment-btn active" data-method="cash">Cash</button>
                    <button class="payment-btn" data-method="card">Card</button>
                    <button class="payment-btn" data-method="mobile">Mobile</button>
                </div>

                <button class="checkout-btn" id="checkoutBtn" disabled>Complete Sale</button>
                <button class="clear-cart-btn" id="clearCartBtn">Clear Cart</button>
            </div>
        </div>

        <!-- SCANNER SECTION -->
        <div class="scanner-section" id="scannerSection">
            <div class="scanner-header">
                <h3>Barcode Scanner</h3>
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
                <div style="text-align: center; color: var(--muted); font-size: 12px; padding: 20px;">
                    No scans yet
                </div>
            </div>

            <div class="scanner-actions">
                <button id="clearScannerBtn">Clear</button>
                <button id="toggleScannerDbBtn" title="Toggle between sample data and database">Mode</button>
            </div>
        </div>
    </div>

    <script>
        // Products loaded from database
        let products = [];
        let cart = [];
        let selectedPaymentMethod = "cash";

        // Initialize
        async function init() {
            await loadProducts();
            updateClock();
            setInterval(updateClock, 1000);
            setupEventListeners();
            // Auto-focus barcode scanner for immediate scanning
            document.getElementById("scannerInput").focus();
        }

        // Load products from database API
        async function loadProducts() {
            try {
                const response = await fetch('/api/pos/products', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                });
                const data = await response.json();
                products = data || [];
                renderProducts(products);
            } catch (error) {
                console.error('Error loading products:', error);
                alert('Error loading products from database');
            }
        }

        // Render Products
        function renderProducts(productsToShow) {
            const grid = document.getElementById("productsGrid");
            grid.innerHTML = productsToShow.map(product => `
                <div class="product-card">
                    <div class="product-image">Item</div>
                    <div class="product-info">
                        <div class="product-name">${product.name || product.product_name}</div>
                        <div class="product-sku">${product.sku || product.barcode || 'N/A'}</div>
                        <div class="product-price">₦${Number(product.price || 0).toLocaleString()}</div>
                        <div class="product-stock">Stock: ${product.stock || 0}</div>
                        <button onclick="addToCart(${product.id})">Add to Cart</button>
                    </div>
                </div>
            `).join("");
        }

        // Add to Cart
        function addToCart(productId, productData = null) {
            const product = products.find(p => p.id === productId) || productData;
            if (!product) {
                alert("Product unavailable. Please reload products.");
                return;
            }
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
            const numericQty = Math.max(1, parseInt(quantity, 10) || 1);
            const cartItem = cart.find(item => item.id === productId);
            if (cartItem) {
                cartItem.quantity = numericQty;
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
                            <div class="cart-item-name">${item.name || item.product_name || 'Item'}</div>
                            <div class="cart-item-sku">${item.sku || item.barcode || 'N/A'}</div>
                            <div class="cart-item-price">₦${Number(item.price || 0).toLocaleString()}</div>
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
            const subtotal = cart.reduce((sum, item) => {
                const price = Number(item.price || 0);
                return sum + (price * item.quantity);
            }, 0);
            const taxAmount = subtotal * 0.075;
            const total = subtotal + taxAmount;

            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            document.getElementById("cartCount").textContent = totalItems;
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
                // Strip currency symbols/commas so backend receives a numeric total
                const total = parseFloat(totalText.replace(/[^\d.-]/g, ''));
                if (Number.isNaN(total)) {
                    alert("Unable to read total amount. Please try again.");
                    return;
                }

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
                        alert(`Sale completed!\nOrder #${result.sale_id}\nTotal: ₦${total.toFixed(2)}\nSold by: ${result.salesperson}\n\nThank you for your purchase!`);
                        
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
                            addToCart(product.id, product);
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
                    <div style="text-align: center; color: var(--muted); font-size: 12px; padding: 20px;">
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
                    <div style="text-align: center; color: var(--muted); font-size: 12px; padding: 20px;">
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
                const url = `/api/pos/barcode/${encodeURIComponent(barcode)}`;
                console.log('Fetching barcode from:', url);
                const response = await fetch(url, {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                });
                console.log('Response status:', response.status);
                if (!response.ok) {
                    console.log('Response not OK, returning null');
                    return null;
                }
                const data = await response.json();
                console.log('Fetched product:', data);
                return data;
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
