<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

$data       = $_POST['data'] ?? date('Y-m-d');
$categoria  = $_POST['categoria'] ?? 'Logistica';
$descrizione= $_POST['descrizione'] ?? '';
$importo    = floatval($_POST['importo'] ?? 0);
$campo      = $_POST['campo'] ?? null;

if ($importo <= 0) {
    echo json_encode(['success' => false, 'message' => 'Importo non valido']);
    exit;
}

$stmt = $conn->prepare("INSERT INTO costi (data_spesa, categoria, descrizione, importo, campo) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param('sssds', $data, $categoria, $descrizione, $importo, $campo);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id' => $conn->insert_id, 'message' => 'Spesa registrata con successo']);
} else {
    echo json_encode(['success' => false, 'message' => 'Errore nel salvataggio']);
}

$stmt->close();
$conn->close();
?>
