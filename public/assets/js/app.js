(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const uploadForm = document.getElementById('document-upload');
        if (uploadForm) {
            uploadForm.addEventListener('submit', async function (event) {
                event.preventDefault();
                const feedback = document.getElementById('upload-feedback');
                if (!uploadForm.file.files.length) {
                    feedback.textContent = 'Seleziona un file PDF da caricare.';
                    feedback.className = 'text-danger';
                    return;
                }
                const formData = new FormData(uploadForm);
                try {
                    const response = await fetch('/api/upload', {
                        method: 'POST',
                        body: formData,
                    });
                    const result = await response.json();
                    if (!response.ok) {
                        throw new Error(result.error || 'Errore durante il caricamento');
                    }
                    feedback.textContent = 'Caricamento completato. Aggiorna la pagina per vedere il documento.';
                    feedback.className = 'text-success';
                    uploadForm.reset();
                } catch (error) {
                    feedback.textContent = error.message;
                    feedback.className = 'text-danger';
                }
            });
        }
    });
})();
