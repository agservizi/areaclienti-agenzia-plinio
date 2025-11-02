document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.glass-container .btn-outline-light');

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            button.classList.add('active');
            setTimeout(() => button.classList.remove('active'), 200);
        });
    });
});
