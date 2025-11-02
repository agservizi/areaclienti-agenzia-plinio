<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h1 class="h5 mb-3">Documenti disponibili</h1>
                <ul class="list-group list-group-flush">
                    <?php if (empty($files)): ?>
                        <li class="list-group-item">Nessun documento caricato.</li>
                    <?php else: ?>
                        <?php foreach ($files as $file): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= htmlspecialchars($file['filename_original']) ?></strong>
                                    <div class="text-muted small"><?= round(($file['size'] ?? 0) / 1024, 1) ?> KB</div>
                                </div>
                                <a class="btn btn-sm btn-outline-primary" href="/client/documents/download?id=<?= (int) $file['id'] ?>">Scarica</a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 class="h5 mb-3">Carica documento (PDF)</h2>
                <form id="document-upload" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <div class="mb-3">
                        <label class="form-label" for="document-file">File PDF</label>
                        <input class="form-control" type="file" id="document-file" name="file" accept="application/pdf" required>
                    </div>
                    <button class="btn btn-primary" type="submit">Carica</button>
                </form>
                <div class="upload-feedback mt-3" id="upload-feedback"></div>
            </div>
        </div>
    </div>
</div>
