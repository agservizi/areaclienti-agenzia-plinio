<?php
/**
 * Dashboard principale per i clienti
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();

// Controllo autenticazione
if (!$auth->isAuthenticated() || !$auth->isClient()) {
    header('Location: ../index.php');
    exit;
}

$user = $auth->getCurrentUser();
$title = 'Dashboard Cliente';

include __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-container">
    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="sidebar-header">
            <img src="../assets/images/logo.png" alt="Agenzia Plinio" class="sidebar-logo">
            <h3>Area Clienti</h3>
        </div>
        
        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="user-details">
                <h4><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h4>
                <p><?= htmlspecialchars($user['email']) ?></p>
            </div>
        </div>

        <ul class="sidebar-menu">
            <li class="menu-item active">
                <a href="dashboard.php">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="menu-section">
                <span>Servizi Disponibili</span>
            </li>
            
            <li class="menu-item">
                <a href="spedizioni.php">
                    <i class="fas fa-shipping-fast"></i>
                    <span>Spedizioni</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="attivazioni-digitali.php">
                    <i class="fas fa-digital-tachograph"></i>
                    <span>Attivazioni Digitali</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="caf-patronato.php">
                    <i class="fas fa-file-alt"></i>
                    <span>CAF e Patronato</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="visure.php">
                    <i class="fas fa-search"></i>
                    <span>Visure</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="telefonia-utenze.php">
                    <i class="fas fa-phone"></i>
                    <span>Telefonia e Utenze</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="servizi-postali.php">
                    <i class="fas fa-envelope"></i>
                    <span>Servizi Postali</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="ritiro-pacchi.php">
                    <i class="fas fa-box"></i>
                    <span>Ritiro Pacchi</span>
                </a>
            </li>
            
            <li class="menu-section">
                <span>Account</span>
            </li>
            
            <li class="menu-item">
                <a href="profilo.php">
                    <i class="fas fa-user-cog"></i>
                    <span>Il Mio Profilo</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="richieste.php">
                    <i class="fas fa-list"></i>
                    <span>Le Mie Richieste</span>
                </a>
            </li>
            
            <li class="menu-item">
                <a href="notifiche.php">
                    <i class="fas fa-bell"></i>
                    <span>Notifiche</span>
                    <span class="badge">3</span>
                </a>
            </li>
            
            <li class="menu-item logout">
                <a href="#" onclick="handleLogout()">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="page-header">
            <div class="header-left">
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1>Dashboard</h1>
            </div>
            
            <div class="header-right">
                <button class="btn btn-primary" onclick="showNewRequestModal()">
                    <i class="fas fa-plus"></i>
                    Nuova Richiesta
                </button>
                
                <div class="notification-bell" onclick="toggleNotifications()">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </div>
                
                <div class="user-menu">
                    <img src="../assets/images/default-avatar.png" alt="Avatar" class="user-avatar-small">
                </div>
            </div>
        </header>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <!-- Statistiche rapide -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clipboard-list text-primary"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="totalRequests">0</h3>
                        <p>Richieste Totali</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock text-warning"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="pendingRequests">0</h3>
                        <p>In Elaborazione</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="completedRequests">0</h3>
                        <p>Completate</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shipping-fast text-info"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="activeShipments">0</h3>
                        <p>Spedizioni Attive</p>
                    </div>
                </div>
            </div>

            <!-- Servizi più utilizzati -->
            <div class="row mt-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-star text-warning"></i> Servizi Più Utilizzati</h3>
                        </div>
                        <div class="card-body">
                            <div class="services-grid">
                                <div class="service-card" onclick="redirectToService('spedizioni')">
                                    <div class="service-icon">
                                        <i class="fas fa-shipping-fast"></i>
                                    </div>
                                    <h4>Spedizioni</h4>
                                    <p>Spedisci pacchi con BRT, Poste e corrieri</p>
                                    <div class="service-stats">
                                        <span class="usage-count">12 richieste</span>
                                    </div>
                                </div>
                                
                                <div class="service-card" onclick="redirectToService('attivazioni-digitali')">
                                    <div class="service-icon">
                                        <i class="fas fa-digital-tachograph"></i>
                                    </div>
                                    <h4>Attivazioni Digitali</h4>
                                    <p>SPID, PEC e Firma Digitale</p>
                                    <div class="service-stats">
                                        <span class="usage-count">8 richieste</span>
                                    </div>
                                </div>
                                
                                <div class="service-card" onclick="redirectToService('caf-patronato')">
                                    <div class="service-icon">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <h4>CAF e Patronato</h4>
                                    <p>Pratiche fiscali e previdenziali</p>
                                    <div class="service-stats">
                                        <span class="usage-count">5 richieste</span>
                                    </div>
                                </div>
                                
                                <div class="service-card" onclick="redirectToService('visure')">
                                    <div class="service-icon">
                                        <i class="fas fa-search"></i>
                                    </div>
                                    <h4>Visure</h4>
                                    <p>Visure catastali, camerali, CRIF</p>
                                    <div class="service-stats">
                                        <span class="usage-count">3 richieste</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-bell text-info"></i> Notifiche Recenti</h3>
                        </div>
                        <div class="card-body">
                            <div class="notifications-list" id="recentNotifications">
                                <!-- Le notifiche verranno caricate via JavaScript -->
                                <div class="loading-placeholder">
                                    <i class="fas fa-spinner fa-spin"></i>
                                    Caricamento notifiche...
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <a href="notifiche.php" class="btn btn-outline-primary btn-sm">
                                    Vedi tutte le notifiche
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Richieste recenti -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-history text-secondary"></i> Richieste Recenti</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="recentRequestsTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Servizio</th>
                                            <th>Titolo</th>
                                            <th>Stato</th>
                                            <th>Data</th>
                                            <th>Azioni</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recentRequestsBody">
                                        <!-- I dati verranno caricati via JavaScript -->
                                        <tr>
                                            <td colspan="6" class="text-center">
                                                <i class="fas fa-spinner fa-spin"></i>
                                                Caricamento richieste...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-center mt-3">
                                <a href="richieste.php" class="btn btn-outline-primary">
                                    Vedi tutte le richieste
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal Nuova Richiesta -->
<div id="newRequestModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h2><i class="fas fa-plus"></i> Nuova Richiesta di Servizio</h2>
            <span class="close" onclick="closeModal('newRequestModal')">&times;</span>
        </div>
        <div class="modal-body">
            <div class="services-selection">
                <div class="service-option" onclick="createNewRequest('spedizioni')">
                    <i class="fas fa-shipping-fast"></i>
                    <h4>Spedizioni</h4>
                    <p>Spedizioni nazionali e internazionali</p>
                </div>
                
                <div class="service-option" onclick="createNewRequest('attivazioni-digitali')">
                    <i class="fas fa-digital-tachograph"></i>
                    <h4>Attivazioni Digitali</h4>
                    <p>SPID, PEC, Firma Digitale</p>
                </div>
                
                <div class="service-option" onclick="createNewRequest('caf-patronato')">
                    <i class="fas fa-file-alt"></i>
                    <h4>CAF e Patronato</h4>
                    <p>Pratiche fiscali e previdenziali</p>
                </div>
                
                <div class="service-option" onclick="createNewRequest('visure')">
                    <i class="fas fa-search"></i>
                    <h4>Visure</h4>
                    <p>Visure catastali, camerali, CRIF</p>
                </div>
                
                <div class="service-option" onclick="createNewRequest('telefonia-utenze')">
                    <i class="fas fa-phone"></i>
                    <h4>Telefonia e Utenze</h4>
                    <p>Contratti luce, gas, telefonia</p>
                </div>
                
                <div class="service-option" onclick="createNewRequest('servizi-postali')">
                    <i class="fas fa-envelope"></i>
                    <h4>Servizi Postali</h4>
                    <p>Invio email e PEC</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/dashboard.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>