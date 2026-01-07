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
    } else {
      slide.classList.add('next');
    }
  });

  setInterval(() => {
    const nextSlide = (currentSlide + 1) % heroSlides.length;

    heroSlides[currentSlide].classList.remove('active');
    heroSlides[currentSlide].classList.add('prev');

    heroSlides[nextSlide].classList.remove('prev', 'next');
    heroSlides[nextSlide].classList.add('active');

    heroSlides.forEach((slide, index) => {
      if (index !== nextSlide && index !== currentSlide) {
        slide.classList.remove('active', 'prev');
        slide.classList.add('next');
      }
    });

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
  '.solution-card, .why-item, .service-item, .partner-logo, .stat-box'
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
// Solution Card Hover Effects
// ============================================

const solutionCards = document.querySelectorAll('.solution-card');

solutionCards.forEach((card, index) => {
  card.addEventListener('mouseenter', () => {
    // Add stagger effect
    solutionCards.forEach((otherCard, otherIndex) => {
      if (otherIndex !== index) {
        otherCard.style.opacity = '0.6';
        otherCard.style.transform = 'scale(0.95)';
      }
    });
  });

  card.addEventListener('mouseleave', () => {
    solutionCards.forEach((otherCard) => {
      otherCard.style.opacity = '1';
      otherCard.style.transform = 'scale(1)';
    });
  });
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

const cards = document.querySelectorAll('.solution-card, .service-item, .why-item');

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
// Performance Monitoring (Dev Tools)
// ============================================

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
