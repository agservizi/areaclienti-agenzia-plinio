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
                    const raw = await response.text();
                    let payload;
                    try {
                        payload = raw ? JSON.parse(raw) : null;
                    } catch (parseError) {
                        console.error("Async form parse error", parseError, raw);
                        throw new Error("Risposta non valida dal server");
                    }

                    if (!payload) {
                        throw new Error("Risposta vuota dal server");
                    }

                    if (payload.success) {
                        form.dispatchEvent(new CustomEvent("async:success", { detail: payload }));
                    } else {
                        form.dispatchEvent(new CustomEvent("async:error", { detail: payload }));
                    }
                } catch (error) {
                    console.error("Async form error", error);
                    const message = error instanceof Error ? error.message : "Errore di rete";
                    form.dispatchEvent(new CustomEvent("async:error", { detail: { success: false, errors: [message] } }));
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

    /**
     * Manage collapsible admin sidebar with responsive behaviour.
     */
    function initAdminSidebar() {
        const shell = qs('[data-admin-shell]');
        if (!shell) {
            return;
        }

        const toggle = qs('[data-sidebar-toggle]', shell);
        const backdrop = qs('[data-sidebar-backdrop]', shell);
        const desktopMq = window.matchMedia('(min-width: 992px)');
        const body = document.body;

        const setAria = (expanded) => {
            if (toggle) {
                toggle.setAttribute('aria-expanded', String(expanded));
            }
        };

        const closeMobile = () => {
            shell.classList.remove('is-expanded');
            body.classList.remove('sidebar-overlay-open');
            setAria(false);
        };

        const syncState = () => {
            if (desktopMq.matches) {
                shell.classList.remove('is-expanded');
                body.classList.remove('sidebar-overlay-open');
                setAria(!shell.classList.contains('is-collapsed'));
            } else {
                shell.classList.remove('is-collapsed');
                setAria(shell.classList.contains('is-expanded'));
            }
        };

        if (toggle) {
            toggle.addEventListener('click', () => {
                if (desktopMq.matches) {
                    const collapsed = shell.classList.toggle('is-collapsed');
                    setAria(!collapsed);
                } else {
                    const expanded = shell.classList.toggle('is-expanded');
                    body.classList.toggle('sidebar-overlay-open', expanded);
                    setAria(expanded);
                }
            });
        }

        if (backdrop) {
            backdrop.addEventListener('click', () => {
                if (!desktopMq.matches) {
                    closeMobile();
                }
            });
        }

        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !desktopMq.matches && shell.classList.contains('is-expanded')) {
                closeMobile();
            }
        });

        const handleViewportChange = () => {
            closeMobile();
            syncState();
        };

        if (typeof desktopMq.addEventListener === "function") {
            desktopMq.addEventListener("change", handleViewportChange);
        } else if (typeof desktopMq.addListener === "function") {
            desktopMq.addListener(handleViewportChange);
        }

        syncState();
    }

    document.addEventListener("DOMContentLoaded", () => {
        initParallax();
        initFetchSecurity();
        initToasts();
        initAutoSubmitFilters();
        initAsyncForms();
        initCoverageCheck();
        initAdminSidebar();
    });
})();
