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
  console.log('[installation-lightbox] openLightbox called with:', gallery, startIndex);

  if (!installationLightbox || !lightboxImage) {
    console.error('[installation-lightbox] Lightbox elements not found on the page', {
      installationLightbox,
      lightboxImage,
    });
    return;
  }

  if (!gallery?.length) {
    console.error('[installation-lightbox] openLightbox called with an empty gallery', gallery);
    return;
  }

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
  console.log('[modern-interactions] lightbox init', { installationLightboxExists: !!installationLightbox });

  const openInstallationGallery = (triggerElement) => {
    try {
      console.log('[modern-interactions] openInstallationGallery triggered', { triggerElement });

      const card = triggerElement.closest('.installation-card');
      if (!card) {
        console.error('[installation-lightbox] Could not find a parent .installation-card for', triggerElement);
        return;
      }

      // Prefer an img with data-gallery, otherwise any element with data-gallery
      const media = card.querySelector('img[data-gallery]') || card.querySelector('[data-gallery]');
      console.log('[modern-interactions] found media element', { media });
      if (!media) {
        console.error('[installation-lightbox] Could not find an element with [data-gallery] inside the installation card', card);
        return;
      }

      const rawGallery = media.getAttribute('data-gallery');
      console.log('[installation-lightbox] raw data-gallery attribute:', rawGallery);

      let gallery = [];
      if (rawGallery) {
        try {
          const parsed = JSON.parse(rawGallery);
          gallery = Array.isArray(parsed) ? parsed : [parsed];
        } catch (error) {
          console.error('[installation-lightbox] Failed to parse gallery images JSON', error, rawGallery);
        }
      }

      gallery = gallery.filter(Boolean);

      // Fallback to the media src if no gallery entries
      if (!gallery.length && media.src) {
        gallery = [media.src];
      }

      console.log('[installation-lightbox] resolved gallery images:', gallery);

      if (!gallery.length) {
        console.error('[installation-lightbox] No gallery images available to display', media);
        return;
      }

      openLightbox(gallery, 0);
    } catch (error) {
      console.error('[installation-lightbox] Unexpected error opening installation gallery', error);
    }
  };

  // Attach to the card buttons
  document.querySelectorAll('[data-open-installation]').forEach((button) => {
    button.addEventListener('click', (ev) => {
      ev.preventDefault();
      ev.stopPropagation();
      console.log('[modern-interactions] installation button clicked', { button });
      openInstallationGallery(button);
    });
  });

  // Attach to the images themselves
  document.querySelectorAll('.installation-media img[data-gallery]').forEach((image) => {
    image.addEventListener('click', (ev) => {
      ev.preventDefault();
      ev.stopPropagation();
      console.log('[modern-interactions] installation image clicked', { image });
      openInstallationGallery(image);
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
