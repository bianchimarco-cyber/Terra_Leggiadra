<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

$data_vendita   = $_POST['data_vendita'] ?? date('Y-m-d');
$tipo           = $_POST['tipo'] ?? 'Olio';
$quantita       = floatval($_POST['quantita'] ?? 0);
$unita          = $_POST['unita_misura'] ?? 'kg'; // 'kg' o 'quarta' (1 quarta = 12.5 kg)
$prezzo         = floatval($_POST['prezzo_unitario'] ?? 0);
$acquirente     = trim($_POST['acquirente'] ?? '') ?: null;

if ($quantita <= 0 || $prezzo <= 0) {
    echo json_encode(['success' => false, 'message' => 'Quantità e prezzo devono essere maggiori di zero']);
    exit;
}

// Converti sempre in kg per il salvataggio
$kg_effettivi = ($unita === 'quarta') ? $quantita * 12.5 : $quantita;
$ricavo = $quantita * $prezzo; // il ricavo si basa sulla quantità nell'unità scelta

$stmt = $conn->prepare("INSERT INTO vendite (data_vendita, tipo, quantita, unita_misura, kg_effettivi, prezzo_unitario, ricavo_totale, acquirente) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param('sssdddds', $data_vendita, $tipo, $quantita, $unita, $kg_effettivi, $prezzo, $ricavo, $acquirente);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'ricavo' => $ricavo, 'kg_effettivi' => $kg_effettivi, 'message' => 'Vendita registrata']);
} else {
    echo json_encode(['success' => false, 'message' => 'Errore DB: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
