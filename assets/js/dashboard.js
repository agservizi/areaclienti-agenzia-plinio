/**
 * JavaScript per la gestione della dashboard clienti
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
});

let dashboardData = {
    stats: {
        total: 0,
        pending: 0,
        completed: 0,
        shipments: 0
    },
    notifications: [],
    recentRequests: []
};

/**
 * Inizializza la dashboard
 */
async function initializeDashboard() {
    try {
        AppUtils.debug('Initializing dashboard...');
        
        // Carica i dati della dashboard
        await loadDashboardData();
        
        // Inizializza i componenti
        initializeSidebar();
        initializeNotifications();
        initializeServiceCards();
        
        AppUtils.debug('Dashboard initialized successfully');
        
    } catch (error) {
        AppUtils.debug('Dashboard initialization error:', error);
        AppUtils.notifications.error('Errore durante il caricamento della dashboard');
    }
}

/**
 * Carica i dati della dashboard
 */
async function loadDashboardData() {
    try {
        // Carica statistiche
        const statsResponse = await AppUtils.api.get('dashboard/stats.php');
        if (statsResponse.success) {
            dashboardData.stats = statsResponse.data;
            updateStatsDisplay();
        }
        
        // Carica notifiche recenti
        const notificationsResponse = await AppUtils.api.get('notifications/recent.php');
        if (notificationsResponse.success) {
            dashboardData.notifications = notificationsResponse.data;
            updateNotificationsDisplay();
        }
        
        // Carica richieste recenti
        const requestsResponse = await AppUtils.api.get('requests/recent.php');
        if (requestsResponse.success) {
            dashboardData.recentRequests = requestsResponse.data;
            updateRecentRequestsDisplay();
        }
        
    } catch (error) {
        AppUtils.debug('Error loading dashboard data:', error);
        
        // Dati di fallback per dimostrazione
        dashboardData.stats = {
            total: 25,
            pending: 3,
            completed: 20,
            shipments: 2
        };
        
        dashboardData.notifications = [
            {
                id: 1,
                title: 'Spedizione completata',
                message: 'Il tuo pacco è stato consegnato con successo',
                type: 'success',
                created_at: new Date().toISOString(),
                is_read: false
            },
            {
                id: 2,
                title: 'Servizio completato',
                message: 'La tua richiesta di visura è stata completata',
                type: 'info',
                created_at: new Date(Date.now() - 2 * 60 * 60 * 1000).toISOString(),
                is_read: false
            },
            {
                id: 3,
                title: 'Nuova richiesta in elaborazione',
                message: 'La tua richiesta di attivazione SPID è in elaborazione',
                type: 'warning',
                created_at: new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString(),
                is_read: true
            }
        ];
        
        dashboardData.recentRequests = [
            {
                id: 1,
                service_type: 'spedizioni',
                title: 'Spedizione pacco Milano',
                status: 'completed',
                created_at: new Date(Date.now() - 2 * 24 * 60 * 60 * 1000).toISOString()
            },
            {
                id: 2,
                service_type: 'attivazioni_digitali',
                title: 'Attivazione SPID',
                status: 'pending',
                created_at: new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString()
            },
            {
                id: 3,
                service_type: 'attivazioni_digitali',
                title: 'Attivazione SPID',
                status: 'in_progress',
                created_at: new Date().toISOString()
            }
        ];
        
        updateStatsDisplay();
        updateNotificationsDisplay();
        updateRecentRequestsDisplay();
    }
}

/**
 * Aggiorna la visualizzazione delle statistiche
 */
function updateStatsDisplay() {
    const elements = {
        totalRequests: document.getElementById('totalRequests'),
        pendingRequests: document.getElementById('pendingRequests'),
        completedRequests: document.getElementById('completedRequests'),
        activeShipments: document.getElementById('activeShipments')
    };
    
    // Animazione dei numeri
    if (elements.totalRequests) {
        animateCounter(elements.totalRequests, dashboardData.stats.total);
    }
    if (elements.pendingRequests) {
        animateCounter(elements.pendingRequests, dashboardData.stats.pending);
    }
    if (elements.completedRequests) {
        animateCounter(elements.completedRequests, dashboardData.stats.completed);
    }
    if (elements.activeShipments) {
        animateCounter(elements.activeShipments, dashboardData.stats.shipments);
    }
}

/**
 * Aggiorna la visualizzazione delle notifiche
 */
function updateNotificationsDisplay() {
    const container = document.getElementById('recentNotifications');
    if (!container) return;
    
    if (dashboardData.notifications.length === 0) {
        container.innerHTML = `
            <div class="text-center p-3">
                <i class="fas fa-bell-slash text-muted"></i>
                <p class="text-muted mt-2 mb-0">Nessuna notifica</p>
            </div>
        `;
        return;
    }
    
    const notificationsHtml = dashboardData.notifications.map(notification => `
        <div class="notification-item ${!notification.is_read ? 'unread' : ''}" 
             onclick="readNotification(${notification.id})">
            <div class="notification-header">
                <span class="notification-title">${AppUtils.Utils.escapeHtml(notification.title)}</span>
                <span class="notification-time">${formatRelativeTime(notification.created_at)}</span>
            </div>
            <div class="notification-message">
                ${AppUtils.Utils.escapeHtml(notification.message)}
            </div>
        </div>
    `).join('');
    
    container.innerHTML = notificationsHtml;
}

/**
 * Aggiorna la visualizzazione delle richieste recenti
 */
function updateRecentRequestsDisplay() {
    const tbody = document.getElementById('recentRequestsBody');
    if (!tbody) return;
    
    if (dashboardData.recentRequests.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center">
                    <i class="fas fa-inbox text-muted"></i>
                    <p class="text-muted mt-2 mb-0">Nessuna richiesta recente</p>
                </td>
            </tr>
        `;
        return;
    }
    
    const requestsHtml = dashboardData.recentRequests.map(request => `
        <tr onclick="viewRequest(${request.id})" style="cursor: pointer;">
            <td>#${request.id}</td>
            <td>${getServiceDisplayName(request.service_type)}</td>
            <td>${AppUtils.Utils.escapeHtml(request.title)}</td>
            <td><span class="status-badge status-${request.status}">${getStatusDisplayName(request.status)}</span></td>
            <td>${formatRelativeTime(request.created_at)}</td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="event.stopPropagation(); viewRequest(${request.id})">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        </tr>
    `).join('');
    
    tbody.innerHTML = requestsHtml;
}

/**
 * Inizializza la sidebar
 */
function initializeSidebar() {
    // Gestione del menu attivo
    updateActiveMenuItem();
    
    // Gestione del responsive
    handleSidebarResponsive();
}

/**
 * Inizializza le notifiche
 */
function initializeNotifications() {
    // Aggiorna il badge delle notifiche
    updateNotificationBadge();
    
    // Carica notifiche non lette ogni 30 secondi
    setInterval(loadUnreadNotifications, 30000);
}

/**
 * Inizializza le card dei servizi
 */
function initializeServiceCards() {
    const serviceCards = document.querySelectorAll('.service-card');
    
    serviceCards.forEach(card => {
        card.addEventListener('click', function() {
            const serviceType = this.dataset.service || this.getAttribute('onclick')?.match(/'([^']+)'/)?.[1];
            if (serviceType) {
                redirectToService(serviceType);
            }
        });
        
        // Animazione hover con particelle (opzionale)
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-6px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });
}

/**
 * Gestione del logout
 */
async function handleLogout() {
    try {
        const result = await AppUtils.api.post('auth/logout.php');
        
        if (result.success) {
            AppUtils.notifications.success('Logout effettuato con successo');
            setTimeout(() => {
                window.location.href = '../index.php';
            }, 1000);
        } else {
            AppUtils.notifications.error('Errore durante il logout');
        }
        
    } catch (error) {
        AppUtils.debug('Logout error:', error);
        AppUtils.notifications.error('Errore di connessione');
        
        // Forza il logout locale
        setTimeout(() => {
            window.location.href = '../index.php';
        }, 1500);
    }
}

/**
 * Mostra il modal per nuova richiesta
 */
function showNewRequestModal() {
    AppUtils.ModalManager.show('newRequestModal');
}

/**
 * Crea una nuova richiesta per un servizio specifico
 */
function createNewRequest(serviceType) {
    AppUtils.ModalManager.hide('newRequestModal');
    redirectToService(serviceType);
}

/**
 * Reindirizza a un servizio specifico
 */
function redirectToService(serviceType) {
    const serviceUrls = {
        'spedizioni': 'spedizioni.php',
        'attivazioni-digitali': 'attivazioni-digitali.php',
        'caf-patronato': 'caf-patronato.php',
        'visure': 'visure.php',
        'telefonia-utenze': 'telefonia-utenze.php',
        'servizi-postali': 'servizi-postali.php',
        'ritiro-pacchi': 'ritiro-pacchi.php'
    };
    
    const url = serviceUrls[serviceType] || 'richieste.php';
    window.location.href = url;
}

/**
 * Visualizza una richiesta specifica
 */
function viewRequest(requestId) {
    window.location.href = `richiesta.php?id=${requestId}`;
}

/**
 * Legge una notifica
 */
async function readNotification(notificationId) {
    try {
        await AppUtils.api.post(`notifications/read.php`, { id: notificationId });
        
        // Aggiorna la visualizzazione
        const notification = dashboardData.notifications.find(n => n.id === notificationId);
        if (notification) {
            notification.is_read = true;
            updateNotificationsDisplay();
            updateNotificationBadge();
        }
        
    } catch (error) {
        AppUtils.debug('Error marking notification as read:', error);
    }
}

/**
 * Toggle del pannello notifiche
 */
function toggleNotifications() {
    // Implementazione per aprire/chiudere il pannello notifiche
    // Per ora reindirizza alla pagina notifiche
    window.location.href = 'notifiche.php';
}

/**
 * Toggle della sidebar su mobile
 */
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    
    if (sidebar && mainContent) {
        sidebar.classList.toggle('show');
        
        // Chiudi sidebar cliccando fuori su mobile
        if (sidebar.classList.contains('show')) {
            document.addEventListener('click', closeSidebarOnOutsideClick);
        } else {
            document.removeEventListener('click', closeSidebarOnOutsideClick);
        }
    }
}

/**
 * Chiude la sidebar quando si clicca fuori
 */
function closeSidebarOnOutsideClick(event) {
    const sidebar = document.querySelector('.sidebar');
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    
    if (sidebar && !sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
        sidebar.classList.remove('show');
        document.removeEventListener('click', closeSidebarOnOutsideClick);
    }
}

/**
 * Gestione responsiva della sidebar
 */
function handleSidebarResponsive() {
    const mediaQuery = window.matchMedia('(max-width: 1024px)');
    
    function handleResize(e) {
        const sidebar = document.querySelector('.sidebar');
        const mainContent = document.querySelector('.main-content');
        
        if (e.matches) {
            // Mobile view
            if (mainContent) {
                mainContent.classList.add('sidebar-collapsed');
            }
        } else {
            // Desktop view
            if (sidebar) {
                sidebar.classList.remove('show');
            }
            if (mainContent) {
                mainContent.classList.remove('sidebar-collapsed');
            }
        }
    }
    
    mediaQuery.addListener(handleResize);
    handleResize(mediaQuery);
}

/**
 * Aggiorna il menu item attivo
 */
function updateActiveMenuItem() {
    const currentPage = window.location.pathname.split('/').pop() || 'dashboard.php';
    const menuItems = document.querySelectorAll('.menu-item');
    
    menuItems.forEach(item => {
        const link = item.querySelector('a');
        if (link) {
            const href = link.getAttribute('href');
            if (href === currentPage || (currentPage === 'dashboard.php' && href === 'dashboard.php')) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        }
    });
}

/**
 * Aggiorna il badge delle notifiche
 */
function updateNotificationBadge() {
    const unreadCount = dashboardData.notifications.filter(n => !n.is_read).length;
    const badges = document.querySelectorAll('.notification-badge');
    
    badges.forEach(badge => {
        if (unreadCount > 0) {
            badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    });
    
    // Aggiorna anche il badge nel menu
    const menuBadge = document.querySelector('.menu-item a[href="notifiche.php"] .badge');
    if (menuBadge) {
        if (unreadCount > 0) {
            menuBadge.textContent = unreadCount > 99 ? '99+' : unreadCount;
            menuBadge.style.display = 'inline-block';
        } else {
            menuBadge.style.display = 'none';
        }
    }
}

/**
 * Carica notifiche non lette
 */
async function loadUnreadNotifications() {
    try {
        const response = await AppUtils.api.get('notifications/unread-count.php');
        if (response.success) {
            const unreadCount = response.count || 0;
            
            // Aggiorna solo se c'è una differenza
            const currentUnreadCount = dashboardData.notifications.filter(n => !n.is_read).length;
            if (unreadCount !== currentUnreadCount) {
                // Ricarica le notifiche
                await loadDashboardData();
            }
        }
    } catch (error) {
        AppUtils.debug('Error loading unread notifications:', error);
    }
}

/**
 * Utility functions
 */

function animateCounter(element, targetValue, duration = 1000) {
    const startValue = 0;
    const startTime = performance.now();
    
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        const easeOut = 1 - Math.pow(1 - progress, 3);
        const currentValue = Math.floor(startValue + (targetValue - startValue) * easeOut);
        
        element.textContent = currentValue;
        
        if (progress < 1) {
            requestAnimationFrame(update);
        } else {
            element.textContent = targetValue;
        }
    }
    
    requestAnimationFrame(update);
}

function formatRelativeTime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInMinutes = Math.floor((now - date) / 60000);
    
    if (diffInMinutes < 1) return 'Adesso';
    if (diffInMinutes < 60) return `${diffInMinutes} min fa`;
    
    const diffInHours = Math.floor(diffInMinutes / 60);
    if (diffInHours < 24) return `${diffInHours} ore fa`;
    
    const diffInDays = Math.floor(diffInHours / 24);
    if (diffInDays < 7) return `${diffInDays} giorni fa`;
    
    return AppUtils.Utils.formatDate(date);
}

function getServiceDisplayName(serviceType) {
    const serviceNames = {
        'spedizioni': 'Spedizioni',
        'attivazioni_digitali': 'Attivazioni Digitali',
        'caf_patronato': 'CAF e Patronato',
        'visure': 'Visure',
        'telefonia_utenze': 'Telefonia e Utenze',
        'servizi_postali': 'Servizi Postali',
        'ritiro_pacchi': 'Ritiro Pacchi'
    };
    
    return serviceNames[serviceType] || AppUtils.Utils.capitalize(serviceType.replace('_', ' '));
}

function getStatusDisplayName(status) {
    const statusNames = {
        'pending': 'In Attesa',
        'in_progress': 'In Elaborazione',
        'completed': 'Completato',
        'cancelled': 'Annullato'
    };
    
    return statusNames[status] || AppUtils.Utils.capitalize(status);
}

// Event listeners
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        // Ricarica i dati quando la pagina diventa visibile
        loadUnreadNotifications();
    }
});

// Auto-refresh ogni 5 minuti
setInterval(() => {
    if (!document.hidden) {
        loadDashboardData();
    }
}, 5 * 60 * 1000);

// Export per testing
window.DashboardManager = {
    loadDashboardData,
    handleLogout,
    toggleSidebar,
    redirectToService,
    viewRequest
};

AppUtils.debug('Dashboard JavaScript loaded');