document.addEventListener('DOMContentLoaded', () => {
    const filterForm = document.querySelector('form[method="get"]');

    if (filterForm) {
        const select = filterForm.querySelector('select[name="categoria"]');

        if (select) {
            select.addEventListener('change', () => filterForm.submit());
        }
    }
});
