<?php
require __DIR__ . '/includes/db_connect.php';
include __DIR__ . '/includes/header.php';
?>
<div class="container mt-5">
    <div class="glass-container text-center">
        <h1>Portale Servizi Agenzia Plinio</h1>
        <p>Accedi ai servizi digitali, gestisci le richieste e rimani in contatto con il tuo consulente.</p>
        <div class="d-flex justify-content-center gap-3 mt-4">
            <a class="btn btn-outline-light" href="<?php echo htmlspecialchars($basePath . '/auth/login.php', ENT_QUOTES, 'UTF-8'); ?>">Accedi</a>
            <a class="btn btn-outline-light" href="<?php echo htmlspecialchars($basePath . '/auth/register.php', ENT_QUOTES, 'UTF-8'); ?>">Registrati</a>
        </div>
    </div>
</div>
<section class="container mt-5">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card glass-container h-100">
                <h4>Digital Identity</h4>
                <p>Servizi SPID, CNS e PEC per privati e aziende.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card glass-container h-100">
                <h4>Connessioni</h4>
                <p>Soluzioni di telefonia e internet con partner Windtre, Fastweb e Iliad.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card glass-container h-100">
                <h4>Logistica</h4>
                <p>Prenota spedizioni e ritiri con assistenza dedicata.</p>
            </div>
        </div>
    </div>
</section>
<script src="<?php echo htmlspecialchars($assetBase . '/js/bootstrap.bundle.min.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase . '/js/main.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
</div>
</body>
</html>
