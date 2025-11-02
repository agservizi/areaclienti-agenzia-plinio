document.addEventListener('DOMContentLoaded', function () {
    const navLinks = document.querySelectorAll('.admin-sidebar .nav-link');
    const path = window.location.pathname;
    navLinks.forEach(function (link) {
        if (link.getAttribute('href') === path) {
            link.classList.add('active');
        }
    });

    const forms = document.querySelectorAll('form[action="/client/coverage/check"]');
    forms.forEach(function (form) {
        form.addEventListener('submit', function () {
            const button = form.querySelector('button[type="submit"]');
            if (button) {
                button.disabled = true;
                button.dataset.originalText = button.textContent;
                button.textContent = 'Verifica in corso...';
            }
        });
    });
});
