<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ARTSCI | Enterprise Security & Power Solutions</title>
  <link rel="icon" type="image/webp" href="{{ asset('Artsci Logo REAL 1.webp') }}" />
  <link rel="apple-touch-icon" href="{{ asset('Artsci Logo REAL 1.webp') }}" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="{{ asset('modern-design.css') }}" />
</head>
<body>
  <!-- Premium Navigation -->
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
        <li><a href="#solutions">Solutions</a></li>
        <li><a href="#services">Services</a></li>
        <li><a href="#why-artsci">Why ARTSCI</a></li>
        <li><a href="#partners">Partners</a></li>
        <li><a href="#contact" class="cta-nav">Get Started</a></li>
      </ul>
    </div>
  </nav>

  <!-- Hero Section - Security Focus -->
  <section class="hero">
    <div class="hero-media">
      <img class="hero-slide-bg active" src="{{ asset('Desktop-012.webp') }}" alt="High-tech security operations center" />
      <img class="hero-slide-bg" src="{{ asset('Desktop-013.webp') }}" alt="Central command station display wall" />
      <img class="hero-slide-bg" src="{{ asset('Desktop-014.webp') }}" alt="Enterprise surveillance workspace" />
      <img class="hero-slide-bg" src="{{ asset('Desktop-015.webp') }}" alt="Security network monitoring dashboard" />
    </div>
    <div class="hero-content">
      <div class="hero-text">
        <h1 class="hero-title">Enterprise Security & Power Solutions</h1>
        <p class="hero-subtitle">Advanced surveillance, access control, and energy management systems for critical infrastructure</p>
        <button class="hero-cta" onclick="document.getElementById('contact').scrollIntoView({behavior: 'smooth'})">Schedule Demo</button>
      </div>
      <div class="hero-dots">
        <span class="dot active" onclick="changeSlide(0)"></span>
        <span class="dot" onclick="changeSlide(1)"></span>
        <span class="dot" onclick="changeSlide(2)"></span>
        <span class="dot" onclick="changeSlide(3)"></span>
      </div>
    </div>
  </section>

  <!-- Solutions Section -->
  <section class="solutions" id="solutions">
    <div class="container">
      <div class="section-header">
        <h2>Comprehensive Security Solutions</h2>
        <p>Integrated systems designed for maximum protection and efficiency</p>
      </div>
      <div class="solutions-grid">
        <div class="solution-card">
          <div class="solution-icon">
            <i class="fas fa-video"></i>
          </div>
          <h3>Advanced CCTV Systems</h3>
          <p>High-definition surveillance with AI-powered analytics for real-time threat detection and automated response</p>
        </div>
        <div class="solution-card">
          <div class="solution-icon">
            <i class="fas fa-lock"></i>
          </div>
          <h3>Access Control Solutions</h3>
          <p>Biometric and card-based access systems with integration to security protocols for facilities management</p>
        </div>
        <div class="solution-card">
          <div class="solution-icon">
            <i class="fas fa-power-off"></i>
          </div>
          <h3>Power Management</h3>
          <p>UPS systems, solar integration, and backup power solutions for uninterrupted security operations</p>
        </div>
        <div class="solution-card">
          <div class="solution-icon">
            <i class="fas fa-network-wired"></i>
          </div>
          <h3>Network Infrastructure</h3>
          <p>Secure fiber-optic networks and IoT connectivity for seamless system integration and monitoring</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Services Section -->
  <section class="services" id="services">
    <div class="container">
      <div class="section-header">
        <h2>Professional Services</h2>
        <p>Expert support at every stage of your security journey</p>
      </div>
      <div class="services-grid">
        <div class="service-item">
          <h3>System Design</h3>
          <p>Custom architecture for your facility's unique security needs</p>
        </div>
        <div class="service-item">
          <h3>Installation</h3>
          <p>Professional deployment with minimal operational disruption</p>
        </div>
        <div class="service-item">
          <h3>Maintenance</h3>
          <p>24/7 monitoring and preventive maintenance programs</p>
        </div>
        <div class="service-item">
          <h3>Training</h3>
          <p>Comprehensive staff training on all system operations</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Why ARTSCI Section -->
  <section class="why-artsci" id="why-artsci">
    <div class="container">
      <div class="section-header">
        <h2>Why Choose ARTSCI</h2>
        <p>Industry-leading expertise and innovation</p>
      </div>
      <div class="features-grid">
        <div class="feature">
          <h4>24/7 Support</h4>
          <p>Round-the-clock monitoring and technical support</p>
        </div>
        <div class="feature">
          <h4>AI Integration</h4>
          <p>Advanced analytics for predictive threat detection</p>
        </div>
        <div class="feature">
          <h4>Scalability</h4>
          <p>Systems that grow with your organization</p>
        </div>
        <div class="feature">
          <h4>Compliance</h4>
          <p>Full adherence to international security standards</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Contact Section -->
  <section class="contact" id="contact">
    <div class="container">
      <div class="section-header">
        <h2>Get Started Today</h2>
        <p>Let's secure your enterprise</p>
      </div>
      <form class="contact-form" onsubmit="handleContactSubmit(event)">
        <input type="text" placeholder="Your Name" required>
        <input type="email" placeholder="Your Email" required>
        <input type="tel" placeholder="Your Phone" required>
        <textarea placeholder="Tell us about your security needs..." rows="5" required></textarea>
        <button type="submit">Request Demo</button>
      </form>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <p>&copy; 2024 ARTSCI. All rights reserved. Enterprise Security Solutions.</p>
      <div class="footer-links">
        <a href="#solutions">Solutions</a>
        <a href="#services">Services</a>
        <a href="#contact">Contact</a>
      </div>
    </div>
  </footer>

  <script src="{{ asset('modern-interactions.js') }}"></script>
  <script>
    let currentSlide = 0;
    const slides = document.querySelectorAll('.hero-slide-bg');
    const dots = document.querySelectorAll('.dot');

    function changeSlide(index) {
      slides.forEach(slide => slide.classList.remove('active'));
      dots.forEach(dot => dot.classList.remove('active'));
      slides[index].classList.add('active');
      dots[index].classList.add('active');
      currentSlide = index;
    }

    setInterval(() => {
      currentSlide = (currentSlide + 1) % slides.length;
      changeSlide(currentSlide);
    }, 5000);

    function handleContactSubmit(event) {
      event.preventDefault();
      alert('Thank you for your interest. We will contact you soon!');
      event.target.reset();
    }
  </script>
</body>
</html>
