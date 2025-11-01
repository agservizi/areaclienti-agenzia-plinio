<?php
/**
 * Script di migrazione database basato su file SQL.
 * Utilizza le credenziali definite nel file .env del progetto.
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$rootPath = dirname(__DIR__);
$envPath = $rootPath . DIRECTORY_SEPARATOR . '.env';
$sqlPath = $rootPath . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'database.sql';

require_once $rootPath . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'env.php';

if (!is_file($envPath)) {
    fwrite(STDERR, "File .env non trovato.\n");
    exit(1);
}

if (!is_file($sqlPath)) {
    fwrite(STDERR, "File database.sql non trovato in config/.\n");
    exit(1);
}

app_load_env($envPath);

echo "File .env caricato.\n";

$dbHost = env('DB_HOST', 'localhost');
$dbUser = env('DB_USERNAME', 'root');
$dbPass = env('DB_PASSWORD', '');
$dbName = env('DB_NAME', '');
$dbPort = (int) env('DB_PORT', 3306);
$dbCharset = env('DB_CHARSET', 'utf8mb4');

if ($dbName === '') {
    fwrite(STDERR, "Variabile DB_NAME mancante nel file .env.\n");
    exit(1);
}

echo "Connessione al database {$dbHost}:{$dbPort}/{$dbName}...\n";

$sqlContent = file_get_contents($sqlPath);

if ($sqlContent === false || trim($sqlContent) === '') {
    fwrite(STDERR, "Il file database.sql è vuoto o non leggibile.\n");
    exit(1);
}

// Rimuove istruzioni che potrebbero non essere permesse sull'hosting condiviso
$sqlContent = preg_replace('/CREATE\s+DATABASE[^;]+;\s*/i', '', $sqlContent);
$sqlContent = preg_replace('/USE\s+[^;]+;\s*/i', '', $sqlContent);

$mysqli = @mysqli_init();

if ($mysqli === false) {
    fwrite(STDERR, "Impossibile inizializzare l'estensione MySQLi.\n");
    exit(1);
}

$connected = @$mysqli->real_connect($dbHost, $dbUser, $dbPass, $dbName, $dbPort);

if ($connected === false) {
    fwrite(STDERR, 'Connessione al database fallita: ' . $mysqli->connect_error . "\n");
    exit(1);
}

echo "Connessione stabilita. Avvio migrazione...\n";

if (!$mysqli->set_charset($dbCharset)) {
    fwrite(STDERR, 'Impossibile impostare il charset ' . $dbCharset . ': ' . $mysqli->error . "\n");
    $mysqli->close();
    exit(1);
}

// Disabilita i vincoli per evitare problemi di ordine di creazione
$mysqli->query('SET FOREIGN_KEY_CHECKS=0');

if (!$mysqli->multi_query($sqlContent)) {
    fwrite(STDERR, 'Errore durante l\'esecuzione della migrazione: ' . $mysqli->error . "\n");
    $mysqli->close();
    exit(1);
}

// Scarica eventuali result set generati
while ($mysqli->more_results()) {
    $mysqli->next_result();
    if ($result = $mysqli->store_result()) {
        $result->free();
    }
}

$mysqli->query('SET FOREIGN_KEY_CHECKS=1');
$mysqli->close();

echo "Migrazione completata con successo.\n";
