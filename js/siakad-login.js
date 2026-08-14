document.addEventListener('DOMContentLoaded', function () {
  /* ============ Toggle lihat / sembunyikan password ============ */
  const toggleBtn     = document.querySelector('[data-password-toggle]');
  const passwordInput = document.getElementById('password');

  const eyeOffSvg = `
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
      <path d="M3 3l18 18"/>
      <path d="M10.5 5.2A10 10 0 0 1 12 5c5.2 0 8.8 4.4 9.7 7-.35 1-1.1 2.4-2.2 3.6M6.5 6.5C4.3 8.1 2.9 10.2 2.3 12c.9 2.6 4.5 7 9.7 7 1.5 0 2.8-.4 4-1"/>
      <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>
    </svg>`;

  const eyeSvg = `
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
      <path d="M2.3 12c.9-2.6 4.5-7 9.7-7s8.8 4.4 9.7 7c-.9 2.6-4.5 7-9.7 7S3.2 14.6 2.3 12Z"/>
      <circle cx="12" cy="12" r="3"/>
    </svg>`;

  if (toggleBtn && passwordInput) {
    toggleBtn.addEventListener('click', function () {
      const isHidden = passwordInput.type === 'password';

      passwordInput.type = isHidden ? 'text' : 'password';
      toggleBtn.innerHTML = isHidden ? eyeSvg : eyeOffSvg;
      toggleBtn.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
    });
  }
});