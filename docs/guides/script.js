// Professional Image Slider for ARTSCI
class HeroSlider {
  constructor() {
    this.slides = document.querySelectorAll('.hero-slide');
    this.indicators = document.querySelectorAll('.indicator');
    this.prevBtn = document.querySelector('.slider-btn.prev');
    this.nextBtn = document.querySelector('.slider-btn.next');
    this.currentSlide = 0;
    this.autoPlayInterval = null;
    this.autoPlayDelay = 5000; // 5 seconds
    
    this.init();
  }

  init() {
    if (!this.slides.length) return;

    // Event listeners for buttons
    this.prevBtn?.addEventListener('click', () => this.prevSlide());
    this.nextBtn?.addEventListener('click', () => this.nextSlide());
    
    // Event listeners for indicators
    this.indicators.forEach((indicator, index) => {
      indicator.addEventListener('click', () => this.goToSlide(index));
    });

    // Start auto-play
    this.startAutoPlay();

    // Pause on hover
    const slider = document.querySelector('.hero-slider-wrapper');
    slider?.addEventListener('mouseenter', () => this.stopAutoPlay());
    slider?.addEventListener('mouseleave', () => this.startAutoPlay());

    // Pause when user interacts
    this.slides.forEach(slide => {
      slide.addEventListener('click', () => {
        this.stopAutoPlay();
      });
    });
  }

  updateSlide() {
    // Remove active class from all slides and indicators
    this.slides.forEach(slide => slide.classList.remove('active', 'prev'));
    this.indicators.forEach(indicator => indicator.classList.remove('active'));

    // Add active class to current slide and indicator
    this.slides[this.currentSlide].classList.add('active');
    this.indicators[this.currentSlide].classList.add('active');
  }

  nextSlide() {
    const prevIndex = this.currentSlide;
    this.currentSlide = (this.currentSlide + 1) % this.slides.length;
    this.slides[prevIndex].classList.add('prev');
    this.updateSlide();
  }

  prevSlide() {
    this.currentSlide = (this.currentSlide - 1 + this.slides.length) % this.slides.length;
    this.updateSlide();
  }

  goToSlide(index) {
    this.currentSlide = index;
    this.updateSlide();
    this.stopAutoPlay();
    this.startAutoPlay();
  }

  startAutoPlay() {
    if (!this.slides.length) return;
    this.autoPlayInterval = setInterval(() => {
      this.nextSlide();
    }, this.autoPlayDelay);
  }

  stopAutoPlay() {
    if (this.autoPlayInterval) {
      clearInterval(this.autoPlayInterval);
    }
  }
}

// Mobile Navigation Toggle
class MobileNav {
  constructor() {
    this.nav = document.querySelector('.nav');
    this.toggle = document.querySelector('.nav-toggle');
    this.navLinks = document.querySelectorAll('.nav-links a');
    
    this.init();
  }

  init() {
    this.toggle?.addEventListener('click', () => this.toggleNav());
    
    // Close nav when a link is clicked
    this.navLinks.forEach(link => {
      link.addEventListener('click', () => this.closeNav());
    });
  }

  toggleNav() {
    this.nav.classList.toggle('open');
    this.toggle.setAttribute('aria-expanded', 
      this.nav.classList.contains('open') ? 'true' : 'false');
  }

  closeNav() {
    this.nav.classList.remove('open');
    this.toggle.setAttribute('aria-expanded', 'false');
  }
}

// Smooth Scroll
class SmoothScroll {
  constructor() {
    this.init();
  }

  init() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', (e) => {
        const target = document.querySelector(anchor.getAttribute('href'));
        if (target) {
          e.preventDefault();
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      });
    });
  }
}

// Intersection Observer for Animations
class AnimationObserver {
  constructor() {
    this.init();
  }

  init() {
    const options = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'translateY(0)';
          observer.unobserve(entry.target);
        }
      });
    }, options);

    // Observe cards and service items
    document.querySelectorAll('.card, .gallery-card, .partner-tile').forEach(element => {
      element.style.opacity = '0';
      element.style.transform = 'translateY(20px)';
      element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
      observer.observe(element);
    });
  }
}

// Active Link Highlighting
class ActiveLink {
  constructor() {
    this.navLinks = document.querySelectorAll('.nav-links a');
    this.init();
  }

  init() {
    window.addEventListener('scroll', () => this.updateActiveLink());
    this.updateActiveLink();
  }

  updateActiveLink() {
    const scrollPosition = window.scrollY + 100;

    this.navLinks.forEach(link => {
      const target = document.querySelector(link.getAttribute('href'));
      if (target) {
        const offsetTop = target.offsetTop;
        const offsetHeight = target.offsetHeight;

        if (scrollPosition >= offsetTop && scrollPosition < offsetTop + offsetHeight) {
          this.navLinks.forEach(l => l.classList.remove('active'));
          link.classList.add('active');
        }
      }
    });
  }
}

// Initialize all components when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  new HeroSlider();
  new MobileNav();
  new SmoothScroll();
  new AnimationObserver();
  new ActiveLink();
});

// Performance optimization: Lazy load images
if ('IntersectionObserver' in window) {
  const imageObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const img = entry.target;
        if (img.dataset.src) {
          img.src = img.dataset.src;
          img.removeAttribute('data-src');
          imageObserver.unobserve(img);
        }
      }
    });
  });

  document.querySelectorAll('img[data-src]').forEach(img => {
    imageObserver.observe(img);
  });
}
