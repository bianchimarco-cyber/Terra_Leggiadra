<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

$data_spesa  = isset($_POST['data'])        ? $_POST['data']               : date('Y-m-d');
$categoria   = isset($_POST['categoria'])   ? $_POST['categoria']          : 'Logistica';
$descrizione = isset($_POST['descrizione']) ? trim($_POST['descrizione'])  : '';
$importo     = floatval(isset($_POST['importo']) ? $_POST['importo']       : 0);
$campo       = isset($_POST['campo'])       ? trim($_POST['campo'])        : '';
if ($campo === '') $campo = null;

if ($importo <= 0) {
    echo json_encode(['success' => false, 'message' => 'Importo non valido']);
    exit;
}

// Colonne: data_spesa(s) categoria(s) descrizione(s) importo(d) campo(s) = 5
$stmt = $conn->prepare("INSERT INTO costi (data_spesa, categoria, descrizione, importo, campo) VALUES (?, ?, ?, ?, ?)");
if (!$stmt) { echo json_encode(['success'=>false,'message'=>'Prepare: '.$conn->error]); exit; }
$stmt->bind_param('sssds', $data_spesa, $categoria, $descrizione, $importo, $campo);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id' => $conn->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Execute: '.$stmt->error]);
}
$stmt->close();
$conn->close();
?>
