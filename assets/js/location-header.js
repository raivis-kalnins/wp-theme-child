/* Small dependency-free navigation controls for the SSR location pages. */
(() => {
  'use strict';
  const byId = id => document.getElementById(id);
  const langBtn = byId('dp-lang-toggle');
  const langMenu = byId('dp-lang-menu');
  const mobileBtn = byId('dp-mobile-toggle');
  const mobile = byId('dp-mobile-panel');
  const backdrop = byId('dp-mobile-overlay');
  const mobileClose = byId('dp-mobile-close');
  const search = byId('dp-location-search');
  const searchOpen = byId('dp-search-open');
  const searchClose = byId('dp-search-close');
  const searchInput = byId('dp-search-input');
  const choices = Array.from(byId('dp-location-results')?.querySelectorAll('a') || []);
  if (!langBtn || !langMenu || !mobileBtn || !mobile) return;

  const closeLanguages = () => {
    langMenu.hidden = true;
    langBtn.setAttribute('aria-expanded', 'false');
  };
  const closeMobile = (restoreFocus = false) => {
    if (mobile.hidden) return;
    mobile.hidden = true;
    backdrop.hidden = true;
    document.body.classList.remove('dp-menu-open');
    mobileBtn.setAttribute('aria-expanded', 'false');
    if (restoreFocus) mobileBtn.focus();
  };
  langBtn.addEventListener('click', event => {
    event.stopPropagation();
    const opening = langMenu.hidden;
    closeMobile();
    langMenu.hidden = !opening;
    langBtn.setAttribute('aria-expanded', String(opening));
    if (opening) langMenu.querySelector('a[aria-current="page"]')?.focus();
  });
  document.addEventListener('pointerdown', event => {
    if (!byId('dp-languages')?.contains(event.target)) closeLanguages();
  });
  mobileBtn.addEventListener('click', () => {
    closeLanguages();
    backdrop.hidden = false;
    mobile.hidden = false;
    document.body.classList.add('dp-menu-open');
    mobileBtn.setAttribute('aria-expanded', 'true');
    mobileClose?.focus();
  });
  mobileClose?.addEventListener('click', () => closeMobile(true));
  backdrop?.addEventListener('click', () => closeMobile(true));
  mobile.querySelectorAll('a').forEach(link => link.addEventListener('click', () => closeMobile()));
  window.addEventListener('resize', () => {
    if (innerWidth >= 1200) closeMobile();
  }, { passive: true });
  document.addEventListener('keydown', event => {
    if (event.key !== 'Escape') return;
    if (!langMenu.hidden) {
      closeLanguages();
      langBtn.focus();
    }
    if (!mobile.hidden) closeMobile(true);
  });
  if (search instanceof HTMLDialogElement && searchInput) {
    searchOpen?.addEventListener('click', () => {
      closeLanguages();
      closeMobile();
      if (typeof search.showModal === 'function') search.showModal();
      searchInput.focus();
    });
    searchClose?.addEventListener('click', () => search.close());
    search.addEventListener('click', event => {
      if (event.target === search) search.close();
    });
    searchInput.addEventListener('input', () => {
      const value = searchInput.value.trim().toLocaleLowerCase();
      choices.forEach(choice => {
        choice.hidden = !choice.dataset.locationSearch.toLocaleLowerCase().includes(value);
      });
    });
  }
})();
