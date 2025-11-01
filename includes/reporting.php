<?php
declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

if (!function_exists('fetch_requests_report')) {
    function fetch_requests_report(array $filters = []): array
    {
        $query = 'SELECT r.id, r.status, r.created_at, r.updated_at, r.user_id, s.title AS service_title, s.slug AS service_slug, u.email AS user_email '
            . 'FROM requests r '
            . 'JOIN services s ON s.id = r.service_id '
            . 'JOIN users u ON u.id = r.user_id '
            . 'WHERE 1=1';

        $params = [];

        if (!empty($filters['status'])) {
            $query .= ' AND r.status = :status';
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['service'])) {
            $query .= ' AND s.slug = :service';
            $params['service'] = $filters['service'];
        }

        if (!empty($filters['date_from'])) {
            $from = DateTimeImmutable::createFromFormat('Y-m-d', $filters['date_from']);
            if ($from) {
                $query .= ' AND r.created_at >= :date_from';
                $params['date_from'] = $from->format('Y-m-d 00:00:00');
            }
        }

        if (!empty($filters['date_to'])) {
            $to = DateTimeImmutable::createFromFormat('Y-m-d', $filters['date_to']);
            if ($to) {
                $query .= ' AND r.created_at <= :date_to';
                $params['date_to'] = $to->format('Y-m-d 23:59:59');
            }
        }

        $query .= ' ORDER BY r.created_at DESC';

        $stmt = db()->prepare($query);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $totals = [
            'total' => 0,
            'pending' => 0,
            'processing' => 0,
            'completed' => 0,
            'rejected' => 0,
        ];
        $services = [];

        foreach ($rows as $row) {
            $totals['total']++;
            $status = $row['status'] ?? '';
            if (isset($totals[$status])) {
                $totals[$status]++;
            }
            $serviceKey = $row['service_slug'] ?? 'sconosciuto';
            if (!isset($services[$serviceKey])) {
                $services[$serviceKey] = [
                    'title' => $row['service_title'] ?? 'Servizio',
                    'count' => 0,
                ];
            }
            $services[$serviceKey]['count']++;
        }

        usort($services, static function (array $a, array $b): int {
            return $b['count'] <=> $a['count'];
        });

        return [
            'rows' => $rows,
            'totals' => $totals,
            'services' => $services,
        ];
    }
}

if (!function_exists('generate_requests_csv')) {
    function generate_requests_csv(array $rows, array $filters = []): string
    {
        $handle = fopen('php://temp', 'r+');
        if ($handle === false) {
            throw new RuntimeException('Impossibile generare il CSV');
        }

        $metaLine = 'Report richieste AG Servizi';
        if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
            $metaLine .= sprintf(' (Periodo: %s - %s)', $filters['date_from'] ?: 'inizio', $filters['date_to'] ?: 'oggi');
        }
    fputcsv($handle, [$metaLine], ';');
    fputcsv($handle, ['Generato il', date('d/m/Y H:i')], ';');
    fputcsv($handle, [], ';');

        fputcsv($handle, ['ID', 'Utente', 'Servizio', 'Stato', 'Creata il', 'Aggiornata il'], ';');
        foreach ($rows as $row) {
            fputcsv($handle, [
                $row['id'],
                $row['user_email'] ?? '-',
                $row['service_title'] ?? '-',
                $row['status'] ?? '-',
                format_date($row['created_at'] ?? null),
                format_date($row['updated_at'] ?? null),
            ], ';');
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv ?: '';
    }
}

if (!function_exists('pdf_escape_text')) {
    function pdf_escape_text(string $text): string
    {
        $encoded = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text);
        if ($encoded === false) {
            $encoded = $text;
        }
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $encoded);
    }
}

if (!function_exists('generate_requests_pdf')) {
    function generate_requests_pdf(array $rows, array $filters = []): string
    {
        $lines = [];
        $lines[] = 'Report richieste AG Servizi';
        $lines[] = 'Generato il: ' . date('d/m/Y H:i');
        if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
            $lines[] = sprintf('Periodo: %s - %s', $filters['date_from'] ?: 'inizio', $filters['date_to'] ?: 'oggi');
        }
        if (!empty($filters['status'])) {
            $lines[] = 'Filtro stato: ' . $filters['status'];
        }
        if (!empty($filters['service'])) {
            $lines[] = 'Filtro servizio: ' . $filters['service'];
        }
        $lines[] = '';
        $lines[] = 'ID | Utente | Servizio | Stato | Creata | Aggiornata';

        foreach ($rows as $row) {
            $lines[] = sprintf(
                '%s | %s | %s | %s | %s | %s',
                $row['id'],
                $row['user_email'] ?? '-',
                $row['service_title'] ?? '-',
                $row['status'] ?? '-',
                format_date($row['created_at'] ?? null),
                format_date($row['updated_at'] ?? null)
            );
        }

        if (!$rows) {
            $lines[] = 'Nessuna richiesta disponibile per i filtri selezionati.';
        }

        $pageWidth = 595;
        $pageHeight = 842;
        $margin = 40;
        $lineHeight = 14;
        $linesPerPage = max(1, (int) floor(($pageHeight - ($margin * 2)) / $lineHeight));
        $chunks = array_chunk($lines, $linesPerPage);
        if (!$chunks) {
            $chunks = [[$lines[0] ?? '']];
        }

        $objects = [];
        $offsets = [];
        $objects[] = '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj';

        $pageCount = count($chunks);
        $pageReferences = [];
        for ($i = 0; $i < $pageCount; $i++) {
            $pageReferences[] = sprintf('%d 0 R', 4 + ($i * 2));
        }
        $objects[] = sprintf('2 0 obj << /Type /Pages /Kids [%s] /Count %d >> endobj', implode(' ', $pageReferences), $pageCount);
        $objects[] = '3 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj';

        foreach ($chunks as $index => $chunk) {
            $pageObjectNumber = 4 + ($index * 2);
            $contentObjectNumber = $pageObjectNumber + 1;

            $y = $pageHeight - $margin;
            $content = "BT\n/F1 12 Tf\n";
            foreach ($chunk as $line) {
                $content .= sprintf("1 0 0 1 %d %d Tm (%s) Tj\n", $margin, $y, pdf_escape_text($line));
                $y -= $lineHeight;
            }
            $content .= "ET";

            $objects[] = sprintf(
                '%d 0 obj << /Type /Page /Parent 2 0 R /Resources << /Font << /F1 3 0 R >> >> /MediaBox [0 0 %d %d] /Contents %d 0 R >> endobj',
                $pageObjectNumber,
                $pageWidth,
                $pageHeight,
                $contentObjectNumber
            );

            $objects[] = sprintf(
                "%d 0 obj << /Length %d >> stream\n%s\nendstream\nendobj",
                $contentObjectNumber,
                strlen($content),
                $content
            );
        }

        $pdf = "%PDF-1.4\n";
        foreach ($objects as $index => $object) {
            $offsets[$index + 1] = strlen($pdf);
            $pdf .= $object . "\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= 'xref\n';
        $pdf .= '0 ' . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        foreach ($objects as $index => $object) {
            $pdf .= sprintf('%010d 00000 n %s', $offsets[$index + 1], "\n");
        }
        $pdf .= sprintf('trailer << /Size %d /Root 1 0 R >>%s', count($objects) + 1, "\n");
        $pdf .= "startxref\n" . $xrefOffset . "\n%%EOF";

        return $pdf;
    }
}
