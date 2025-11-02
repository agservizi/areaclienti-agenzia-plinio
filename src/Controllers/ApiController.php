<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\CoverageCheck;
use App\Models\FileStore;
use App\Models\Shipment;
use RuntimeException;
use function app_log;
use function crypto_encrypt_file;
use function current_user;
use function render_json;
use function require_login;
use function sanitize;

class ApiController
{
    public function coverage(): void
    {
        require_login();
        $data = sanitize($_POST);
        $address = $data['address'] ?? '';
        $operator = $data['operator'] ?? '';
        if ($address === '' || $operator === '') {
            render_json(['error' => 'Dati mancanti'], 422);
            return;
        }

        $result = CoverageCheck::create([
            'user_id' => (int) current_user()['id'],
            'address' => $address,
            'operator' => $operator,
            'result' => [
                'coverage' => rand(60, 99) . '%',
                'technology' => 'FTTH',
                'note' => 'Verifica preliminare, conferma necessaria',
            ],
        ]);

        render_json(['data' => $result['result']]);
    }

    public function tracking(string $code): void
    {
        $shipment = Shipment::findByTracking($code);
        if (!$shipment) {
            render_json(['error' => 'Tracking non trovato'], 404);
            return;
        }

        render_json([
            'tracking_code' => $shipment['tracking_code'],
            'status' => $shipment['status'],
            'updated_at' => $shipment['updated_at'],
        ]);
    }

    public function upload(): void
    {
        require_login();
        if (empty($_FILES['file']) || !is_array($_FILES['file'])) {
            render_json(['error' => 'Nessun file caricato'], 400);
            return;
        }

        $file = $_FILES['file'];
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            render_json(['error' => 'Errore nel caricamento del file'], 400);
            return;
        }

        $allowedMime = ['application/pdf'];
        $maxSize = 10 * 1024 * 1024;
        if (!in_array($file['type'], $allowedMime, true)) {
            render_json(['error' => 'Formato non supportato'], 415);
            return;
        }
        if ($file['size'] > $maxSize) {
            render_json(['error' => 'File troppo grande'], 413);
            return;
        }

        $storagePath = __DIR__ . '/../../storage/files_encrypted';
        try {
            $crypto = crypto_encrypt_file($file['tmp_name'], $storagePath);
        } catch (RuntimeException $exception) {
            app_log('files', 'Upload encryption failed', ['error' => $exception->getMessage()]);
            render_json(['error' => 'Impossibile salvare il file'], 500);
            return;
        }

        $fileId = FileStore::create([
            'user_id' => (int) current_user()['id'],
            'filename_original' => $file['name'],
            'filename_storage' => $crypto['stored_name'],
            'iv' => $crypto['nonce'],
            'mime' => $file['type'],
            'size' => (int) $file['size'],
        ]);

        render_json(['id' => $fileId, 'message' => 'File caricato']);
    }
}
