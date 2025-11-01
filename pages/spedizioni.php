<?php
/**
 * Pagina per la gestione delle spedizioni
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
$title = 'Spedizioni';
$pageCSS = ['../assets/css/services.css'];
$pageJS = ['../assets/js/spedizioni.js'];

include __DIR__ . '/../includes/header.php';
?>

<div class="dashboard-container">
    <!-- Sidebar -->
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="page-header">
            <div class="header-left">
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1><i class="fas fa-shipping-fast text-primary"></i> Spedizioni</h1>
            </div>
            
            <div class="header-right">
                <button class="btn btn-primary" onclick="showNewShipmentModal()">
                    <i class="fas fa-plus"></i>
                    Nuova Spedizione
                </button>
            </div>
        </header>

        <!-- Content -->
        <div class="dashboard-content">
            <!-- Filtri e ricerca -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="statusFilter">Stato</label>
                                <select id="statusFilter" class="form-control" onchange="filterShipments()">
                                    <option value="">Tutti gli stati</option>
                                    <option value="pending">In Attesa</option>
                                    <option value="picked_up">Ritirato</option>
                                    <option value="in_transit">In Transito</option>
                                    <option value="delivered">Consegnato</option>
                                    <option value="failed">Fallito</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="carrierFilter">Corriere</label>
                                <select id="carrierFilter" class="form-control" onchange="filterShipments()">
                                    <option value="">Tutti i corrieri</option>
                                    <option value="brt">BRT</option>
                                    <option value="poste">Poste Italiane</option>
                                    <option value="tnt">TNT/FedEx</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="searchQuery">Cerca</label>
                                <div class="input-group">
                                    <input type="text" id="searchQuery" class="form-control" 
                                           placeholder="Cerca per destinatario, città o tracking..." 
                                           onkeyup="searchShipments(this.value)">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" onclick="clearSearch()">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button class="btn btn-outline-primary btn-block" onclick="exportShipments()">
                                    <i class="fas fa-download"></i> Esporta
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista spedizioni -->
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Le Tue Spedizioni</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="shipmentsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tracking</th>
                                    <th>Corriere</th>
                                    <th>Destinatario</th>
                                    <th>Destinazione</th>
                                    <th>Stato</th>
                                    <th>Data Creazione</th>
                                    <th>Azioni</th>
                                </tr>
                            </thead>
                            <tbody id="shipmentsTableBody">
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <i class="fas fa-spinner fa-spin"></i>
                                        Caricamento spedizioni...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginazione -->
                    <nav aria-label="Paginazione spedizioni" id="shipmentsPagination" style="display: none;">
                        <ul class="pagination justify-content-center">
                            <!-- Generata dinamicamente -->
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal Nuova Spedizione -->
<div id="newShipmentModal" class="modal">
    <div class="modal-content modal-xl">
        <div class="modal-header">
            <h2><i class="fas fa-shipping-fast"></i> Nuova Spedizione</h2>
            <span class="close" onclick="closeModal('newShipmentModal')">&times;</span>
        </div>
        <form id="newShipmentForm" class="modal-form">
            <div class="modal-body">
                <!-- Step 1: Tipo di spedizione -->
                <div class="form-step active" id="step1">
                    <h3>1. Seleziona il tipo di spedizione</h3>
                    
                    <div class="shipping-types">
                        <div class="shipping-type-card" data-carrier="brt" onclick="selectCarrier('brt')">
                            <div class="carrier-logo">
                                <img src="../assets/images/brt-logo.png" alt="BRT" onerror="this.style.display='none'">
                                <i class="fas fa-truck"></i>
                            </div>
                            <h4>BRT</h4>
                            <p>Spedizioni nazionali ed europee</p>
                            <ul>
                                <li>Consegna entro 24-48h</li>
                                <li>Tracking in tempo reale</li>
                                <li>Assicurazione inclusa</li>
                            </ul>
                        </div>
                        
                        <div class="shipping-type-card" data-carrier="poste" onclick="selectCarrier('poste')">
                            <div class="carrier-logo">
                                <img src="../assets/images/poste-logo.png" alt="Poste Italiane" onerror="this.style.display='none'">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <h4>Poste Italiane</h4>
                            <p>Pacco ordinario e celere</p>
                            <ul>
                                <li>Consegna 2-5 giorni lavorativi</li>
                                <li>Copertura nazionale</li>
                                <li>Prezzi convenienti</li>
                            </ul>
                        </div>
                        
                        <div class="shipping-type-card" data-carrier="tnt" onclick="selectCarrier('tnt')">
                            <div class="carrier-logo">
                                <img src="../assets/images/tnt-logo.png" alt="TNT/FedEx" onerror="this.style.display='none'">
                                <i class="fas fa-plane"></i>
                            </div>
                            <h4>TNT/FedEx</h4>
                            <p>Spedizioni internazionali express</p>
                            <ul>
                                <li>Consegna express mondiale</li>
                                <li>Gestione dogane</li>
                                <li>Servizio premium</li>
                            </ul>
                        </div>
                    </div>
                    
                    <input type="hidden" id="selectedCarrier" name="carrier" required>
                </div>

                <!-- Step 2: Dati mittente -->
                <div class="form-step" id="step2">
                    <h3>2. Dati del mittente</h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sender_name">Nome e Cognome *</label>
                                <input type="text" id="sender_name" name="sender_name" class="form-control" 
                                       value="<?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sender_phone">Telefono *</label>
                                <input type="tel" id="sender_phone" name="sender_phone" class="form-control" 
                                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="sender_address">Indirizzo completo *</label>
                        <textarea id="sender_address" name="sender_address" class="form-control" rows="2" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="sender_city">Città *</label>
                                <input type="text" id="sender_city" name="sender_city" class="form-control" 
                                       value="<?= htmlspecialchars($user['city'] ?? '') ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="sender_postal_code">CAP *</label>
                                <input type="text" id="sender_postal_code" name="sender_postal_code" class="form-control" 
                                       value="<?= htmlspecialchars($user['postal_code'] ?? '') ?>" required maxlength="5">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Dati destinatario -->
                <div class="form-step" id="step3">
                    <h3>3. Dati del destinatario</h3>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="recipient_name">Nome e Cognome *</label>
                                <input type="text" id="recipient_name" name="recipient_name" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="recipient_phone">Telefono *</label>
                                <input type="tel" id="recipient_phone" name="recipient_phone" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="recipient_address">Indirizzo completo *</label>
                        <textarea id="recipient_address" name="recipient_address" class="form-control" rows="2" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="recipient_city">Città *</label>
                                <input type="text" id="recipient_city" name="recipient_city" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="recipient_postal_code">CAP *</label>
                                <input type="text" id="recipient_postal_code" name="recipient_postal_code" class="form-control" required maxlength="5">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Dettagli pacco -->
                <div class="form-step" id="step4">
                    <h3>4. Dettagli del pacco</h3>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="package_weight">Peso (kg) *</label>
                                <input type="number" id="package_weight" name="package_weight" class="form-control" 
                                       step="0.1" min="0.1" max="30" required>
                            </div>
                        </div>
                        
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="package_dimensions">Dimensioni (cm) - Lunghezza x Larghezza x Altezza</label>
                                <input type="text" id="package_dimensions" name="package_dimensions" class="form-control" 
                                       placeholder="es. 30x20x15">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="package_content">Contenuto del pacco *</label>
                        <textarea id="package_content" name="package_content" class="form-control" rows="3" 
                                  placeholder="Descrivi brevemente il contenuto del pacco..." required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="declared_value">Valore dichiarato (€)</label>
                                <input type="number" id="declared_value" name="declared_value" class="form-control" 
                                       step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="delivery_type">Tipo di consegna</label>
                                <select id="delivery_type" name="delivery_type" class="form-control">
                                    <option value="standard">Standard</option>
                                    <option value="express">Express (+10€)</option>
                                    <option value="same_day">Stesso giorno (+25€)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-container">
                            <input type="checkbox" id="insurance_requested" name="insurance_requested">
                            <span class="checkmark"></span>
                            Assicurazione aggiuntiva (+5€)
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label for="special_instructions">Istruzioni speciali</label>
                        <textarea id="special_instructions" name="special_instructions" class="form-control" rows="2" 
                                  placeholder="Note aggiuntive per il corriere..."></textarea>
                    </div>
                </div>

                <!-- Step 5: Riepilogo e conferma -->
                <div class="form-step" id="step5">
                    <h3>5. Riepilogo e conferma</h3>
                    
                    <div class="shipment-summary">
                        <div class="summary-section">
                            <h4><i class="fas fa-user"></i> Mittente</h4>
                            <div id="senderSummary"></div>
                        </div>
                        
                        <div class="summary-section">
                            <h4><i class="fas fa-map-marker-alt"></i> Destinatario</h4>
                            <div id="recipientSummary"></div>
                        </div>
                        
                        <div class="summary-section">
                            <h4><i class="fas fa-box"></i> Pacco</h4>
                            <div id="packageSummary"></div>
                        </div>
                        
                        <div class="summary-section">
                            <h4><i class="fas fa-calculator"></i> Costi</h4>
                            <div id="costSummary"></div>
                        </div>
                    </div>
                    
                    <div class="form-group mt-4">
                        <label class="checkbox-container">
                            <input type="checkbox" id="terms_accepted" name="terms_accepted" required>
                            <span class="checkmark"></span>
                            Accetto i <a href="#" onclick="showTermsModal()">termini e condizioni</a> del servizio di spedizione
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer">
                <div class="step-navigation">
                    <button type="button" class="btn btn-secondary" id="prevStepBtn" onclick="previousStep()" style="display: none;">
                        <i class="fas fa-arrow-left"></i> Indietro
                    </button>
                    
                    <div class="step-indicator">
                        <span class="step-dot active" data-step="1"></span>
                        <span class="step-dot" data-step="2"></span>
                        <span class="step-dot" data-step="3"></span>
                        <span class="step-dot" data-step="4"></span>
                        <span class="step-dot" data-step="5"></span>
                    </div>
                    
                    <button type="button" class="btn btn-primary" id="nextStepBtn" onclick="nextStep()">
                        Avanti <i class="fas fa-arrow-right"></i>
                    </button>
                    
                    <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                        <i class="fas fa-paper-plane"></i> Conferma Spedizione
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Dettagli Spedizione -->
<div id="shipmentDetailsModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h2><i class="fas fa-info-circle"></i> Dettagli Spedizione</h2>
            <span class="close" onclick="closeModal('shipmentDetailsModal')">&times;</span>
        </div>
        <div class="modal-body" id="shipmentDetailsContent">
            <!-- Contenuto caricato dinamicamente -->
        </div>
    </div>
</div>

<script src="../assets/js/services.js"></script>

<?php include __DIR__ . '/../includes/footer.php'; ?>