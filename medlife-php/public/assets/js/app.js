(() => {
  const header = document.getElementById('site-header');
  if (header) {
    const updateHeader = () => header.classList.toggle('scrolled', window.scrollY > 16);
    updateHeader();
    window.addEventListener('scroll', updateHeader, { passive: true });
  }

  const toggle = document.getElementById('nav-toggle');
  const nav = document.getElementById('main-nav');
  if (toggle && nav) {
    const close = () => {
      document.body.classList.remove('nav-open');
      toggle.setAttribute('aria-expanded', 'false');
    };

    toggle.addEventListener('click', () => {
      const open = document.body.classList.toggle('nav-open');
      toggle.setAttribute('aria-expanded', String(open));
    });

    nav.addEventListener('click', (event) => {
      if (event.target instanceof Element && event.target.closest('a')) close();
    });

    document.addEventListener('keydown', (event) => {
      if (event.key === 'Escape') close();
    });

    window.addEventListener('resize', () => {
      if (window.innerWidth > 1100) close();
    }, { passive: true });
  }

  const reveals = document.querySelectorAll('[data-reveal]');
  if (reveals.length && 'IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        entry.target.classList.add('revealed');
        observer.unobserve(entry.target);
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    reveals.forEach((element) => observer.observe(element));
  } else {
    reveals.forEach((element) => element.classList.add('revealed'));
  }

  const counters = document.querySelectorAll('[data-count]');
  if (counters.length && 'IntersectionObserver' in window) {
    const counterObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        const element = entry.target;
        const target = parseInt(element.dataset.count || '', 10);
        if (Number.isNaN(target)) return;

        const duration = 1200;
        const start = performance.now();
        const tick = (now) => {
          const progress = Math.min((now - start) / duration, 1);
          const eased = 1 - Math.pow(1 - progress, 3);
          element.textContent = Math.floor(eased * target).toLocaleString();
          if (progress < 1) requestAnimationFrame(tick);
        };

        requestAnimationFrame(tick);
        counterObserver.unobserve(element);
      });
    }, { threshold: 0.5 });

    counters.forEach((element) => counterObserver.observe(element));
  }

  const search = document.getElementById('doctor-search');
  const grid = document.getElementById('doctors-grid');
  const pills = document.querySelectorAll('.ml-pill[data-filter]');
  let activeFilter = 'all';

  function filterDoctors() {
    if (!grid) return;
    const query = search ? search.value.toLowerCase().trim() : '';
    const cards = grid.querySelectorAll('[data-department]');

    cards.forEach((card) => {
      const department = card.dataset.department || '';
      const haystack = `${card.dataset.name || ''} ${card.dataset.spec || ''} ${department}`.toLowerCase();
      const matchesFilter = activeFilter === 'all' || department === activeFilter;
      const matchesSearch = query === '' || haystack.includes(query);
      card.hidden = !(matchesFilter && matchesSearch);
    });
  }

  if (search) search.addEventListener('input', filterDoctors);

  pills.forEach((pill) => {
    pill.setAttribute('aria-pressed', pill.classList.contains('is-active') ? 'true' : 'false');
    pill.addEventListener('click', () => {
      pills.forEach((item) => {
        item.classList.remove('is-active');
        item.setAttribute('aria-pressed', 'false');
      });
      pill.classList.add('is-active');
      pill.setAttribute('aria-pressed', 'true');
      activeFilter = pill.dataset.filter || 'all';
      filterDoctors();
    });
  });
})();
