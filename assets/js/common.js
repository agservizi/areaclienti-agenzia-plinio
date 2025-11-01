/**
 * Funzioni comuni per l'area clienti
 */

// Configurazione globale
const CONFIG = {
    API_BASE_URL: 'api/',
    SITE_URL: window.location.origin + '/area-clienti/',
    CSRF_TOKEN: null,
    DEBUG: true
};

// Utilità per il debugging
const debug = (...args) => {
    if (CONFIG.DEBUG) {
        console.log('[DEBUG]', ...args);
    }
};

/**
 * Classe per gestire le chiamate API
 */
class ApiClient {
    constructor() {
        this.baseURL = CONFIG.API_BASE_URL;
    }

    async request(endpoint, options = {}) {
        const url = this.baseURL + endpoint;
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        // Merge delle opzioni
        const finalOptions = {
            ...defaultOptions,
            ...options,
            headers: {
                ...defaultOptions.headers,
                ...options.headers
            }
        };

        // Aggiunta CSRF token se disponibile
        if (CONFIG.CSRF_TOKEN) {
            finalOptions.headers['X-CSRF-Token'] = CONFIG.CSRF_TOKEN;
        }

        try {
            debug('API Request:', url, finalOptions);
            
            const response = await fetch(url, finalOptions);
            const data = await response.json();
            
            debug('API Response:', data);

            if (!response.ok) {
                throw new Error(data.error || `HTTP error! status: ${response.status}`);
            }

            return data;
        } catch (error) {
            debug('API Error:', error);
            throw error;
        }
    }

    async get(endpoint) {
        return this.request(endpoint, { method: 'GET' });
    }

    async post(endpoint, data) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    async put(endpoint, data) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    async delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    }

    async uploadFile(endpoint, formData) {
        return this.request(endpoint, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
                // Non impostare Content-Type per i file upload
            }
        });
    }
}

// Istanza globale del client API
const api = new ApiClient();

/**
 * Classe per gestire le notifiche
 */
class NotificationManager {
    constructor() {
        this.container = null;
        this.init();
    }

    init() {
        // Crea il container delle notifiche se non esiste
        if (!document.getElementById('notification-container')) {
            const container = document.createElement('div');
            container.id = 'notification-container';
            container.className = 'notification-container';
            document.body.appendChild(container);
        }
        this.container = document.getElementById('notification-container');
    }

    show(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        const icon = this.getIcon(type);
        notification.innerHTML = `
            <div class="notification-content">
                <i class="${icon}"></i>
                <span class="notification-message">${message}</span>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        this.container.appendChild(notification);

        // Animazione di entrata
        setTimeout(() => notification.classList.add('show'), 10);

        // Rimozione automatica
        if (duration > 0) {
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, duration);
        }

        return notification;
    }

    success(message, duration) {
        return this.show(message, 'success', duration);
    }

    error(message, duration = 8000) {
        return this.show(message, 'error', duration);
    }

    warning(message, duration) {
        return this.show(message, 'warning', duration);
    }

    info(message, duration) {
        return this.show(message, 'info', duration);
    }

    getIcon(type) {
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };
        return icons[type] || icons.info;
    }
}

// Istanza globale del notification manager
const notifications = new NotificationManager();

/**
 * Classe per gestire i modali
 */
class ModalManager {
    static show(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
            
            // Focus sul primo input
            const firstInput = modal.querySelector('input, textarea, select');
            if (firstInput) {
                setTimeout(() => firstInput.focus(), 100);
            }
        }
    }

    static hide(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    static hideAll() {
        document.querySelectorAll('.modal.show').forEach(modal => {
            modal.classList.remove('show');
        });
        document.body.style.overflow = '';
    }
}

/**
 * Classe per gestire i form
 */
class FormManager {
    constructor(formElement) {
        this.form = typeof formElement === 'string' ? 
            document.getElementById(formElement) : formElement;
        this.submitButton = this.form?.querySelector('button[type="submit"]');
        this.init();
    }

    init() {
        if (!this.form) return;

        // Previeni submit multipli
        this.form.addEventListener('submit', (e) => {
            if (this.form.classList.contains('submitting')) {
                e.preventDefault();
                return false;
            }
        });

        // Validazione in tempo reale
        this.form.querySelectorAll('input, textarea, select').forEach(field => {
            field.addEventListener('blur', () => this.validateField(field));
            field.addEventListener('input', () => this.clearFieldError(field));
        });
    }

    validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        let errorMessage = '';

        // Rimuovi errori precedenti
        this.clearFieldError(field);

        // Validazione required
        if (field.hasAttribute('required') && !value) {
            isValid = false;
            errorMessage = 'Questo campo è obbligatorio';
        }

        // Validazione email
        if (field.type === 'email' && value && !this.isValidEmail(value)) {
            isValid = false;
            errorMessage = 'Inserisci un\'email valida';
        }

        // Validazione password
        if (field.type === 'password' && value && value.length < 8) {
            isValid = false;
            errorMessage = 'La password deve essere di almeno 8 caratteri';
        }

        // Validazione codice fiscale
        if (field.name === 'fiscal_code' && value && !this.isValidFiscalCode(value)) {
            isValid = false;
            errorMessage = 'Codice fiscale non valido';
        }

        // Validazione telefono
        if (field.type === 'tel' && value && !this.isValidPhone(value)) {
            isValid = false;
            errorMessage = 'Numero di telefono non valido';
        }

        if (!isValid) {
            this.showFieldError(field, errorMessage);
        }

        return isValid;
    }

    validateForm() {
        let isValid = true;
        this.form.querySelectorAll('input, textarea, select').forEach(field => {
            if (!this.validateField(field)) {
                isValid = false;
            }
        });
        return isValid;
    }

    showFieldError(field, message) {
        field.classList.add('is-invalid');
        
        // Rimuovi errore precedente
        const existingError = field.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }

        // Aggiungi nuovo errore
        const errorElement = document.createElement('div');
        errorElement.className = 'field-error';
        errorElement.textContent = message;
        field.parentNode.appendChild(errorElement);
    }

    clearFieldError(field) {
        field.classList.remove('is-invalid');
        const errorElement = field.parentNode.querySelector('.field-error');
        if (errorElement) {
            errorElement.remove();
        }
    }

    setSubmitting(isSubmitting) {
        if (isSubmitting) {
            this.form.classList.add('submitting');
            if (this.submitButton) {
                this.submitButton.disabled = true;
                this.submitButton.classList.add('loading');
                this.submitButton.setAttribute('data-original-text', this.submitButton.textContent);
                this.submitButton.textContent = 'Elaborazione...';
            }
        } else {
            this.form.classList.remove('submitting');
            if (this.submitButton) {
                this.submitButton.disabled = false;
                this.submitButton.classList.remove('loading');
                const originalText = this.submitButton.getAttribute('data-original-text');
                if (originalText) {
                    this.submitButton.textContent = originalText;
                }
            }
        }
    }

    reset() {
        this.form.reset();
        this.form.querySelectorAll('.is-invalid').forEach(field => {
            this.clearFieldError(field);
        });
    }

    getFormData() {
        const formData = new FormData(this.form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }
        
        return data;
    }

    // Utilità di validazione
    isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    isValidFiscalCode(fiscalCode) {
        if (fiscalCode.length !== 16) return false;
        
        const re = /^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$/;
        return re.test(fiscalCode.toUpperCase());
    }

    isValidPhone(phone) {
        const re = /^[\+]?[0-9\s\-\(\)]{8,}$/;
        return re.test(phone);
    }
}

/**
 * Utilità generali
 */
const Utils = {
    // Formattazione valuta
    formatCurrency(amount, currency = 'EUR') {
        return new Intl.NumberFormat('it-IT', {
            style: 'currency',
            currency: currency
        }).format(amount);
    },

    // Formattazione data
    formatDate(date, options = {}) {
        const defaultOptions = {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        
        return new Intl.DateTimeFormat('it-IT', {
            ...defaultOptions,
            ...options
        }).format(new Date(date));
    },

    // Formattazione data e ora
    formatDateTime(date) {
        return new Intl.DateTimeFormat('it-IT', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }).format(new Date(date));
    },

    // Capitalizza prima lettera
    capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    },

    // Debounce function
    debounce(func, wait, immediate) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                timeout = null;
                if (!immediate) func(...args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func(...args);
        };
    },

    // Throttle function
    throttle(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },

    // Genera ID unico
    generateId() {
        return Math.random().toString(36).substr(2, 9);
    },

    // Escape HTML
    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
};

/**
 * Funzioni globali per l'interfaccia
 */

// Mostra/nascondi password
function togglePassword(inputId) {
    const input = inputId ? document.getElementById(inputId) : 
        document.getElementById('password');
    const button = input.parentNode.querySelector('.toggle-password');
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fas fa-eye';
    }
}

// Mostra modal
function showModal(modalId) {
    ModalManager.show(modalId);
}

// Chiudi modal
function closeModal(modalId) {
    ModalManager.hide(modalId);
}

// Chiudi tutti i modal quando si clicca fuori
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal')) {
        ModalManager.hideAll();
    }
});

// Chiudi modal con ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        ModalManager.hideAll();
    }
});

// Inizializzazione al caricamento della pagina
document.addEventListener('DOMContentLoaded', () => {
    debug('DOM loaded, initializing...');
    
    // Inizializza tutti i form
    document.querySelectorAll('form').forEach(form => {
        new FormManager(form);
    });
    
    // Inizializza tooltips (se presente una libreria)
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    }
});

// Gestione errori globali
window.addEventListener('error', (e) => {
    debug('Global error:', e.error);
    if (CONFIG.DEBUG) {
        notifications.error('Si è verificato un errore inaspettato. Ricarica la pagina e riprova.');
    }
});

// Gestione promise rejection non gestite
window.addEventListener('unhandledrejection', (e) => {
    debug('Unhandled promise rejection:', e.reason);
    if (CONFIG.DEBUG) {
        notifications.error('Si è verificato un errore di rete. Controlla la connessione e riprova.');
    }
});

// Export per uso in altri file
window.AppUtils = {
    api,
    notifications,
    ModalManager,
    FormManager,
    Utils,
    debug
};