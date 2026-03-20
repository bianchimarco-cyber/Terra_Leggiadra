<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

$id           = intval(isset($_POST['id'])            ? $_POST['id']            : 0);
$data_vendita = isset($_POST['data_vendita'])          ? $_POST['data_vendita']  : date('Y-m-d');
$tipo         = isset($_POST['tipo'])                  ? $_POST['tipo']          : 'Olio';
$quantita     = floatval(isset($_POST['quantita'])     ? $_POST['quantita']      : 0);
$unita        = isset($_POST['unita_misura'])          ? $_POST['unita_misura']  : 'litri';
$prezzo       = floatval(isset($_POST['prezzo_unitario']) ? $_POST['prezzo_unitario'] : 0);
$acquirente   = isset($_POST['acquirente'])            ? trim($_POST['acquirente']) : '';
if ($acquirente === '') $acquirente = null;

if ($id <= 0 || $quantita <= 0 || $prezzo <= 0) {
    echo json_encode(['success' => false, 'message' => 'Dati non validi']);
    exit;
}

$kg_effettivi = ($unita === 'quarta') ? $quantita * 12.5 : $quantita;
$ricavo = round($quantita * $prezzo, 2);

// 9 params: s s d s d d d s i
$stmt = $conn->prepare("UPDATE vendite SET data_vendita=?, tipo=?, quantita=?, unita_misura=?, kg_effettivi=?, prezzo_unitario=?, ricavo_totale=?, acquirente=? WHERE id=?");
if (!$stmt) { echo json_encode(['success'=>false,'message'=>$conn->error]); exit; }
$stmt->bind_param('ssdsdddsi', $data_vendita, $tipo, $quantita, $unita, $kg_effettivi, $prezzo, $ricavo, $acquirente, $id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}
$stmt->close();
$conn->close();
?>
