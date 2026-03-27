$(document).ready(function () {
    console.log('Admin JS loaded');

    // ── Theme Toggle ──────────────────────────────────────
    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('gh_theme', theme);
        if (theme === 'light') {
            $('#theme-icon').removeClass('bi-moon-stars-fill').addClass('bi-sun-fill');
            $('#theme-toggle').attr('title', 'Switch to Dark Mode');
        } else {
            $('#theme-icon').removeClass('bi-sun-fill').addClass('bi-moon-stars-fill');
            $('#theme-toggle').attr('title', 'Switch to Light Mode');
        }
    }

    // Init icon from current theme
    const savedTheme = localStorage.getItem('gh_theme') || 'dark';
    applyTheme(savedTheme);

    $('#theme-toggle').on('click', function () {
        const current = document.documentElement.getAttribute('data-theme') || 'dark';
        applyTheme(current === 'dark' ? 'light' : 'dark');
    });
    // ─────────────────────────────────────────────────────

    // Initialize Tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    });

});
