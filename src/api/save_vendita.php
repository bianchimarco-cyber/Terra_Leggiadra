<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

$data_vendita   = $_POST['data_vendita'] ?? date('Y-m-d');
$tipo           = $_POST['tipo'] ?? 'Olio';
$quantita       = floatval($_POST['quantita'] ?? 0);
$prezzo         = floatval($_POST['prezzo_unitario'] ?? 0);
$acquirente     = $_POST['acquirente'] ?? null;

if ($quantita <= 0 || $prezzo <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dati non validi']);
    exit;
}

$ricavo = $quantita * $prezzo;

$stmt = $conn->prepare("INSERT INTO vendite (data_vendita, tipo, quantita, prezzo_unitario, ricavo_totale, acquirente) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param('ssdddS', $data_vendita, $tipo, $quantita, $prezzo, $ricavo, $acquirente);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'ricavo' => $ricavo, 'message' => 'Vendita registrata']);
} else {
    echo json_encode(['success' => false, 'message' => 'Errore: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
