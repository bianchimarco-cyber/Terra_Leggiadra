<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

$data_vendita = isset($_POST['data_vendita']) ? $_POST['data_vendita'] : date('Y-m-d');
$tipo         = isset($_POST['tipo'])         ? $_POST['tipo']         : 'Olio';
$quantita     = floatval(isset($_POST['quantita'])        ? $_POST['quantita']        : 0);
$unita        = isset($_POST['unita_misura']) ? $_POST['unita_misura'] : 'litri';
$prezzo       = floatval(isset($_POST['prezzo_unitario']) ? $_POST['prezzo_unitario'] : 0);
$acquirente   = isset($_POST['acquirente'])   ? trim($_POST['acquirente']) : '';
if ($acquirente === '') $acquirente = null;

if ($quantita <= 0 || $prezzo <= 0) {
    echo json_encode(['success' => false, 'message' => 'Quantita e prezzo devono essere maggiori di zero']);
    exit;
}

$kg_effettivi = ($unita === 'quarta') ? $quantita * 12.5 : $quantita;
$ricavo = round($quantita * $prezzo, 2);

// Colonne: data_vendita(s) tipo(s) quantita(d) unita_misura(s) kg_effettivi(d) prezzo_unitario(d) ricavo_totale(d) acquirente(s) = 8
$stmt = $conn->prepare("INSERT INTO vendite (data_vendita, tipo, quantita, unita_misura, kg_effettivi, prezzo_unitario, ricavo_totale, acquirente) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) { echo json_encode(['success'=>false,'message'=>'Prepare: '.$conn->error]); exit; }
$stmt->bind_param('ssdsddds', $data_vendita, $tipo, $quantita, $unita, $kg_effettivi, $prezzo, $ricavo, $acquirente);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'ricavo' => $ricavo, 'kg_effettivi' => $kg_effettivi]);
} else {
    echo json_encode(['success' => false, 'message' => 'Execute: '.$stmt->error]);
}
$stmt->close();
$conn->close();
?>
