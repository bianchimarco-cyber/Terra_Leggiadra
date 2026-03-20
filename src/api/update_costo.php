<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

$id          = intval(isset($_POST['id'])          ? $_POST['id']          : 0);
$data_spesa  = isset($_POST['data'])               ? $_POST['data']        : date('Y-m-d');
$categoria   = isset($_POST['categoria'])          ? $_POST['categoria']   : 'Logistica';
$descrizione = isset($_POST['descrizione'])        ? trim($_POST['descrizione']) : '';
$importo     = floatval(isset($_POST['importo'])   ? $_POST['importo']     : 0);
$campo       = isset($_POST['campo'])              ? trim($_POST['campo'])  : '';
if ($campo === '') $campo = null;

if ($id <= 0 || $importo <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dati non validi']);
    exit;
}

// 6 params: s s s d s i
$stmt = $conn->prepare("UPDATE costi SET data_spesa=?, categoria=?, descrizione=?, importo=?, campo=? WHERE id=?");
if (!$stmt) { echo json_encode(['success'=>false,'message'=>$conn->error]); exit; }
$stmt->bind_param('sssdsi', $data_spesa, $categoria, $descrizione, $importo, $campo, $id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}
$stmt->close();
$conn->close();
?>
