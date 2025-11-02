<?php
/** @var string $layout */

$projectRoot = dirname(__DIR__, 3);
$publicDir = $projectRoot . '/public';
$hasLocalBootstrapBundle = is_file($publicDir . '/assets/bootstrap/bootstrap.bundle.min.js');
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
<?php if ($hasLocalBootstrapBundle): ?>
<script src="/assets/bootstrap/bootstrap.bundle.min.js"></script>
<?php else: ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<?php endif; ?>
<script src="/assets/js/app.js"></script>
<script src="/assets/js/auth.js"></script>
<?php if ($layout !== 'public'): ?>
<script src="/assets/js/dashboard.js"></script>
<?php endif; ?>
</body>
</html>
