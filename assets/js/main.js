/* Main JS interactions for AG Servizi area personale */
(function () {
    "use strict";

    const qs = (selector, ctx = document) => ctx.querySelector(selector);
    const qsa = (selector, ctx = document) => Array.from(ctx.querySelectorAll(selector));

    /**
     * Attach parallax effect to hero sections.
     */
    function initParallax() {
        qsa(".hero-parallax").forEach((section) => {
            section.addEventListener("mousemove", (event) => {
                const rect = section.getBoundingClientRect();
                const offsetX = (event.clientX - rect.left) / rect.width - 0.5;
                const offsetY = (event.clientY - rect.top) / rect.height - 0.5;
                section.style.setProperty("--parallax-x", `${offsetX * 12}px`);
                section.style.setProperty("--parallax-y", `${offsetY * 12}px`);
            });

            section.addEventListener("mouseleave", () => {
                section.style.removeProperty("--parallax-x");
                section.style.removeProperty("--parallax-y");
            });
        });
    }

    /**
     * Attach CSRF token to every fetch request automatically.
     */
    function initFetchSecurity() {
        const originalFetch = window.fetch;
        window.fetch = function (input, init = {}) {
            init.headers = init.headers || {};
            if (init.headers instanceof Headers) {
                init.headers.set("X-Requested-With", "XMLHttpRequest");
                const csrfToken = document.body.dataset.csrf;
                if (csrfToken) {
                    init.headers.set("X-CSRF-Token", csrfToken);
                }
            } else {
                init.headers["X-Requested-With"] = "XMLHttpRequest";
                const csrfToken = document.body.dataset.csrf;
                if (csrfToken) {
                    init.headers["X-CSRF-Token"] = csrfToken;
                }
            }
            return originalFetch(input, init);
        };
    }

    /**
     * Handle toast notifications using Bootstrap API.
     */
    function initToasts() {
        const toasts = qsa(".toast");
        toasts.forEach((toastEl) => {
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        });
    }

    /**
     * Auto-submit filters in tables when inputs change.
     */
    function initAutoSubmitFilters() {
        qsa("[data-autosubmit=true]").forEach((form) => {
            qsa("select,input", form).forEach((input) => {
                input.addEventListener("change", () => form.submit());
            });
        });
    }

    /**
     * Bind request forms for AJAX submission.
     */
    function initAsyncForms() {
        qsa("form[data-async=true]").forEach((form) => {
            form.addEventListener("submit", async (event) => {
                event.preventDefault();
                const submitBtn = qs('[type="submit"]', form);
                if (submitBtn) {
                    submitBtn.disabled = true;
                }

                const formData = new FormData(form);
                const action = form.dataset.action || form.action;

                try {
                    const response = await fetch(action, {
                        method: form.method || "POST",
                        body: formData,
                    });
                    const payload = await response.json();
                    if (payload.success) {
                        form.dispatchEvent(new CustomEvent("async:success", { detail: payload }));
                    } else {
                        form.dispatchEvent(new CustomEvent("async:error", { detail: payload }));
                    }
                } catch (error) {
                    console.error("Async form error", error);
                    form.dispatchEvent(new CustomEvent("async:error", { detail: { success: false, errors: ["Errore di rete"] } }));
                } finally {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                    }
                }
            });
        });
    }

    /**
     * Initialize coverage check with simulated results.
     */
    function initCoverageCheck() {
        const coverageForm = qs("#coverage-check-form");
        if (!coverageForm) {
            return;
        }

        const resultContainer = qs("#coverage-result");
        coverageForm.addEventListener("async:success", (event) => {
            const { data } = event.detail;
            if (!resultContainer) {
                return;
            }
            resultContainer.innerHTML = `
                <div class="alert alert-success mt-3">
                    <h5 class="mb-2">Copertura trovata</h5>
                    <p class="mb-1"><strong>Operatore consigliato:</strong> ${data.operator}</p>
                    <p class="mb-0"><strong>Velocità stimata:</strong> ${data.speed}</p>
                </div>
            `;
        });

        coverageForm.addEventListener("async:error", (event) => {
            if (!resultContainer) {
                return;
            }
            const detail = event.detail;
            const message = detail && detail.errors ? detail.errors.join(" ") : "Errore durante la verifica.";
            resultContainer.innerHTML = `<div class="alert alert-danger mt-3">${message}</div>`;
        });
    }

    document.addEventListener("DOMContentLoaded", () => {
        initParallax();
        initFetchSecurity();
        initToasts();
        initAutoSubmitFilters();
        initAsyncForms();
        initCoverageCheck();
    });
})();
