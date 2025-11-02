        </main>
        <footer class="admin-footer">
            <div class="admin-footer-content">
                <small>&copy; <span data-current-year></span> Agenzia Plinio &middot; Console Amministrativa</small>
                <a class="admin-footer-link" href="<?php echo htmlspecialchars($basePath . '/auth/logout.php', ENT_QUOTES, 'UTF-8'); ?>">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Esci</span>
                </a>
            </div>
        </footer>
    </div>
</div>
<script src="<?php echo htmlspecialchars($assetBase . '/js/bootstrap.bundle.min.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase . '/js/main.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
<script src="<?php echo htmlspecialchars($assetBase . '/js/admin.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
</body>
</html>