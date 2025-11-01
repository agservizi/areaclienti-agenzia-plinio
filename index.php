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
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="assets/images/logo.png" alt="Agenzia Plinio" class="logo">
                <h1>Area Clienti</h1>
                <p>Accedi al tuo account per gestire i tuoi servizi</p>
            </div>

            <form id="loginForm" class="login-form">
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i>
                        Username o Email
                    </label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="checkbox-container">
                        <input type="checkbox" id="remember" name="remember">
                        <span class="checkmark"></span>
                        Ricordami
                    </label>
                    <a href="#" class="forgot-password" onclick="showForgotPassword()">Password dimenticata?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-sign-in-alt"></i>
                    Accedi
                </button>

                <div class="login-divider">
                    <span>oppure</span>
                </div>

                <button type="button" class="btn btn-secondary btn-full" onclick="showRegister()">
                    <i class="fas fa-user-plus"></i>
                    Registrati come nuovo cliente
                </button>
            </form>


            <div id="alert-container"></div>
        </div>

        <aside class="login-info-panel">
            <div class="panel-header">
                <span class="badge-soft">Servizi premium</span>
                <h2>La tua Agenzia digitale di fiducia</h2>
                <p>Gestisci spedizioni, attivazioni digitali e pratiche CAF in modo semplice e sempre aggiornato.</p>
            </div>

            <div class="panel-stats">
                <div class="stat-card">
                    <div class="stat-value">24/7</div>
                    <span class="stat-label">Area clienti sempre disponibile</span>
                </div>
                <div class="stat-card">
                    <div class="stat-value">+1200</div>
                    <span class="stat-label">Pratiche gestite ogni anno</span>
                </div>
                <div class="stat-card">
                    <div class="stat-value">100%</div>
                    <span class="stat-label">Assistenza dedicata</span>
                </div>
            </div>

            <div class="panel-features">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <div class="feature-content">
                        <h3>Spedizioni Smart</h3>
                        <p>Richiedi ritiro pacchi, stampa etichette e traccia le consegne direttamente dal tuo profilo.</p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-digital-tachograph"></i>
                    </div>
                    <div class="feature-content">
                        <h3>Attivazioni Digitali</h3>
                        <p>SPID, PEC e firma digitale con notifiche in tempo reale sullo stato della pratica.</p>
                    </div>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="feature-content">
                        <h3>CAF & Patronato</h3>
                        <p>Invia documenti, verifica appuntamenti e monitora le tue richieste fiscali senza code.</p>
                    </div>
                </div>
            </div>

            <div class="panel-footer">
                <div class="contact-block">
                    <i class="fas fa-headset"></i>
                    <div>
                        <span class="contact-label">Serve aiuto?</span>
                        <a href="tel:+390815678321" class="contact-value">081 567 8321</a>
                    </div>
                </div>
                <div class="contact-block">
                    <i class="fas fa-envelope-open-text"></i>
                    <div>
                        <span class="contact-label">Scrivici a</span>
                        <a href="mailto:assistenza@agenziaplinio.it" class="contact-value">assistenza@agenziaplinio.it</a>
                    </div>
                </div>
            </div>
        </aside>
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