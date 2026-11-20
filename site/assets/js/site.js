// Small enhancements; every page works without this file.

/* copy the email address */
for (const btn of document.querySelectorAll('[data-copy]')) {
  if (!navigator.clipboard?.writeText) continue;
  btn.hidden = false;
  const label = btn.querySelector('[data-copy-label]');
  const status = btn.parentElement.querySelector('[data-copy-status]') || document.querySelector('[data-copy-status]');
  const original = label?.textContent;
  btn.addEventListener('click', async () => {
    try {
      await navigator.clipboard.writeText(btn.dataset.copy);
      if (label) label.textContent = 'Copied';
      if (status) status.textContent = 'Email address copied.';
      setTimeout(() => { if (label) label.textContent = original; if (status) status.textContent = ''; }, 2500);
    } catch {
      if (status) status.textContent = 'Could not copy. The address is ' + btn.dataset.copy + '.';
    }
  });
}

/* contact form: errors appear after interaction, in sync for screen readers (Modern Web Guidance: accessible-error-announcement) */
const form = document.querySelector('[data-form]');
if (form) {
  const fields = [...form.querySelectorAll('input:not([type=hidden]):not(#website), textarea')];
  const announcer = document.createElement('p');
  announcer.className = 'visually-hidden';
  announcer.setAttribute('aria-live', 'polite');
  form.append(announcer);
  let timer;

  const errorFor = (el) => document.getElementById(el.dataset.error);
  const message = (el) => {
    const v = el.validity;
    if (v.valueMissing) return el.id === 'name' ? 'Enter your name, so I know who to reply to.' : el.id === 'email' ? 'Enter your email address, so I can reply.' : `Write at least ${el.minLength} characters about what you want to build.`;
    if (v.typeMismatch) return 'Enter an email address like name@example.com.';
    if (v.tooShort) return `Write at least ${el.minLength} characters (now ${el.value.trim().length}).`;
    if (v.tooLong) return `Keep it under ${el.maxLength} characters.`;
    return '';
  };
  const link = (el, shown) => {
    const id = el.dataset.error;
    const ids = (el.getAttribute('aria-describedby') || '').split(/\s+/).filter((x) => x && x !== id);
    if (shown) ids.push(id);
    ids.length ? el.setAttribute('aria-describedby', ids.join(' ')) : el.removeAttribute('aria-describedby');
  };
  const sync = (el, announce) => {
    const err = errorFor(el);
    const invalid = el.matches(':user-invalid') || (el.dataset.touched === '1' && !el.validity.valid);
    if (invalid) {
      const text = message(el);
      if (err && text) err.textContent = text;
      if (err) err.hidden = false;
      el.setAttribute('aria-invalid', 'true');
      link(el, true);
      if (announce) {
        clearTimeout(timer);
        announcer.textContent = '';
        timer = setTimeout(() => { announcer.textContent = `${el.labels[0].textContent}: ${err?.textContent || text}`; }, 500);
      }
    } else {
      if (err) err.hidden = true;
      el.setAttribute('aria-invalid', 'false');
      link(el, false);
    }
  };
  for (const el of fields) {
    // the server may already have flagged it
    if (el.getAttribute('aria-invalid') !== 'true') el.setAttribute('aria-invalid', 'false');
    el.addEventListener('blur', () => { if (el.value !== '') el.dataset.touched = '1'; sync(el, true); });
    el.addEventListener('input', () => { if (el.getAttribute('aria-invalid') === 'true') sync(el, false); });
  }
  form.addEventListener('submit', (e) => {
    const bad = fields.filter((el) => !el.checkValidity());
    if (bad.length) {
      e.preventDefault();
      bad.forEach((el) => { el.dataset.touched = '1'; sync(el, false); });
      bad[0].focus();
      return;
    }
    const btn = form.querySelector('[data-submit]');
    if (btn) { btn.setAttribute('aria-disabled', 'true'); btn.addEventListener('click', (ev) => ev.preventDefault()); }
  });
  // a page sent back by the server with errors: start at the summary
  document.querySelector('[data-error-summary]')?.focus();
}
