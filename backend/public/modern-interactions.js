'use strict';

// ============================================
// Hero Image Carousel
// ============================================
const heroSlides = document.querySelectorAll('.hero-slide-bg');
let currentSlide = 0;

if (heroSlides.length > 0) {
  heroSlides.forEach((slide, index) => {
    slide.classList.remove('active');
    if (index === 0) {
      slide.classList.add('active');
    }
  });

  setInterval(() => {
    const nextSlide = (currentSlide + 1) % heroSlides.length;
    heroSlides[currentSlide].classList.remove('active');
    heroSlides[nextSlide].classList.add('active');
    currentSlide = nextSlide;
  }, 4500);
}

// ============================================
// Mobile navigation toggle
// ============================================
const navToggle = document.querySelector('.nav-toggle');
const navMenu = document.querySelector('.nav-menu');

if (navToggle && navMenu) {
  navToggle.addEventListener('click', (event) => {
    event.stopPropagation();
    navMenu.classList.toggle('active');
    navToggle.classList.toggle('active');
  });

  navMenu.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => {
      navMenu.classList.remove('active');
      navToggle.classList.remove('active');
    });
  });

  document.addEventListener('click', (event) => {
    if (!event.target.closest('.nav-container') && navMenu.classList.contains('active')) {
      navMenu.classList.remove('active');
      navToggle.classList.remove('active');
    }
  });
}

// ============================================
// Navbar shadow on scroll
// ============================================
const navbar = document.querySelector('.navbar');
if (navbar) {
  window.addEventListener('scroll', () => {
    if (window.scrollY > 40) {
      navbar.style.boxShadow = '0 8px 24px rgba(10, 20, 40, 0.08)';
    } else {
      navbar.style.boxShadow = 'none';
    }
  });
}

// ============================================
// Smooth anchor scrolling
// ============================================
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener('click', function (event) {
    const href = this.getAttribute('href');
    if (!href || href === '#') {
      return;
    }

    const target = document.querySelector(href);
    if (!target) {
      return;
    }

    event.preventDefault();
    target.scrollIntoView({ behavior: 'smooth', block: 'start' });

    if (navMenu && navMenu.classList.contains('active')) {
      navMenu.classList.remove('active');
      navToggle.classList.remove('active');
    }
  });
});

// ============================================
// Active nav link indicator
// ============================================
const navLinks = document.querySelectorAll('.nav-menu a:not(.cta-nav)');
const sections = document.querySelectorAll('section[id]');

if (navLinks.length > 0 && sections.length > 0) {
  window.addEventListener('scroll', () => {
    const scrollPosition = window.scrollY + 120;

    let currentSection = sections[0].id;
    sections.forEach((section) => {
      if (scrollPosition >= section.offsetTop) {
        currentSection = section.id;
      }
    });

    navLinks.forEach((link) => {
      const href = link.getAttribute('href');
      link.classList.toggle('active', href === `#${currentSection}`);
    });
  });
}

// ============================================
// Installation photo lightbox
// ============================================
const installationLightbox = document.querySelector('.installation-lightbox');
const lightboxImage = installationLightbox?.querySelector('.lightbox-image');
const lightboxCount = installationLightbox?.querySelector('.lightbox-count');
const lightboxClose = installationLightbox?.querySelector('.lightbox-close');
const lightboxPrev = installationLightbox?.querySelector('.lightbox-nav.prev');
const lightboxNext = installationLightbox?.querySelector('.lightbox-nav.next');
const lightboxCaption = installationLightbox?.querySelector('.lightbox-title');

let installationGallery = [];
let installationIndex = 0;

const updateLightbox = () => {
  if (!installationLightbox || !lightboxImage || !lightboxCount) return;

  const currentImage = installationGallery[installationIndex];
  lightboxImage.src = currentImage || '';
  lightboxImage.alt = `Installation photo ${installationIndex + 1}`;
  lightboxCount.textContent = installationGallery.length > 1
    ? `${installationIndex + 1} of ${installationGallery.length}`
    : '1 of 1';
};

const openLightbox = (gallery, startIndex = 0) => {
  if (!installationLightbox || !lightboxImage || !gallery?.length) return;

  installationGallery = gallery;
  installationIndex = Math.min(Math.max(startIndex, 0), gallery.length - 1);
  updateLightbox();
  installationLightbox.classList.add('is-open');
  document.body.classList.add('installation-lightbox-open');
};

const closeLightbox = () => {
  if (!installationLightbox) return;

  installationLightbox.classList.remove('is-open');
  document.body.classList.remove('installation-lightbox-open');
};

const showNextImage = () => {
  if (!installationGallery.length) return;
  installationIndex = (installationIndex + 1) % installationGallery.length;
  updateLightbox();
};

const showPreviousImage = () => {
  if (!installationGallery.length) return;
  installationIndex = (installationIndex - 1 + installationGallery.length) % installationGallery.length;
  updateLightbox();
};

if (installationLightbox) {
  document.querySelectorAll('[data-open-installation]').forEach((button) => {
    button.addEventListener('click', () => {
      const media = button.closest('.installation-card')?.querySelector('[data-gallery]');
      if (!media) return;

      try {
        const gallery = JSON.parse(media.getAttribute('data-gallery') || '[]');
        openLightbox(gallery, 0);
      } catch (error) {
        console.error('Failed to parse gallery images', error);
      }
    });
  });

  lightboxClose?.addEventListener('click', closeLightbox);
  lightboxPrev?.addEventListener('click', showPreviousImage);
  lightboxNext?.addEventListener('click', showNextImage);

  installationLightbox.addEventListener('click', (event) => {
    if (event.target === installationLightbox) {
      closeLightbox();
    }
  });

  window.addEventListener('keydown', (event) => {
    if (!installationLightbox.classList.contains('is-open')) return;
    if (event.key === 'Escape') {
      closeLightbox();
    }
    if (event.key === 'ArrowRight') {
      showNextImage();
    }
    if (event.key === 'ArrowLeft') {
      showPreviousImage();
    }
  });
}
