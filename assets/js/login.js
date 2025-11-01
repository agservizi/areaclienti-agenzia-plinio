/**
 * JavaScript per la gestione del login e registrazione
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeLoginPage();
});

function initializeLoginPage() {
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const forgotForm = document.getElementById('forgotForm');

    // Gestione form di login
    if (loginForm) {
        const loginManager = new AppUtils.FormManager(loginForm);
        
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (!loginManager.validateForm()) {
                return;
            }

            const formData = loginManager.getFormData();
            await handleLogin(formData, loginManager);
        });
    }

    // Gestione form di registrazione
    if (registerForm) {
        const registerManager = new AppUtils.FormManager(registerForm);
        
        registerForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (!validateRegistrationForm(registerManager)) {
                return;
            }

            const formData = registerManager.getFormData();
            await handleRegistration(formData, registerManager);
        });
    }

    // Gestione form recupero password
    if (forgotForm) {
        const forgotManager = new AppUtils.FormManager(forgotForm);
        
        forgotForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (!forgotManager.validateForm()) {
                return;
            }

            const formData = forgotManager.getFormData();
            await handleForgotPassword(formData, forgotManager);
        });
    }

    // Auto-focus sul primo campo
    const firstInput = document.querySelector('#loginForm input[type="text"], #loginForm input[type="email"]');
    if (firstInput) {
        setTimeout(() => firstInput.focus(), 100);
    }
}

/**
 * Gestisce il login
 */
async function handleLogin(formData, formManager) {
    try {
        formManager.setSubmitting(true);
        clearAlerts();

        AppUtils.debug('Attempting login for:', formData.username);

        const response = await AppUtils.api.post('auth/login.php', {
            username: formData.username,
            password: formData.password,
            remember: formData.remember || false
        });

        if (response.success) {
            AppUtils.notifications.success('Login effettuato con successo!');
            
            // Reindirizza in base al tipo di utente
            const redirectUrl = response.user.user_type === 'admin' ? 
                'admin/dashboard.php' : 'pages/dashboard.php';
            
            setTimeout(() => {
                window.location.href = redirectUrl;
            }, 1000);
        } else {
            showAlert(response.error || 'Errore durante il login', 'danger');
        }

    } catch (error) {
        AppUtils.debug('Login error:', error);
        showAlert(error.message || 'Errore di connessione. Riprova più tardi.', 'danger');
    } finally {
        formManager.setSubmitting(false);
    }
}

/**
 * Gestisce la registrazione
 */
async function handleRegistration(formData, formManager) {
    try {
        formManager.setSubmitting(true);
        clearAlerts();

        AppUtils.debug('Attempting registration for:', formData.username);

        const response = await AppUtils.api.post('auth/register.php', formData);

        if (response.success) {
            AppUtils.notifications.success('Registrazione completata! Puoi ora effettuare il login.');
            AppUtils.ModalManager.hide('registerModal');
            formManager.reset();
            
            // Pre-compila il form di login
            document.getElementById('username').value = formData.username;
            document.getElementById('password').focus();
        } else {
            showAlert(response.error || 'Errore durante la registrazione', 'danger');
        }

    } catch (error) {
        AppUtils.debug('Registration error:', error);
        showAlert(error.message || 'Errore di connessione. Riprova più tardi.', 'danger');
    } finally {
        formManager.setSubmitting(false);
    }
}

/**
 * Gestisce il recupero password
 */
async function handleForgotPassword(formData, formManager) {
    try {
        formManager.setSubmitting(true);
        
        AppUtils.debug('Password reset request for:', formData.email);

        const response = await AppUtils.api.post('auth/forgot-password.php', {
            email: formData.email
        });

        if (response.success) {
            AppUtils.notifications.success('Email di recupero inviata! Controlla la tua casella di posta.');
            AppUtils.ModalManager.hide('forgotModal');
            formManager.reset();
        } else {
            showAlert(response.error || 'Errore durante il recupero password', 'danger');
        }

    } catch (error) {
        AppUtils.debug('Forgot password error:', error);
        showAlert(error.message || 'Errore di connessione. Riprova più tardi.', 'danger');
    } finally {
        formManager.setSubmitting(false);
    }
}

/**
 * Validazione specifica per il form di registrazione
 */
function validateRegistrationForm(formManager) {
    let isValid = formManager.validateForm();
    
    // Validazione conferma password
    const password = document.getElementById('reg_password').value;
    const confirmPassword = document.getElementById('reg_confirm_password').value;
    
    if (password !== confirmPassword) {
        formManager.showFieldError(
            document.getElementById('reg_confirm_password'),
            'Le password non coincidono'
        );
        isValid = false;
    }

    // Validazione privacy policy
    const privacyCheckbox = document.getElementById('reg_privacy');
    if (!privacyCheckbox.checked) {
        showAlert('Devi accettare la Privacy Policy per registrarti', 'warning');
        isValid = false;
    }

    // Validazione codice fiscale (se inserito)
    const fiscalCode = document.getElementById('reg_fiscal_code').value.trim();
    if (fiscalCode && !formManager.isValidFiscalCode(fiscalCode)) {
        formManager.showFieldError(
            document.getElementById('reg_fiscal_code'),
            'Codice fiscale non valido'
        );
        isValid = false;
    }

    return isValid;
}

/**
 * Mostra modal di registrazione
 */
function showRegister() {
    AppUtils.ModalManager.show('registerModal');
}

/**
 * Mostra modal recupero password
 */
function showForgotPassword() {
    AppUtils.ModalManager.show('forgotModal');
}

/**
 * Mostra un alert nella pagina
 */
function showAlert(message, type = 'info') {
    const alertContainer = document.getElementById('alert-container');
    if (!alertContainer) return;

    const alertId = 'alert-' + Date.now();
    const alertElement = document.createElement('div');
    alertElement.id = alertId;
    alertElement.className = `alert alert-${type} alert-dismissible`;
    alertElement.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="${getAlertIcon(type)} me-2"></i>
            <span>${AppUtils.Utils.escapeHtml(message)}</span>
            <button type="button" class="close ms-auto" onclick="removeAlert('${alertId}')">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;

    alertContainer.appendChild(alertElement);

    // Animazione di entrata
    setTimeout(() => alertElement.classList.add('show'), 10);

    // Rimozione automatica dopo 8 secondi
    setTimeout(() => removeAlert(alertId), 8000);
}

/**
 * Rimuove un alert specifico
 */
function removeAlert(alertId) {
    const alert = document.getElementById(alertId);
    if (alert) {
        alert.classList.remove('show');
        setTimeout(() => alert.remove(), 300);
    }
}

/**
 * Pulisce tutti gli alert
 */
function clearAlerts() {
    const alertContainer = document.getElementById('alert-container');
    if (alertContainer) {
        alertContainer.innerHTML = '';
    }
}

/**
 * Restituisce l'icona per il tipo di alert
 */
function getAlertIcon(type) {
    const icons = {
        success: 'fas fa-check-circle',
        danger: 'fas fa-exclamation-circle',
        warning: 'fas fa-exclamation-triangle',
        info: 'fas fa-info-circle'
    };
    return icons[type] || icons.info;
}

/**
 * Gestione avanzata della password
 */
function setupPasswordStrengthIndicator() {
    const passwordInput = document.getElementById('reg_password');
    if (!passwordInput) return;

    // Crea l'indicatore di forza
    const strengthIndicator = document.createElement('div');
    strengthIndicator.className = 'password-strength';
    strengthIndicator.innerHTML = `
        <div class="strength-bar">
            <div class="strength-fill"></div>
        </div>
        <div class="strength-text">Inserisci una password</div>
    `;
    
    passwordInput.parentNode.appendChild(strengthIndicator);

    // Listener per calcolare la forza
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        const strength = calculatePasswordStrength(password);
        updatePasswordStrengthIndicator(strengthIndicator, strength);
    });
}

/**
 * Calcola la forza della password
 */
function calculatePasswordStrength(password) {
    let score = 0;
    let feedback = [];

    if (password.length === 0) {
        return { score: 0, text: 'Inserisci una password', class: '' };
    }

    // Lunghezza
    if (password.length >= 8) score += 1;
    else feedback.push('almeno 8 caratteri');

    if (password.length >= 12) score += 1;

    // Caratteri minuscoli
    if (/[a-z]/.test(password)) score += 1;
    else feedback.push('lettere minuscole');

    // Caratteri maiuscoli
    if (/[A-Z]/.test(password)) score += 1;
    else feedback.push('lettere maiuscole');

    // Numeri
    if (/[0-9]/.test(password)) score += 1;
    else feedback.push('numeri');

    // Caratteri speciali
    if (/[^A-Za-z0-9]/.test(password)) score += 1;
    else feedback.push('caratteri speciali');

    // Determina il livello
    let level, text, className;
    
    if (score < 3) {
        level = 'weak';
        text = 'Debole' + (feedback.length ? ': aggiungi ' + feedback.join(', ') : '');
        className = 'strength-weak';
    } else if (score < 5) {
        level = 'medium';
        text = 'Media' + (feedback.length ? ': migliora con ' + feedback.join(', ') : '');
        className = 'strength-medium';
    } else {
        level = 'strong';
        text = 'Forte';
        className = 'strength-strong';
    }

    return {
        score: Math.min(score, 6),
        level,
        text,
        class: className
    };
}

/**
 * Aggiorna l'indicatore di forza della password
 */
function updatePasswordStrengthIndicator(indicator, strength) {
    const fill = indicator.querySelector('.strength-fill');
    const text = indicator.querySelector('.strength-text');
    
    const percentage = (strength.score / 6) * 100;
    
    fill.style.width = percentage + '%';
    fill.className = `strength-fill ${strength.class}`;
    text.textContent = strength.text;
    text.className = `strength-text ${strength.class}`;
}

// Inizializza l'indicatore di forza della password quando il modal si apre
document.addEventListener('click', function(e) {
    if (e.target.closest('[onclick*="showRegister"]')) {
        setTimeout(setupPasswordStrengthIndicator, 100);
    }
});

/**
 * Gestione "Ricordami"
 */
function handleRememberMe() {
    const rememberCheckbox = document.getElementById('remember');
    if (!rememberCheckbox) return;

    // Carica stato salvato
    const savedRemember = localStorage.getItem('rememberMe');
    if (savedRemember === 'true') {
        rememberCheckbox.checked = true;
        
        // Carica username salvato
        const savedUsername = localStorage.getItem('savedUsername');
        if (savedUsername) {
            document.getElementById('username').value = savedUsername;
            document.getElementById('password').focus();
        }
    }

    // Salva stato quando cambia
    rememberCheckbox.addEventListener('change', function() {
        localStorage.setItem('rememberMe', this.checked);
        
        if (!this.checked) {
            localStorage.removeItem('savedUsername');
        }
    });

    // Salva username al login riuscito
    window.addEventListener('beforeunload', function() {
        const rememberCheckbox = document.getElementById('remember');
        const usernameInput = document.getElementById('username');
        
        if (rememberCheckbox && rememberCheckbox.checked && usernameInput) {
            localStorage.setItem('savedUsername', usernameInput.value);
        }
    });
}

// Inizializza "Ricordami"
document.addEventListener('DOMContentLoaded', handleRememberMe);

/**
 * Gestione dei tasti di scelta rapida
 */
document.addEventListener('keydown', function(e) {
    // Ctrl+Enter per submitare il form
    if (e.ctrlKey && e.key === 'Enter') {
        const activeForm = document.querySelector('form:not([style*="display: none"])');
        if (activeForm) {
            const submitButton = activeForm.querySelector('button[type="submit"]');
            if (submitButton && !submitButton.disabled) {
                submitButton.click();
            }
        }
    }
    
    // Alt+R per aprire registrazione
    if (e.altKey && e.key === 'r') {
        e.preventDefault();
        showRegister();
    }
    
    // Alt+F per recupero password
    if (e.altKey && e.key === 'f') {
        e.preventDefault();
        showForgotPassword();
    }
});

// Logging per debug
AppUtils.debug('Login page JavaScript loaded');

// Controllo se l'utente è già loggato
checkExistingSession();

async function checkExistingSession() {
    try {
        const response = await AppUtils.api.get('auth/check-session.php');
        if (response.success && response.authenticated) {
            AppUtils.debug('User already authenticated, redirecting...');
            const redirectUrl = response.user.user_type === 'admin' ? 
                'admin/dashboard.php' : 'pages/dashboard.php';
            window.location.href = redirectUrl;
        }
    } catch (error) {
        // Ignora gli errori del controllo sessione
        AppUtils.debug('Session check failed:', error);
    }
}