/* ============================================
   ARTSCI - MODERN INTERACTIONS
   Advanced Effects, Animations, and Dynamics
   ============================================ */

'use strict';

// ============================================
// Hero Image Carousel
// ============================================

const heroSlides = document.querySelectorAll('.hero-slide-bg');
let currentSlide = 0;

if (heroSlides.length > 0) {
  heroSlides.forEach((slide, index) => {
    slide.classList.remove('active', 'prev', 'next');
    if (index === 0) {
      slide.classList.add('active');
    }
  });

  setInterval(() => {
    const nextSlide = (currentSlide + 1) % heroSlides.length;

    heroSlides[currentSlide].classList.remove('active');
    heroSlides[nextSlide].classList.remove('prev', 'next');
    heroSlides[nextSlide].classList.add('active');

    currentSlide = nextSlide;
  }, 4000); // Change slide every 4 seconds
}

// ============================================
// Mobile Navigation Toggle
// ============================================

const navToggle = document.querySelector('.nav-toggle');
const navMenu = document.querySelector('.nav-menu');

if (navToggle) {
  navToggle.addEventListener('click', (e) => {
    e.stopPropagation();
    navMenu.classList.toggle('active');
    navToggle.classList.toggle('active');
  });

  // Close menu when clicking a link
  const navLinks = navMenu.querySelectorAll('a');
  navLinks.forEach(link => {
    link.addEventListener('click', () => {
      navMenu.classList.remove('active');
      navToggle.classList.remove('active');
    });
  });

  // Close menu when clicking outside
  document.addEventListener('click', (e) => {
    if (!e.target.closest('.nav-container') && navMenu.classList.contains('active')) {
      navMenu.classList.remove('active');
      navToggle.classList.remove('active');
    }
  });
}

// ============================================
// Navbar Scroll Effect
// ============================================

const navbar = document.querySelector('.navbar');
let lastScrollY = 0;

window.addEventListener('scroll', () => {
  const scrollY = window.scrollY;

  // Add shadow on scroll
  if (scrollY > 50) {
    navbar.style.boxShadow = '0 4px 16px rgba(10, 20, 40, 0.1)';
  } else {
    navbar.style.boxShadow = 'none';
  }

  lastScrollY = scrollY;
});

// ============================================
// Intersection Observer for Fade-In Animations
// ============================================

const observerOptions = {
  threshold: 0.1,
  rootMargin: '0px 0px -100px 0px'
};

const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.style.opacity = '1';
      entry.target.style.transform = 'translateY(0)';
      observer.unobserve(entry.target);
    }
  });
}, observerOptions);

// Observe elements for animation
const animatedElements = document.querySelectorAll(
  '.solution-card, .installation-card, .why-item, .service-item, .partner-logo, .stat-box'
);

animatedElements.forEach(el => {
  el.style.opacity = '0';
  el.style.transform = 'translateY(20px)';
  el.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
  observer.observe(el);
});

// ============================================
// Smooth Scroll Links
// ============================================

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    const href = this.getAttribute('href');
    if (href === '#') return;

    e.preventDefault();
    const target = document.querySelector(href);

    if (target) {
      target.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    }
  });
});

// ============================================
// Counter Animation for Stats
// ============================================

const animateCounter = (element, target, duration = 2000) => {
  const text = element.textContent;
  const numberMatch = text.match(/\d+/);

  if (!numberMatch) return;

  const start = 0;
  const increment = target / (duration / 16);
  let current = start;

  const timer = setInterval(() => {
    current += increment;

    if (current >= target) {
      element.textContent = text.replace(/\d+/, target);
      clearInterval(timer);
    } else {
      element.textContent = text.replace(/\d+/, Math.floor(current));
    }
  }, 16);
};

// Trigger counter animation when stats section is visible
const statsObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting && !entry.target.classList.contains('animated')) {
      entry.target.classList.add('animated');
      
      const stats = entry.target.querySelectorAll('.stat-number');
      stats.forEach(stat => {
        const number = parseInt(stat.textContent);
        animateCounter(stat, number, 2000);
      });

      statsObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.5 });

const statsBox = document.querySelector('.hero-stats');
if (statsBox) {
  statsObserver.observe(statsBox);
}

// ============================================
// Button Ripple Effect
// ============================================

const buttons = document.querySelectorAll('.btn');

buttons.forEach(button => {
  button.addEventListener('click', function (e) {
    const ripple = document.createElement('span');
    const rect = this.getBoundingClientRect();
    const size = Math.max(rect.width, rect.height);
    const x = e.clientX - rect.left - size / 2;
    const y = e.clientY - rect.top - size / 2;

    ripple.style.width = ripple.style.height = size + 'px';
    ripple.style.left = x + 'px';
    ripple.style.top = y + 'px';
    ripple.classList.add('ripple');

    this.appendChild(ripple);

    setTimeout(() => ripple.remove(), 600);
  });
});

// Add ripple styles dynamically
const style = document.createElement('style');
style.textContent = `
  .btn {
    position: relative;
    overflow: hidden;
  }

  .ripple {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    transform: scale(0);
    animation: ripple-animation 0.6s ease-out;
    pointer-events: none;
  }

  @keyframes ripple-animation {
    to {
      transform: scale(4);
      opacity: 0;
    }
  }
`;
document.head.appendChild(style);

// ============================================
// Parallax Effect for Hero Section
// ============================================

const hero = document.querySelector('.hero');
const securityGrid = document.querySelector('.security-grid');

if (hero && securityGrid) {
  window.addEventListener('scroll', () => {
    const scrollY = window.scrollY;
    const heroHeight = hero.offsetHeight;

    if (scrollY < heroHeight) {
      securityGrid.style.transform = `translateY(${scrollY * 0.5}px)`;
    }
  });
}

// ============================================
// ============================================
// Solution Card Hover Effects
// ============================================

const solutionCards = document.querySelectorAll('.solution-card');
const isMobile = window.matchMedia('(max-width: 768px)').matches;

solutionCards.forEach((card, index) => {
  card.addEventListener('mouseenter', () => {
    // Disable stagger on mobile
    if (isMobile) return;
    
    // Add smooth stagger effect with easing
    solutionCards.forEach((otherCard, otherIndex) => {
      if (otherIndex !== index) {
        const delay = Math.abs(otherIndex - index) * 30;
        setTimeout(() => {
          otherCard.style.opacity = '0.5';
          otherCard.style.transform = 'scale(0.92) translateY(8px)';
          otherCard.style.filter = 'blur(0.5px)';
        }, delay);
      }
    });
  });

  card.addEventListener('mouseleave', () => {
    if (isMobile) return;
    
    solutionCards.forEach((otherCard) => {
      otherCard.style.opacity = '1';
      otherCard.style.transform = 'scale(1) translateY(0)';
      otherCard.style.filter = 'blur(0)';
    });
  });

  // Add tilt effect on mouse move (desktop only)
  if (!isMobile) {
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      
      const centerX = rect.width / 2;
      const centerY = rect.height / 2;
      
      const rotateX = (y - centerY) * 0.02;
      const rotateY = (centerX - x) * 0.02;
      
      card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateZ(20px)`;
    });

    card.addEventListener('mouseleave', () => {
      card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateZ(0)';
    });
  }
});

// ============================================
// Active Link Highlighting
// ============================================

const navLinks = document.querySelectorAll('.nav-menu a:not(.cta-nav)');
const sections = document.querySelectorAll('section');

window.addEventListener('scroll', () => {
  let current = '';

  sections.forEach(section => {
    const sectionTop = section.offsetTop;
    const sectionHeight = section.clientHeight;

    if (scrollY >= sectionTop - 200) {
      current = section.getAttribute('id');
    }
  });

  navLinks.forEach(link => {
    link.classList.remove('active');
    if (link.getAttribute('href').slice(1) === current) {
      link.classList.add('active');
    }
  });
});

// ============================================
// Form Enhancement (for contact methods)
// ============================================

const contactLinks = document.querySelectorAll('.contact-method a');

contactLinks.forEach(link => {
  link.addEventListener('click', function(e) {
    // Add visual feedback
    this.style.transition = 'all 0.3s ease';
    this.style.transform = 'scale(0.95)';
    
    setTimeout(() => {
      this.style.transform = 'scale(1)';
    }, 150);
  });
});

// ============================================
// Page Load Animation
// ============================================

window.addEventListener('load', () => {
  document.body.style.opacity = '0';
  
  setTimeout(() => {
    document.body.style.transition = 'opacity 0.6s ease-out';
    document.body.style.opacity = '1';
  }, 100);
});

// ============================================
// Utility: Add active state to nav links via CSS
// ============================================

const style2 = document.createElement('style');
style2.textContent = `
  .nav-menu a.active {
    color: var(--primary-blue);
    font-weight: 700;
  }

  .nav-menu a.active::after {
    width: 100%;
  }
`;
document.head.appendChild(style2);

// ============================================
// Mouse Gradient Effect on Cards (Premium Touch)
// ============================================

const cards = document.querySelectorAll('.solution-card, .installation-card, .service-item, .why-item');

cards.forEach(card => {
  card.addEventListener('mousemove', (e) => {
    const rect = card.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;

    // Create subtle gradient based on mouse position
    const gradX = (x / rect.width) * 100;
    const gradY = (y / rect.height) * 100;

    card.style.backgroundPosition = `${gradX}% ${gradY}%`;
  });

  card.addEventListener('mouseleave', () => {
    card.style.backgroundPosition = '50% 50%';
  });
});

// ============================================
// Lazy Load Images
// ============================================

if ('IntersectionObserver' in window) {
  const images = document.querySelectorAll('img');

  const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target;
        img.src = img.dataset.src || img.src;
        img.classList.add('loaded');
        observer.unobserve(img);
      }
    });
  });

  images.forEach(img => {
    if (img.dataset.src) {
      imageObserver.observe(img);
    }
  });
}

// ============================================
// Installations Lightbox
// ============================================

const installationCards = Array.from(document.querySelectorAll('.installation-card'));
const installationItems = installationCards
  .map((card) => {
    const image = card.querySelector('.installation-media img');
    const title = card.querySelector('h3');
    const summary = card.querySelector('p');
    const galleryJson = image ? image.dataset.gallery : '[]';
    let gallery = [];

    try {
      gallery = JSON.parse(galleryJson || '[]');
      if (!Array.isArray(gallery)) {
        gallery = [];
      }
    } catch (_error) {
      gallery = [];
    }

    if (!image) return null;
    return {
      src: image.currentSrc || image.src,
      gallery: gallery.length > 0 ? gallery : [image.currentSrc || image.src],
      alt: image.alt || '',
      title: title ? title.textContent.trim() : 'Installation',
      summary: summary ? summary.textContent.trim() : ''
    };
  })
  .filter(Boolean);

if (installationItems.length > 0) {
  const lightbox = document.createElement('div');
  lightbox.className = 'installation-lightbox';
  lightbox.innerHTML = `
    <div class="lightbox-inner" role="dialog" aria-modal="true" aria-label="Installation gallery">
      <div class="lightbox-top">
        <button class="lightbox-close" type="button" aria-label="Close gallery">&times;</button>
      </div>
      <div class="lightbox-stage">
        <button class="lightbox-nav prev" type="button" aria-label="Previous image">&#8249;</button>
        <img class="lightbox-image" alt="" />
        <button class="lightbox-nav next" type="button" aria-label="Next image">&#8250;</button>
      </div>
      <div class="lightbox-caption">
        <strong></strong>
        <span></span>
      </div>
    </div>
  `;

  document.body.appendChild(lightbox);

  const lightboxImage = lightbox.querySelector('.lightbox-image');
  const lightboxTitle = lightbox.querySelector('.lightbox-caption strong');
  const lightboxSummary = lightbox.querySelector('.lightbox-caption span');
  const closeBtn = lightbox.querySelector('.lightbox-close');
  const prevBtn = lightbox.querySelector('.lightbox-nav.prev');
  const nextBtn = lightbox.querySelector('.lightbox-nav.next');
  const stage = lightbox.querySelector('.lightbox-stage');
  let activeImageIndex = 0;
  let activeGallery = [];
  let touchStartX = 0;

  const renderImage = (imageIndex) => {
    if (activeGallery.length === 0) return;
    const safeImageIndex = (imageIndex + activeGallery.length) % activeGallery.length;
    activeImageIndex = safeImageIndex;
    lightboxImage.src = activeGallery[safeImageIndex];
  };

  const renderInstallation = (cardIndex, imageIndex = 0) => {
    const safeCardIndex = (cardIndex + installationItems.length) % installationItems.length;
    const current = installationItems[safeCardIndex];
    activeGallery = current.gallery;
    lightboxImage.alt = current.alt;
    lightboxTitle.textContent = current.title;
    lightboxSummary.textContent = current.summary;
    renderImage(imageIndex);
  };

  const openLightbox = (index, imageIndex = 0) => {
    renderInstallation(index, imageIndex);
    lightbox.classList.add('is-open');
    document.body.classList.add('installation-lightbox-open');
  };

  const closeLightbox = () => {
    lightbox.classList.remove('is-open');
    document.body.classList.remove('installation-lightbox-open');
  };

  installationCards.forEach((card, index) => {
    const image = card.querySelector('.installation-media img');
    const trigger = card.querySelector('[data-open-installation]');

    if (image) {
      image.addEventListener('click', () => openLightbox(index));
    }

    if (trigger) {
      trigger.addEventListener('click', () => openLightbox(index));
    }
  });

  closeBtn.addEventListener('click', closeLightbox);
  prevBtn.addEventListener('click', () => renderImage(activeImageIndex - 1));
  nextBtn.addEventListener('click', () => renderImage(activeImageIndex + 1));

  lightbox.addEventListener('click', (e) => {
    if (e.target === lightbox) {
      closeLightbox();
    }
  });

  window.addEventListener('keydown', (e) => {
    if (!lightbox.classList.contains('is-open')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') renderImage(activeImageIndex - 1);
    if (e.key === 'ArrowRight') renderImage(activeImageIndex + 1);
  });

  stage.addEventListener('touchstart', (e) => {
    touchStartX = e.changedTouches[0].clientX;
  }, { passive: true });

  stage.addEventListener('touchend', (e) => {
    const touchEndX = e.changedTouches[0].clientX;
    const deltaX = touchEndX - touchStartX;

    if (Math.abs(deltaX) < 40) return;
    if (deltaX > 0) {
      renderImage(activeImageIndex - 1);
    } else {
      renderImage(activeImageIndex + 1);
    }
  }, { passive: true });
}

// ============================================
// Performance Monitoring (Dev Tools)
// ============================================

// Debug: Track any reload/refresh attempts
window.addEventListener('beforeunload', () => {
  console.warn('⚠️ Page is about to reload/refresh');
  console.trace();
});

if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
  console.log('%cARTSCI Security Platform', 'font-size: 20px; font-weight: bold; color: #03A9F4;');
  console.log('%cPremium Enterprise Design', 'font-size: 14px; color: #FFEB3B;');
  console.log('%cModern, Responsive, Security-Focused', 'font-size: 12px; color: #8A95A8;');
}

// ============================================
// Export Module (for future enhancements)
// ============================================

window.ARTSCI = {
  version: '1.0.0',
  animateCounter,
  observer,
  navToggle
};
