/**
 * Khalil Global Trade — Site Script
 * Vanilla JS, no build step. Sections: CONFIG, Smooth Scroll, Reveal,
 * Scrollspy, Header, Parallax, Hero Words, Mobile Nav.
 */
(function () {
  'use strict';

  var CONFIG = {
    companyName: 'Khalil Global Trade',
    email: 'info@khalil-global-trade.de',
    phone: '+49 (0) 000 000 000',
    address: {
      street: 'Musterstraße 1',
      zip: '00000',
      city: 'Musterstadt',
      country: 'Deutschland'
    },
    social: {
      linkedin: '#'
    },
    reducedMotion: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
    isMobile: window.matchMedia('(max-width: 900px)').matches
  };

  window.KHALIL_CONFIG = CONFIG;

  document.addEventListener('DOMContentLoaded', function () {
    initSmoothScroll();
    initHeaderScroll();
    initMobileNav();
    initRevealObserver();
    initScrollspy();
    initParallax();
    initHeroWordDelays();
    initYear();
    fillConfigTargets();
  });

  /* ------------------------------------------------------------------ */
  /* Smooth Scroll (Lenis, self-hosted, no CDN)                          */
  /* ------------------------------------------------------------------ */

  function initSmoothScroll() {
    var reduce = CONFIG.reducedMotion;

    if (!reduce && window.Lenis) {
      var lenis = new window.Lenis({
        duration: 1.1,
        easing: function (t) {
          return Math.min(1, 1.001 - Math.pow(2, -10 * t));
        },
        smoothWheel: true,
        wheelMultiplier: 1
      });

      function raf(time) {
        lenis.raf(time);
        requestAnimationFrame(raf);
      }
      requestAnimationFrame(raf);

      window.__lenis = lenis;
    } else {
      document.documentElement.style.scrollBehavior = 'smooth';
    }

    var anchors = document.querySelectorAll('a[href^="#"]');
    anchors.forEach(function (a) {
      a.addEventListener('click', function (e) {
        var id = a.getAttribute('href');
        if (id.length < 2) return;
        var target = document.querySelector(id);
        if (!target) return;
        e.preventDefault();
        closeMobileNav();
        if (window.__lenis) {
          window.__lenis.scrollTo(target, { offset: -72 });
        } else {
          var y = target.getBoundingClientRect().top + window.pageYOffset - 72;
          window.scrollTo({ top: y, behavior: 'smooth' });
        }
      });
    });
  }

  /* ------------------------------------------------------------------ */
  /* Sticky header: hide on scroll down, reveal on scroll up             */
  /* ------------------------------------------------------------------ */

  function initHeaderScroll() {
    var header = document.querySelector('.site-header');
    var indicator = document.querySelector('.scroll-indicator');
    if (!header) return;

    var lastY = window.pageYOffset;
    var ticking = false;

    function onScroll() {
      var y = window.pageYOffset;

      if (y > lastY && y > header.offsetHeight * 1.5) {
        header.classList.add('header-hidden');
      } else {
        header.classList.remove('header-hidden');
      }

      if (indicator) {
        indicator.classList.toggle('is-hidden', y > 80);
      }

      lastY = y;
      ticking = false;
    }

    window.addEventListener('scroll', function () {
      if (!ticking) {
        requestAnimationFrame(onScroll);
        ticking = true;
      }
    }, { passive: true });
  }

  /* ------------------------------------------------------------------ */
  /* Mobile navigation                                                    */
  /* ------------------------------------------------------------------ */

  function initMobileNav() {
    var toggle = document.querySelector('.nav-toggle');
    if (!toggle) return;

    toggle.addEventListener('click', function () {
      var isOpen = document.body.classList.toggle('nav-open');
      toggle.setAttribute('aria-expanded', String(isOpen));
      document.body.style.overflow = isOpen ? 'hidden' : '';
    });
  }

  function closeMobileNav() {
    var toggle = document.querySelector('.nav-toggle');
    document.body.classList.remove('nav-open');
    document.body.style.overflow = '';
    if (toggle) toggle.setAttribute('aria-expanded', 'false');
  }

  /* ------------------------------------------------------------------ */
  /* Scroll-Reveal via IntersectionObserver                              */
  /* ------------------------------------------------------------------ */

  function initRevealObserver() {
    var items = document.querySelectorAll('[data-reveal]');
    if (!items.length) return;

    items.forEach(function (el) {
      var delay = el.getAttribute('data-reveal-delay');
      if (delay) {
        el.style.setProperty('--reveal-delay', delay + 'ms');
      }
    });

    if (!('IntersectionObserver' in window)) {
      items.forEach(function (el) { el.classList.add('is-visible'); });
      return;
    }

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15, rootMargin: '0px 0px -8% 0px' });

    items.forEach(function (el) { observer.observe(el); });
  }

  /* ------------------------------------------------------------------ */
  /* Scrollspy: highlight active nav link                                 */
  /* ------------------------------------------------------------------ */

  function initScrollspy() {
    var sections = document.querySelectorAll('main section[id]');
    var links = document.querySelectorAll('.nav-links a, .mobile-nav a');
    if (!sections.length || !links.length) return;

    var map = {};
    links.forEach(function (link) {
      var href = link.getAttribute('href');
      if (href && href.charAt(0) === '#') {
        map[href.slice(1)] = map[href.slice(1)] || [];
        map[href.slice(1)].push(link);
      }
    });

    if (!('IntersectionObserver' in window)) return;

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        var id = entry.target.id;
        var relatedLinks = map[id];
        if (!relatedLinks) return;

        if (entry.isIntersecting) {
          links.forEach(function (l) { l.classList.remove('is-active'); });
          relatedLinks.forEach(function (l) { l.classList.add('is-active'); });
        }
      });
    }, { threshold: 0, rootMargin: '-40% 0px -55% 0px' });

    sections.forEach(function (s) { observer.observe(s); });
  }

  /* ------------------------------------------------------------------ */
  /* Parallax (Hero + About media) — disabled on mobile & reduced motion */
  /* ------------------------------------------------------------------ */

  function initParallax() {
    if (CONFIG.reducedMotion || CONFIG.isMobile) return;

    var layers = document.querySelectorAll('[data-parallax]');
    if (!layers.length) return;

    var ticking = false;

    function update() {
      var vh = window.innerHeight;
      layers.forEach(function (layer) {
        var speed = parseFloat(layer.getAttribute('data-parallax')) || 0.15;
        var rect = layer.parentElement.getBoundingClientRect();
        var progress = (rect.top) / vh;
        var offset = progress * 100 * speed;
        layer.style.transform = 'translate3d(0,' + offset.toFixed(2) + 'px,0)';
      });
      ticking = false;
    }

    window.addEventListener('scroll', function () {
      if (!ticking) {
        requestAnimationFrame(update);
        ticking = true;
      }
    }, { passive: true });

    update();
  }

  /* ------------------------------------------------------------------ */
  /* Hero headline word-by-word stagger                                  */
  /* ------------------------------------------------------------------ */

  function initHeroWordDelays() {
    var words = document.querySelectorAll('.hero-title .word span');
    words.forEach(function (span, i) {
      span.style.setProperty('--d', (0.15 + i * 0.09) + 's');
    });
  }

  /* ------------------------------------------------------------------ */
  /* Footer year                                                         */
  /* ------------------------------------------------------------------ */

  function initYear() {
    var el = document.querySelector('[data-year]');
    if (el) el.textContent = new Date().getFullYear();
  }

  /* ------------------------------------------------------------------ */
  /* Fill contact data from CONFIG                                       */
  /* ------------------------------------------------------------------ */

  function fillConfigTargets() {
    document.querySelectorAll('[data-config="email"]').forEach(function (el) {
      el.textContent = CONFIG.email;
      if (el.tagName === 'A') el.href = 'mailto:' + CONFIG.email;
    });
    document.querySelectorAll('[data-config="phone"]').forEach(function (el) {
      el.textContent = CONFIG.phone;
      if (el.tagName === 'A') el.href = 'tel:' + CONFIG.phone.replace(/[^+\d]/g, '');
    });
    document.querySelectorAll('[data-config="address"]').forEach(function (el) {
      el.textContent = CONFIG.address.street + ', ' + CONFIG.address.zip + ' ' + CONFIG.address.city;
    });
  }

  /* ------------------------------------------------------------------ */
  /* Contact form: placeholder submit (no backend in this pass)          */
  /* ------------------------------------------------------------------ */

  document.addEventListener('submit', function (e) {
    var form = e.target.closest('#contact-form');
    if (!form) return;
    e.preventDefault();
    var note = form.querySelector('.form-note');
    if (note) {
      note.textContent = 'Danke für Ihre Nachricht. Die Formularanbindung folgt in einem separaten Schritt.';
    }
    form.reset();
  });
})();
