<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Enterprise Solutions - ARTSCI Security</title>
  <link rel="icon" type="image/webp" href="{{ asset('Artsci Logo REAL 1.webp') }}">
  <link rel="apple-touch-icon" href="{{ asset('Artsci Logo REAL 1.webp') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="{{ asset('modern-design.css') }}">
  <style>
    .solutions-hero {
      margin-top: 60px;
      padding: 60px 24px;
      background: linear-gradient(135deg, var(--primary-blue), #0285C2);
      color: var(--white);
      text-align: center;
    }

    .solutions-hero h1 {
      font-size: clamp(32px, 5vw, 48px);
      font-weight: 900;
      margin-bottom: 12px;
    }

    .solutions-hero p {
      font-size: 16px;
      opacity: 0.95;
      max-width: 600px;
      margin: 0 auto;
    }

    .solutions-container {
      max-width: 1280px;
      margin: 60px auto;
      padding: 0 24px;
    }

    .solutions-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 32px;
      margin-bottom: 60px;
    }

    .product-card {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }

    .product-card:hover {
      transform: translateY(-8px);
      box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
    }

    .product-image {
      width: 100%;
      height: 240px;
      object-fit: cover;
      background: #f0f4f9;
    }

    .product-content {
      padding: 24px;
    }

    .product-category {
      font-size: 12px;
      color: var(--primary-blue);
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 8px;
    }

    .product-title {
      font-size: 20px;
      font-weight: 700;
      color: var(--text-dark);
      margin-bottom: 12px;
    }

    .product-description {
      font-size: 14px;
      color: var(--gray-medium);
      line-height: 1.6;
      margin-bottom: 16px;
    }

    .product-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 16px;
      border-top: 1px solid var(--border-color);
    }

    .product-price {
      font-size: 24px;
      font-weight: 700;
      color: var(--primary-blue);
    }

    .product-barcode {
      font-size: 12px;
      color: var(--gray-medium);
      margin-top: 4px;
    }

    .empty-state {
      text-align: center;
      padding: 60px 24px;
      color: var(--gray-medium);
    }

    .empty-state i {
      font-size: 64px;
      margin-bottom: 24px;
      opacity: 0.5;
    }

    .empty-state h2 {
      font-size: 28px;
      font-weight: 700;
      margin-bottom: 12px;
      color: var(--text-dark);
    }
  </style>
</head>
<body>
  <!-- Navigation -->
  <nav class="navbar">
    <div class="nav-container">
      <div class="nav-logo">
        <img src="{{ asset('Artsci Logo REAL 1.webp') }}" alt="ARTSCI" class="logo-img">
        <span class="brand-name">ARTSCI</span>
      </div>
      <button class="nav-toggle" aria-label="Toggle menu">
        <span></span>
        <span></span>
        <span></span>
      </button>
      <ul class="nav-menu">
        <li><a href="/">Home</a></li>
        <li><a href="/solutions" class="active">Solutions</a></li>
        <li><a href="/#why-artsci">Why ARTSCI</a></li>
        <li><a href="/#partners">Partners</a></li>
        <li><a href="/#contact" class="cta-nav">Get Started</a></li>
      </ul>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="solutions-hero">
    <h1>Complete Enterprise Solutions</h1>
    <p>All products and solutions available from the database, integrated and tested for reliability</p>
  </section>

  <!-- Solutions Content -->
  <div class="solutions-container">
    @if($solutions->count() > 0)
      <div class="solutions-grid">
        @foreach($solutions as $product)
          <div class="product-card">
            @if($product->image)
              <img src="{{ Storage::disk('public')->url($product->image) }}" alt="{{ $product->name }}" class="product-image">
            @else
              <div class="product-image" style="display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-box" style="font-size: 64px; color: var(--gray-light);"></i>
              </div>
            @endif
            <div class="product-content">
              <div class="product-category">{{ $product->solution->name ?? 'Solution' }}</div>
              <h3 class="product-title">{{ $product->name }}</h3>
              <p class="product-description">{{ Str::limit($product->description, 80) }}</p>
              <div class="product-footer">
                <div>
              <div class="product-price">₦{{ number_format($product->price ?? 0, 2) }}</div>
              <div class="product-barcode" style="font-size: 12px; color: #666;">{{ $product->barcode }}</div>
            </div>
            <div style="text-align: right;">
              <div style="font-size: 14px; font-weight: 600; color: var(--primary-blue);">Stock: {{ $product->stock ?? 'N/A' }}</div>
            </div>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @else
      <div class="empty-state">
        <i class="fas fa-inbox"></i>
        <h2>No Products Available</h2>
        <p>Check back soon for available solutions!</p>
      </div>
    @endif
  </div>

  <!-- Footer -->
  <footer class="footer" id="contact">
    <div class="container">
      <div class="footer-top">
        <div class="footer-logo-section">
          <img src="{{ asset('Artsci Logo REAL 1.webp') }}" alt="ARTSCI Logo" class="footer-logo">
          <div>
            <h3>ARTSCI</h3>
            <p>Enterprise Security & Power Solutions Since 2015</p>
          </div>
        </div>
      </div>

      <div class="footer-divider"></div>

      <div class="footer-middle">
        <div class="footer-column">
          <h4>Solutions</h4>
          <a href="/#solutions">CCTV & Surveillance</a>
          <a href="/#solutions">Solar Power Systems</a>
          <a href="/#solutions">Access Control</a>
          <a href="/#solutions">Perimeter Security</a>
          <a href="/#solutions">Smart Automation</a>
          <a href="/#solutions">Full Integration</a>
        </div>

        <div class="footer-column">
          <h4>Company</h4>
          <a href="/#why-artsci">About Us</a>
          <a href="/#services">Our Services</a>
          <a href="/#partners">Our Partners</a>
          <a href="/#contact">Contact Us</a>
          <a href="#">Privacy Policy</a>
          <a href="#">Terms & Conditions</a>
        </div>

        <div class="footer-column">
          <h4>Contact</h4>
          <div class="footer-contact">
            <div class="contact-method">
              <span class="method-icon"><i class="fas fa-phone"></i></span>
              <div>
                <p class="method-label">Phone</p>
                <a href="tel:+2347015862018">0701 586 2018</a>
              </div>
            </div>
            <div class="contact-method">
              <span class="method-icon"><i class="fas fa-envelope"></i></span>
              <div>
                <p class="method-label">Email</p>
                <a href="mailto:info@artsci.ng">info@artsci.ng</a>
              </div>
            </div>
            <div class="contact-method">
              <span class="method-icon"><i class="fab fa-instagram"></i></span>
              <div>
                <p class="method-label">Social</p>
                <a href="https://instagram.com/artsci.ng" target="_blank">@artsci.ng</a>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="footer-divider"></div>

      <div class="footer-bottom">
        <p>&copy; 2025 ARTSCI Security & Power Solutions. All rights reserved.</p>
        <p class="tagline">Fortress-Level Power, Security And Protection </p>
      </div>
    </div>
  </footer>

  <script src="{{ asset('modern-interactions.js') }}"></script>
</body>
</html>
