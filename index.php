<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Area Clienti Agenzia Plinio</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="login-page">
    <div class="auth-layout">
        <aside class="auth-side">
            <div class="auth-side-header">
                <img src="assets/images/logo.png" alt="Agenzia Plinio" class="brand-logo">
                <div class="brand-tagline">
                    <span class="eyebrow">Area Clienti</span>
                    <h2>Il tuo punto di accesso unico</h2>
                </div>
            </div>

            <div class="auth-side-body">
                <p class="side-intro">Gestisci spedizioni, attivazioni digitali e pratiche CAF in un’unica piattaforma con assistenza dedicata.</p>

                <div class="side-metrics">
                    <div class="metric">
                        <span class="metric-value">24/7</span>
                        <span class="metric-label">Portale sempre attivo</span>
                    </div>
                    <div class="metric">
                        <span class="metric-value">+1200</span>
                        <span class="metric-label">Pratiche gestite l’anno</span>
                    </div>
                    <div class="metric">
                        <span class="metric-value">4.8/5</span>
                        <span class="metric-label">Soddisfazione clienti</span>
                    </div>
                </div>

                <div class="side-highlights">
                    <div class="highlight-item">
                        <i class="fas fa-shipping-fast"></i>
                        <div>
                            <h3>Spedizioni integrate</h3>
                            <p>Ritiro, monitoraggio e documenti tutto online, con notifiche automatiche.</p>
                        </div>
                    </div>
                    <div class="highlight-item">
                        <i class="fas fa-digital-tachograph"></i>
                        <div>
                            <h3>Identità digitale</h3>
                            <p>SPID, PEC e firma digitale con workflow guidati e reminder intelligenti.</p>
                        </div>
                    </div>
                    <div class="highlight-item">
                        <i class="fas fa-file-alt"></i>
                        <div>
                            <h3>CAF & Patronato</h3>
                            <p>Carica documenti, prenota appuntamenti e segui le tue pratiche in tempo reale.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="auth-side-footer">
                <div>
                    <span class="footer-label">Assistenza dedicata</span>
                    <a href="tel:+390815678321" class="footer-value">081 567 8321</a>
                </div>
                <div>
                    <span class="footer-label">Scrivici</span>
                    <a href="mailto:assistenza@agenziaplinio.it" class="footer-value">assistenza@agenziaplinio.it</a>
                </div>
            </div>
        </aside>

        <main class="auth-content">
            <div class="auth-content-header">
                <span class="eyebrow">Accesso clienti registrati</span>
                <h1>Benvenuto in Agenzia Plinio</h1>
                <p>Accedi per gestire servizi, richieste e aggiornamenti in modo rapido e centralizzato.</p>
            </div>

            <form id="loginForm" class="auth-form">
                <div class="form-field">
                    <label for="username">
                        <i class="fas fa-user"></i>
                        Username o Email
                    </label>
                    <input type="text" id="username" name="username" autocomplete="username" required>
                </div>

                <div class="form-field">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" autocomplete="current-password" required>
                        <button type="button" class="toggle-password" aria-label="Mostra password" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-meta">
                    <label class="checkbox-container">
                        <input type="checkbox" id="remember" name="remember">
                        <span class="checkmark"></span>
                        Ricordami
                    </label>
                    <button type="button" class="link-button" onclick="showForgotPassword()">Password dimenticata?</button>
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-sign-in-alt"></i>
                    Accedi al portale
                </button>

                <div class="form-divider">
                    <span>Oppure</span>
                </div>

                <button type="button" class="btn btn-secondary btn-full" onclick="showRegister()">
                    <i class="fas fa-user-plus"></i>
                    Richiedi credenziali cliente
                </button>
            </form>

            <div id="alert-container" aria-live="polite"></div>

            <div class="auth-footer-links">
                <span>Problemi con l’accesso? <a href="mailto:assistenza@agenziaplinio.it">Contatta l’assistenza</a></span>
                <span>Consulta la <a href="#">Privacy Policy</a></span>
            </div>
        </main>
    </div>

    <!-- Modal Registrazione -->
    <div id="registerModal" class="modal">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2><i class="fas fa-user-plus"></i> Registrazione Cliente</h2>
                                <span class="close" onclick="closeModal('registerModal')">&times;</span>
                            </div>
                            <form id="registerForm" class="modal-form">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="reg_first_name">Nome *</label>
                                        <input type="text" id="reg_first_name" name="first_name" required>
                                    </div>
                                    <div class="form-group">
                                        <label for="reg_last_name">Cognome *</label>
                                        <input type="text" id="reg_last_name" name="last_name" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="reg_username">Username *</label>
                                    <input type="text" id="reg_username" name="username" required>
                                </div>

                                <div class="form-group">
                                    <label for="reg_email">Email *</label>
                                    <input type="email" id="reg_email" name="email" required>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="reg_password">Password *</label>
                                        <input type="password" id="reg_password" name="password" required minlength="8">
                                    </div>
                                    <div class="form-group">
                                        <label for="reg_confirm_password">Conferma Password *</label>
                                        <input type="password" id="reg_confirm_password" name="confirm_password" required>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="reg_phone">Telefono</label>
                                    <input type="tel" id="reg_phone" name="phone">
                                </div>

                                <div class="form-group">
                                    <label for="reg_fiscal_code">Codice Fiscale</label>
                                    <input type="text" id="reg_fiscal_code" name="fiscal_code" maxlength="16">
                                </div>

                                <div class="form-group">
                                    <label for="reg_address">Indirizzo</label>
                                    <textarea id="reg_address" name="address" rows="2"></textarea>
                                </div>

                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="reg_city">Città</label>
                                        <input type="text" id="reg_city" name="city">
                                    </div>
                                    <div class="form-group">
                                        <label for="reg_postal_code">CAP</label>
                                        <input type="text" id="reg_postal_code" name="postal_code" maxlength="5">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="checkbox-container">
                                        <input type="checkbox" id="reg_privacy" name="privacy" required>
                                        <span class="checkmark"></span>
                                        Accetto la <a href="#" target="_blank">Privacy Policy</a> *
                                    </label>
                                </div>

                                <div class="modal-buttons">
                                    <button type="button" class="btn btn-secondary" onclick="closeModal('registerModal')">Annulla</button>
                                    <button type="submit" class="btn btn-primary">Registrati</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Modal Password Dimenticata -->
                    <div id="forgotModal" class="modal">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h2><i class="fas fa-key"></i> Recupero Password</h2>
                                <span class="close" onclick="closeModal('forgotModal')">&times;</span>
                            </div>
                            <form id="forgotForm" class="modal-form">
                                <p>Inserisci la tua email per ricevere le istruzioni per il reset della password.</p>
                
                                <div class="form-group">
                                    <label for="forgot_email">Email</label>
                                    <input type="email" id="forgot_email" name="email" required>
                                </div>

                                <div class="modal-buttons">
                                    <button type="button" class="btn btn-secondary" onclick="closeModal('forgotModal')">Annulla</button>
                                    <button type="submit" class="btn btn-primary">Invia Email</button>
                                </div>
                            </form>
                        </div>
                    </div>

    <script src="assets/js/common.js"></script>
    <script src="assets/js/login.js"></script>
</body>
</html>