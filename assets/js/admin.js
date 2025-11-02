document.addEventListener('DOMContentLoaded', () => {
    const cards = document.querySelectorAll('.dashboard-panel .card');

    cards.forEach(card => {
        card.addEventListener('mouseenter', () => card.classList.add('shadow-lg'));
        card.addEventListener('mouseleave', () => card.classList.remove('shadow-lg'));
    });
});
