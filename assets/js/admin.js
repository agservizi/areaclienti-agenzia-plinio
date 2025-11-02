document.addEventListener('DOMContentLoaded', () => {
    const adminShell = document.querySelector('[data-admin-shell]');
    const toggleButtons = document.querySelectorAll('[data-admin-toggle]');
    const sidebarBackdrop = document.querySelector('[data-admin-backdrop]');
    const sidebarLinks = document.querySelectorAll('.admin-menu-link');

    const closeSidebar = () => {
        if (adminShell) {
            adminShell.classList.remove('sidebar-open');
        }
    };

    toggleButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (adminShell) {
                adminShell.classList.toggle('sidebar-open');
            }
        });
    });

    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', closeSidebar);
    }

    sidebarLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 992) {
                closeSidebar();
            }
        });
    });

    const summaryCards = document.querySelectorAll('.admin-summary-card');

    summaryCards.forEach(card => {
        card.addEventListener('mouseenter', () => card.classList.add('is-hovered'));
        card.addEventListener('mouseleave', () => card.classList.remove('is-hovered'));
    });
});
