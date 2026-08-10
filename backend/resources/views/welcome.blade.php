<!DOCTYPE html>
<html lang="en">
<head>
  @php
    $canonicalUrl = url('/');
    $seoTitle = 'ARTSCI Security & Power Solutions in Nigeria | CCTV, Solar, Access Control';
    $seoDescription = 'ARTSCI delivers CCTV surveillance, solar power systems, access control, perimeter security, and smart automation for homes, estates, offices, schools, and enterprise sites across Nigeria.';
    $seoImage = asset('Desktop-012.webp');
    $seoSchema = [
      '@context' => 'https://schema.org',
      '@graph' => [
        [
          '@type' => 'Organization',
          '@id' => $canonicalUrl . '#organization',
          'name' => 'ARTSCI Security & Power Solutions',
          'url' => $canonicalUrl,
          'logo' => asset('logo.png'),
          'image' => $seoImage,
          'description' => $seoDescription,
          'telephone' => '+2349160450776',
          'email' => 'support@artsci.com.ng',
          'sameAs' => [
            'https://instagram.com/artsci_official',
          ],
          'areaServed' => 'Nigeria',
        ],
        [
          '@type' => 'WebSite',
          '@id' => $canonicalUrl . '#website',
          'url' => $canonicalUrl,
          'name' => 'ARTSCI Security & Power Solutions',
          'publisher' => [
            '@id' => $canonicalUrl . '#organization',
          ],
          'inLanguage' => 'en-NG',
        ],
        [
          '@type' => 'LocalBusiness',
          '@id' => $canonicalUrl . '#localbusiness',
          'name' => 'ARTSCI Security & Power Solutions',
          'url' => $canonicalUrl,
          'image' => $seoImage,
          'telephone' => '+2349160450776',
          'email' => 'support@artsci.com.ng',
          'priceRange' => '$$',
          'areaServed' => 'Nigeria',
          'serviceType' => [
            'CCTV installation',
            'Solar power installation',
            'Access control systems',
            'Electric fence installation',
            'Smart automation systems',
          ],
        ],
      ],
    ];
  @endphp
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ $seoTitle }}</title>
  <meta name="description" content="{{ $seoDescription }}" />
  <meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1" />
  <link rel="canonical" href="{{ $canonicalUrl }}" />
  <meta property="og:locale" content="en_NG" />
  <meta property="og:type" content="website" />
  <meta property="og:title" content="{{ $seoTitle }}" />
  <meta property="og:description" content="{{ $seoDescription }}" />
  <meta property="og:url" content="{{ $canonicalUrl }}" />
  <meta property="og:site_name" content="ARTSCI Security & Power Solutions" />
  <meta property="og:image" content="{{ $seoImage }}" />
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="{{ $seoTitle }}" />
  <meta name="twitter:description" content="{{ $seoDescription }}" />
  <meta name="twitter:image" content="{{ $seoImage }}" />
  <link rel="icon" type="image/png" href="{{ asset('logo.png') }}" />
  <link rel="apple-touch-icon" href="{{ asset('logo.png') }}" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="{{ asset('modern-design.css') }}" />
  <script type="application/ld+json">{!! json_encode($seoSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
</head>
<body>
  <!-- Premium Navigation -->
  <nav class="navbar">
    <div class="nav-container">
      <div class="nav-logo">
        <img src="{{ asset('Artsci Logo REAL 1.webp') }}" alt="ARTSCI" class="logo-img">
        <span class="brand-name"></span>
      </div>
      <button class="nav-toggle" aria-label="Toggle menu">
        <span></span>
        <span></span>
        <span></span>
      </button>
      <ul class="nav-menu">
        <li><a href="#solutions">Solutions</a></li>
        <li><a href="#services">Services</a></li>
        <li><a href="#installations">Installations</a></li>
        <li><a href="#why-artsci">Why ARTSCI</a></li>
        <li><a href="#partners">Partners</a></li>
        <li><a href="#contact" class="cta-nav">Get Started</a></li>
      </ul>
    </div>
  </nav>

  <!-- Hero Section - Security Focus -->
  <section class="hero">
    <div class="hero-media">
      <img class="hero-slide-bg active" src="{{ asset('Desktop-012.webp') }}" alt="High-tech security operations center" fetchpriority="high" />
      <img class="hero-slide-bg" src="{{ asset('Desktop-013.webp') }}" alt="Central command station display wall" />
      <img class="hero-slide-bg" src="{{ asset('Desktop-014.webp') }}" alt="Enterprise surveillance workspace" />
      <img class="hero-slide-bg" src="{{ asset('Desktop-015.webp') }}" alt="Security network monitoring dashboard" />
    </div>
    <div class="hero-scrim"></div>
  </section>

  <!-- Solutions Section -->
  <section class="solutions" id="solutions">
    <div class="container">
      <div class="section-header">
        <h2>Enterprise Solutions</h2>
        <p>Integrated security and power systems for complete infrastructure protection</p>
      </div>
      <div class="solutions-grid">
        <a href="{{ route('solutions.index') }}#cctv" class="solution-card solution-cctv">
          <span class="solution-bg" aria-hidden="true"></span>
          <div class="solution-content">
            <img class="solution-thumb" src="{{ asset('images/survelliance.webp') }}" alt="CCTV and surveillance systems">
            <h3>SURVEILLANCE</h3>
            <p>HD/4K CCTV systems with AI detection, cloud archival, and 24/7 remote monitoring</p>
            <ul>
              <li>4K IP cameras with AI detection</li>
              <li>Cloud + local storage</li>
              <li>Mobile app monitoring</li>
              <li>Smart alerts & analytics</li>
            </ul>
          </div>
        </a>

        <a href="{{ route('solutions.index') }}#solar" class="solution-card solution-power">
          <span class="solution-bg" aria-hidden="true"></span>
          <div class="solution-content">
            <img class="solution-thumb" src="{{ asset('images/solar power.jpg') }}" alt="Solar power systems">
            <h3>SOLAR POWER</h3>
            <p>Hybrid inverters with lithium batteries sized for enterprise loads</p>
            <ul>
              <li>5-10kVA hybrid systems</li>
              <li>Lithium battery storage</li>
              <li>Solar panel integration</li>
              <li>Energy monitoring</li>
            </ul>
          </div>
        </a>

        <a href="{{ route('solutions.index') }}#access" class="solution-card solution-access">
          <span class="solution-bg" aria-hidden="true"></span>
          <div class="solution-content">
            <img class="solution-thumb" src="{{ asset('images/access-control.webp') }}" alt="Access control solutions">
            <h3>ACCESS CONTROL</h3>
            <p>Smart gates, biometric locks, and visitor management systems</p>
            <ul>
              <li>Automatic gate systems</li>
              <li>Biometric access control</li>
              <li>RFID integration</li>
              <li>Audit trails & reports</li>
            </ul>
          </div>
        </a>

        <a href="{{ route('solutions.index') }}#perimeter" class="solution-card solution-fence">
          <span class="solution-bg" aria-hidden="true"></span>
          <div class="solution-content">
            <img class="solution-thumb" src="{{ asset('images/Perimeter Security.jpg') }}" alt="Perimeter security and electric fencing">
            <h3>PERIMETER SECURITY</h3>
            <p>High-voltage electric fencing with integrated alarms and monitoring</p>
            <ul>
              <li>Multi-zone electric fence</li>
              <li>Alarm integration</li>
              <li>Battery backup 24/7</li>
              <li>Compliance certified</li>
            </ul>
          </div>
        </a>

        <a href="{{ route('solutions.index') }}#automation" class="solution-card solution-smart">
          <span class="solution-bg" aria-hidden="true"></span>
          <div class="solution-content">
            <img class="solution-thumb" src="{{ asset('images/Smart-Automation.png') }}" alt="Smart automation systems">
            <h3>SMART AUTOMATION</h3>
            <p>Unified control for lighting, climate, security, and energy management</p>
            <ul>
              <li>Central dashboard control</li>
              <li>Scheduling & automations</li>
              <li>Mobile app integration</li>
              <li>Voice control compatible</li>
            </ul>
          </div>
        </a>

        <a href="{{ route('solutions.index') }}#integration" class="solution-card solution-integration">
          <span class="solution-bg" aria-hidden="true"></span>
          <div class="solution-content">
            <img class="solution-thumb" src="{{ asset('images/smart integration.jpg') }}" alt="System integration and unified dashboards">
            <h3>FULL INTEGRATION</h3>
            <p>Complete enterprise stack with unified monitoring and control</p>
            <ul>
              <li>All systems integrated</li>
              <li>Single dashboard</li>
              <li>API access available</li>
              <li>White-label options</li>
            </ul>
          </div>
        </a>
      </div>
    </div>
  </section>

  <!-- Why ARTSCI Section -->
  <section class="why-artsci" id="why-artsci">
    <div class="container">
      <div class="section-header">
        <h2>Why Choose ARTSCI</h2>
        <p>The only security partner you need for complete infrastructure protection</p>
      </div>
      <div class="why-grid">
        <div class="why-item">
          <div class="why-number">01</div>
          <h4>Expert Engineering</h4>
          <p>In-house engineers design systems for YOUR specific needs, not generic solutions. Every installation is optimized for maximum uptime and performance.</p>
        </div>
        <div class="why-item">
          <div class="why-number">02</div>
          <h4>Professional Installation</h4>
          <p>Certified technicians handle all installations with zero disruption to your operations. Clean cabling, proper grounding, and full documentation included.</p>
        </div>
        <div class="why-item">
          <div class="why-number">03</div>
          <h4>24/7 Monitoring</h4>
          <p>Round-the-clock remote monitoring with instant alerts. Our team responds to issues before they become problems, ensuring uninterrupted security.</p>
        </div>
        <div class="why-item">
          <div class="why-number">04</div>
          <h4>Preventive Support</h4>
          <p>Regular maintenance visits, firmware updates, and system health checks. We keep your infrastructure running at peak performance year-round.</p>
        </div>
        <div class="why-item">
          <div class="why-number">05</div>
          <h4>Certified Partnerships</h4>
          <p>Authorized distributor for DAHUA, HIKVISION, AMCREST, FELICITY, and CENTURION. Access to enterprise-grade hardware with full warranty support.</p>
        </div>
        <div class="why-item">
          <div class="why-number">06</div>
          <h4>Proven Track Record</h4>
          <p>Since 2015, serving 500+ clients including banks, oil companies, schools, and government agencies. 99.8% uptime guarantee backed by our reputation.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Services Section -->
  <section class="services" id="services">
    <div class="container">
      <div class="section-header">
        <h2>Professional Services</h2>
        <p>Complete lifecycle support from assessment to 24/7 monitoring</p>
      </div>
      <div class="services-grid">
        <div class="service-item">
          <div class="service-step">1</div>
          <h4>Site Assessment</h4>
          <p>Professional walkthrough with risk analysis, load calculation, and custom recommendations</p>
        </div>
        <div class="service-item">
          <div class="service-step">2</div>
          <h4>System Design</h4>
          <p>Custom engineering tailored to your exact requirements, budget, and infrastructure</p>
        </div>
        <div class="service-item">
          <div class="service-step">3</div>
          <h4>Professional Install</h4>
          <p>Certified technicians with minimal downtime, complete documentation, and staff training</p>
        </div>
        <div class="service-item">
          <div class="service-step">4</div>
          <h4>Testing & Handover</h4>
          <p>Full system testing, compliance verification, and knowledge transfer to your team</p>
        </div>
        <div class="service-item">
          <div class="service-step">5</div>
          <h4>24/7 Monitoring</h4>
          <p>Continuous remote monitoring with instant alerts and incident response</p>
        </div>
        <div class="service-item">
          <div class="service-step">6</div>
          <h4>Preventive Maintenance</h4>
          <p>Regular health checks, updates, and optimization for maximum system longevity</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Installations Gallery -->
  <section class="installations" id="installations">
    <div class="container">
      <div class="section-header">
        <h2>Recent Installations</h2>
        <p>Real projects delivered by ARTSCI teams across homes, estates, and enterprise facilities</p>
      </div>
      @php
        $fallbackInstallations = collect([
          [
            'category' => 'CCTV',
            'city' => 'Lagos',
            'title' => 'Commercial CCTV Upgrade',
            'summary' => '32-channel HD surveillance rollout with remote monitoring enabled for round-the-clock visibility.',
            'outcome' => 'Faster incident response and full perimeter coverage.',
            'client_type' => 'Business',
            'completed_label' => 'Completed Nov 2025',
            'completed_at' => '2025-11',
            'image' => asset('images/survelliance.webp'),
            'gallery_images' => [],
          ],
          [
            'category' => 'Solar',
            'city' => 'Abuja',
            'title' => 'Hybrid Solar + Battery Deployment',
            'summary' => 'Enterprise-grade hybrid inverter and battery bank integration for mission-critical uptime.',
            'outcome' => 'Reduced generator runtime and improved power resilience.',
            'client_type' => 'Warehouse',
            'completed_label' => 'Completed Oct 2025',
            'completed_at' => '2025-10',
            'image' => asset('images/solar power.jpg'),
            'gallery_images' => [],
          ],
          [
            'category' => 'Access Control',
            'city' => 'Port Harcourt',
            'title' => 'Biometric Access Deployment',
            'summary' => 'Biometric readers, gate automation, and visitor access rules for controlled site entry.',
            'outcome' => 'Stronger entry security with auditable access logs.',
            'client_type' => 'Business',
            'completed_label' => 'Completed Sep 2025',
            'completed_at' => '2025-09',
            'image' => asset('images/access-control.webp'),
            'gallery_images' => [],
          ],
          [
            'category' => 'Perimeter',
            'city' => 'Ibadan',
            'title' => 'Perimeter Electric Fence System',
            'summary' => 'Multi-zone electric fence connected to smart alerts and centralized alarm monitoring.',
            'outcome' => 'Immediate breach detection with rapid onsite action.',
            'client_type' => 'Home Estate',
            'completed_label' => 'Completed Aug 2025',
            'completed_at' => '2025-08',
            'image' => asset('images/Perimeter Security.jpg'),
            'gallery_images' => [],
          ],
        ]);

        $installationCards = isset($installations) && $installations->count() > 0
          ? $installations->map(function ($item) {
              $gallery = is_array($item->gallery_images) ? $item->gallery_images : [];
              $cover = $item->cover_image ?: ($gallery[0] ?? null);
              return [
                'category' => $item->category,
                'city' => $item->city,
                'title' => $item->title,
                'summary' => $item->summary,
                'outcome' => $item->outcome,
                'client_type' => $item->client_type ?: 'Business',
                'completed_label' => $item->completed_at ? 'Completed ' . $item->completed_at->format('M Y') : 'Completed recently',
                'completed_at' => $item->completed_at ? $item->completed_at->format('Y-m') : null,
                'image' => \App\Support\ImageUrl::url($cover) ?: asset('images/survelliance.webp'),
                'gallery_images' => array_values(array_filter(array_map(fn ($img) => \App\Support\ImageUrl::url($img), $gallery))),
              ];
            })
          : $fallbackInstallations;
      @endphp
      <div class="installation-grid">
        @foreach($installationCards as $card)
          <article class="installation-card">
            <div class="installation-media">
              <img
                src="{{ $card['image'] }}"
                alt="{{ $card['title'] }}"
                loading="lazy"
                data-gallery='@json(array_values(array_unique(array_filter(array_merge([$card["image"]], $card["gallery_images"] ?? [])))))'
              >
              <span class="installation-badge">Verified Installation</span>
            </div>
            <div class="installation-content">
              <div class="installation-meta">
                <span class="meta-pill">{{ $card['category'] }}</span>
                <span class="meta-pill muted">{{ $card['city'] }}</span>
              </div>
              <h3>{{ $card['title'] }}</h3>
              <p>{{ $card['summary'] }}</p>
              <div class="installation-details">
                <span class="detail-chip">{{ $card['client_type'] }}</span>
                @if(!empty($card['completed_at']))
                  <time datetime="{{ $card['completed_at'] }}">{{ $card['completed_label'] }}</time>
                @else
                  <span>{{ $card['completed_label'] }}</span>
                @endif
              </div>
              @if(!empty($card['outcome']))
                <span class="installation-outcome">Outcome: {{ $card['outcome'] }}</span>
              @endif
              <button type="button" class="installation-link" data-open-installation>View installation photo</button>
            </div>
          </article>
        @endforeach
      </div>

      <div class="installation-cta">
        <a href="#contact" class="btn btn-primary">Get a Similar Setup</a>
      </div>
    </div>
  </section>

  <div class="installation-lightbox" aria-hidden="true">
    <div class="lightbox-inner">
      <div class="lightbox-top">
        <button type="button" class="lightbox-close" aria-label="Close installation gallery">×</button>
      </div>
      <div class="lightbox-stage">
        <button type="button" class="lightbox-nav prev" aria-label="Previous image">‹</button>
        <img class="lightbox-image" src="" alt="Installation photo" />
        <button type="button" class="lightbox-nav next" aria-label="Next image">›</button>
      </div>
      <div class="lightbox-caption">
        <strong class="lightbox-title">Installation photo</strong>
        <span class="lightbox-count"></span>
      </div>
    </div>
  </div>

  <!-- Clients & Partners Section -->
  <section class="partners-section" id="partners">
    <div class="container">
      <div class="section-header">
        <h2>Trusted By Industry Leaders</h2>
        <p>We serve banks, oil & gas, schools, government, and estates across Nigeria</p>
      </div>
      <div class="clients-showcase">
        <div class="client-type">
          <span class="client-badge">Banking</span>
        </div>
        <div class="client-type">
          <span class="client-badge">Oil & Gas</span>
        </div>
        <div class="client-type">
          <span class="client-badge">Education</span>
        </div>
        <div class="client-type">
          <span class="client-badge">Government</span>
        </div>
        <div class="client-type">
          <span class="client-badge">Real Estate</span>
        </div>
        <div class="client-type">
          <span class="client-badge">Enterprise</span>
        </div>
      </div>

      <div class="section-header" style="margin-top: 60px;">
        <h2>Hardware Partners</h2>
        <p>Enterprise-grade brands trusted worldwide</p>
      </div>
      <div class="partners-grid">
        <div class="partner-logo">
          <img src="{{ asset('dahua png.png') }}" alt="DAHUA">
        </div>
        <div class="partner-logo">
          <img src="{{ asset('hikvision png2.png') }}" alt="HIKVISION">
        </div>
        <div class="partner-logo">
          <img src="{{ asset('amcrest 2.png') }}" alt="AMCREST">
        </div>
        <div class="partner-logo">
          <img src="{{ asset('centurion.png') }}" alt="CENTURION">
        </div>
        <div class="partner-logo">
          <img src="{{ asset('felicity logo PNG2.png') }}" alt="FELICITY">
        </div>
        <div class="partner-logo">
          <img src="{{ asset('deye 1.png') }}" alt="DEYE">
        </div>
        <div class="partner-logo">
          <img src="{{ asset('growatt 1.png') }}" alt="GROWATT">
        </div>
        <div class="partner-logo">
          <img src="{{ asset('nemtek 1.png') }}" alt="NEMTEK">
        </div>
      </div>
    </div>
  </section>

  <!-- Contact & Footer -->
  <footer class="footer" id="contact">
    <div class="container">
      <div class="footer-top">
        <div class="footer-logo-section">
          <img src="{{ asset('Artsci Logo REAL 1.webp') }}" alt="ARTSCI Logo" class="footer-logo">
          <div>
            
            <p>Enterprise Security & Power Solutions Since 2015</p>
          </div>
        </div>
      </div>

      <div class="footer-divider"></div>

      <div class="footer-middle">
        <div class="footer-column">
          <h4>Solutions</h4>
          <a href="{{ route('solutions.index') }}#cctv">CCTV & Surveillance</a>
          <a href="{{ route('solutions.index') }}#solar">Solar Power Systems</a>
          <a href="{{ route('solutions.index') }}#access">Access Control</a>
          <a href="{{ route('solutions.index') }}#perimeter">Perimeter Security</a>
          <a href="{{ route('solutions.index') }}#automation">Smart Automation</a>
          <a href="{{ route('solutions.index') }}#integration">Full Integration</a>
        </div>

        <div class="footer-column">
          <h4>Company</h4>
          <a href="#why-artsci">About Us</a>
          <a href="#services">Our Services</a>
          <a href="#partners">Our Partners</a>
          <a href="#contact">Contact Us</a>
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
                <a href="tel:+2349160450776">09160450776</a>
              </div>
            </div>
            <div class="contact-method">
              <span class="method-icon"><i class="fas fa-envelope"></i></span>
              <div>
                <p class="method-label">Email</p>
                <a href="mailto:support@artsci.com.ng">support@artsci.com.ng</a>
              </div>
            </div>
            <div class="contact-method">
              <span class="method-icon"><i class="fab fa-instagram"></i></span>
              <div>
                <p class="method-label">Social</p>
                <a href="https://instagram.com/artsci_official" target="_blank">@artsci_official</a>
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

  <!-- Sticky WhatsApp Button -->
  <a href="https://wa.me/2349160450776" target="_blank" class="whatsapp-sticky" title="Chat with us on WhatsApp">
    <i class="fab fa-whatsapp"></i>
  </a>

  <script src="{{ asset('modern-interactions.js') }}"></script>
</body>
</html>
