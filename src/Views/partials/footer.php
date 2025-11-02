<?php
/** @var string $layout */
?>
<?php if ($layout === 'admin'): ?>
    </div> <!-- /.admin-content -->
</div> <!-- /.admin-main -->
</div> <!-- /.admin-wrapper -->
<?php elseif ($layout === 'client'): ?>
</main>
<footer class="client-footer border-top mt-5 py-4">
    <div class="container-xl text-center text-muted">
        &copy; <?= date('Y') ?> Agenzia Plinio. Tutti i diritti riservati.
    </div>
</footer>
<?php else: ?>
</main>
<footer class="public-footer border-top mt-5 py-4">
    <div class="container-xl text-center text-muted">
        &copy; <?= date('Y') ?> Agenzia Plinio. Contattaci per maggiori informazioni.
    </div>
</footer>
<?php endif; ?>
<script src="/assets/bootstrap/bootstrap.bundle.min.js"></script>
<script src="/assets/js/app.js"></script>
<script src="/assets/js/auth.js"></script>
<?php if ($layout !== 'public'): ?>
<script src="/assets/js/dashboard.js"></script>
<?php endif; ?>
</body>
</html>
