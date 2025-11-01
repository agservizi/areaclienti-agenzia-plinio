<?php
/**
 * Sidebar per l'area clienti
 */

$currentPage = basename($_SERVER['PHP_SELF']);
?>

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
        <li class="menu-item <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
            <a href="dashboard.php">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </li>
        
        <li class="menu-section">
            <span>Servizi Disponibili</span>
        </li>
        
        <li class="menu-item <?= $currentPage === 'spedizioni.php' ? 'active' : '' ?>">
            <a href="spedizioni.php">
                <i class="fas fa-shipping-fast"></i>
                <span>Spedizioni</span>
            </a>
        </li>
        
        <li class="menu-item <?= $currentPage === 'pagamenti.php' ? 'active' : '' ?>">
            <a href="pagamenti.php">
                <i class="fas fa-credit-card"></i>
                <span>Pagamenti</span>
            </a>
        </li>
        
        <li class="menu-item <?= $currentPage === 'biglietteria.php' ? 'active' : '' ?>">
            <a href="biglietteria.php">
                <i class="fas fa-train"></i>
                <span>Biglietteria</span>
            </a>
        </li>
        
        <li class="menu-item <?= $currentPage === 'attivazioni-digitali.php' ? 'active' : '' ?>">
            <a href="attivazioni-digitali.php">
                <i class="fas fa-digital-tachograph"></i>
                <span>Attivazioni Digitali</span>
            </a>
        </li>
        
        <li class="menu-item <?= $currentPage === 'caf-patronato.php' ? 'active' : '' ?>">
            <a href="caf-patronato.php">
                <i class="fas fa-file-alt"></i>
                <span>CAF e Patronato</span>
            </a>
        </li>
        
        <li class="menu-item <?= $currentPage === 'visure.php' ? 'active' : '' ?>">
            <a href="visure.php">
                <i class="fas fa-search"></i>
                <span>Visure</span>
            </a>
        </li>
        
        <li class="menu-item <?= $currentPage === 'telefonia-utenze.php' ? 'active' : '' ?>">
            <a href="telefonia-utenze.php">
                <i class="fas fa-phone"></i>
                <span>Telefonia e Utenze</span>
            </a>
        </li>
        
        <li class="menu-item <?= $currentPage === 'servizi-postali.php' ? 'active' : '' ?>">
            <a href="servizi-postali.php">
                <i class="fas fa-envelope"></i>
                <span>Servizi Postali</span>
            </a>
        </li>
        
        <li class="menu-item <?= $currentPage === 'ritiro-pacchi.php' ? 'active' : '' ?>">
            <a href="ritiro-pacchi.php">
                <i class="fas fa-box"></i>
                <span>Ritiro Pacchi</span>
            </a>
        </li>
        
        <li class="menu-section">
            <span>Account</span>
        </li>
        
        <li class="menu-item <?= $currentPage === 'profilo.php' ? 'active' : '' ?>">
            <a href="profilo.php">
                <i class="fas fa-user-cog"></i>
                <span>Il Mio Profilo</span>
            </a>
        </li>
        
        <li class="menu-item <?= $currentPage === 'richieste.php' ? 'active' : '' ?>">
            <a href="richieste.php">
                <i class="fas fa-list"></i>
                <span>Le Mie Richieste</span>
            </a>
        </li>
        
        <li class="menu-item <?= $currentPage === 'notifiche.php' ? 'active' : '' ?>">
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