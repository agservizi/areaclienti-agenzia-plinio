<?php
/**
 * Footer template per l'area clienti
 */
?>

<!-- JavaScript libraries -->
<script src="../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/common.js"></script>

<!-- JavaScript specifici per pagina -->
<?php if (isset($pageJS)): ?>
    <?php foreach ($pageJS as $js): ?>
        <script src="<?= htmlspecialchars($js) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Footer -->
<footer class="page-footer">
    <div class="footer-content">
        <div class="footer-info">
            <p>&copy; 2025 Agenzia Plinio. Tutti i diritti riservati.</p>
            <p>Via Plinio il Vecchio 72, Castellammare di Stabia (NA)</p>
        </div>
        <div class="footer-links">
            <a href="#" onclick="showPrivacyModal()">Privacy Policy</a>
            <a href="#" onclick="showTermsModal()">Termini e Condizioni</a>
            <a href="mailto:info@agenziaplinio.it">Supporto</a>
        </div>
    </div>
</footer>

<!-- Modal Privacy Policy -->
<div id="privacyModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h2><i class="fas fa-shield-alt"></i> Privacy Policy</h2>
            <span class="close" onclick="closeModal('privacyModal')">&times;</span>
        </div>
        <div class="modal-body">
            <div class="privacy-content">
                <h3>Informativa sulla Privacy</h3>
                <p>In conformità al Regolamento UE 2016/679 (GDPR), informiamo che:</p>
                
                <h4>Titolare del Trattamento</h4>
                <p>AG Servizi Via Plinio 72<br>
                Via Plinio il Vecchio 72, Castellammare di Stabia (NA)<br>
                P.IVA: 08442881218</p>
                
                <h4>Dati Raccolti</h4>
                <p>Raccogliamo i seguenti dati personali:</p>
                <ul>
                    <li>Dati anagrafici (nome, cognome, codice fiscale)</li>
                    <li>Dati di contatto (email, telefono, indirizzo)</li>
                    <li>Dati relativi alle richieste di servizio</li>
                    <li>Dati di navigazione e log di accesso</li>
                </ul>
                
                <h4>Finalità del Trattamento</h4>
                <p>I dati vengono trattati per:</p>
                <ul>
                    <li>Erogazione dei servizi richiesti</li>
                    <li>Gestione del rapporto contrattuale</li>
                    <li>Comunicazioni relative ai servizi</li>
                    <li>Adempimenti di legge</li>
                </ul>
                
                <h4>Base Giuridica</h4>
                <p>Il trattamento è basato su:</p>
                <ul>
                    <li>Consenso dell'interessato</li>
                    <li>Esecuzione del contratto</li>
                    <li>Obbligo di legge</li>
                </ul>
                
                <h4>Diritti dell'Interessato</h4>
                <p>Hai diritto di:</p>
                <ul>
                    <li>Accedere ai tuoi dati personali</li>
                    <li>Rettificare dati inesatti</li>
                    <li>Cancellare i dati (diritto all'oblio)</li>
                    <li>Limitare il trattamento</li>
                    <li>Portabilità dei dati</li>
                    <li>Opporti al trattamento</li>
                </ul>
                
                <h4>Conservazione</h4>
                <p>I dati vengono conservati per il tempo necessario alle finalità per cui sono stati raccolti e comunque nel rispetto dei termini di legge.</p>
                
                <h4>Sicurezza</h4>
                <p>Adottiamo misure tecniche e organizzative appropriate per garantire la sicurezza dei dati personali.</p>
                
                <h4>Contatti</h4>
                <p>Per esercitare i tuoi diritti o per informazioni, contatta: <a href="mailto:privacy@agenziaplinio.it">privacy@agenziaplinio.it</a></p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" onclick="closeModal('privacyModal')">Ho capito</button>
        </div>
    </div>
</div>

<!-- Modal Termini e Condizioni -->
<div id="termsModal" class="modal">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h2><i class="fas fa-file-contract"></i> Termini e Condizioni</h2>
            <span class="close" onclick="closeModal('termsModal')">&times;</span>
        </div>
        <div class="modal-body">
            <div class="terms-content">
                <h3>Termini e Condizioni d'Uso</h3>
                
                <h4>1. Accettazione dei Termini</h4>
                <p>L'utilizzo di questa area clienti implica l'accettazione dei presenti termini e condizioni.</p>
                
                <h4>2. Descrizione del Servizio</h4>
                <p>L'area clienti permette di:</p>
                <ul>
                    <li>Richiedere servizi offerti dall'agenzia</li>
                    <li>Monitorare lo stato delle richieste</li>
                    <li>Comunicare con il personale</li>
                    <li>Gestire i propri dati personali</li>
                </ul>
                
                <h4>3. Responsabilità dell'Utente</h4>
                <p>L'utente si impegna a:</p>
                <ul>
                    <li>Fornire informazioni veritiere e aggiornate</li>
                    <li>Mantenere riservate le proprie credenziali</li>
                    <li>Utilizzare il servizio in modo appropriato</li>
                    <li>Rispettare le leggi vigenti</li>
                </ul>
                
                <h4>4. Limitazioni di Responsabilità</h4>
                <p>L'agenzia non è responsabile per:</p>
                <ul>
                    <li>Interruzioni del servizio dovute a cause tecniche</li>
                    <li>Danni derivanti da uso improprio</li>
                    <li>Perdita di dati dovuta a cause esterne</li>
                </ul>
                
                <h4>5. Modifiche ai Termini</h4>
                <p>Ci riserviamo il diritto di modificare questi termini in qualsiasi momento, con preavviso agli utenti.</p>
                
                <h4>6. Risoluzione delle Controversie</h4>
                <p>Eventuali controversie saranno risolte secondo la legge italiana, con competenza esclusiva del Foro di Torre Annunziata.</p>
                
                <h4>7. Contatti</h4>
                <p>Per chiarimenti sui termini: <a href="mailto:info@agenziaplinio.it">info@agenziaplinio.it</a></p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" onclick="closeModal('termsModal')">Ho capito</button>
        </div>
    </div>
</div>

<script>
// Funzioni per i modal dei termini
function showPrivacyModal() {
    AppUtils.ModalManager.show('privacyModal');
}

function showTermsModal() {
    AppUtils.ModalManager.show('termsModal');
}

// Nascondi loading overlay dopo il caricamento
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        const loadingOverlay = document.getElementById('loadingOverlay');
        if (loadingOverlay) {
            loadingOverlay.style.display = 'none';
        }
    }, 500);
});
</script>

</body>
</html>