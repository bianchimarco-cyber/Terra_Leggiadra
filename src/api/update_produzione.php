<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

$id             = intval(isset($_POST['id'])                  ? $_POST['id']                : 0);
$data_raccolta  = isset($_POST['data_raccolta'])              ? $_POST['data_raccolta']     : date('Y-m-d');
$campo          = isset($_POST['campo'])                      ? trim($_POST['campo'])       : '';
$stagione       = intval(isset($_POST['stagione'])            ? $_POST['stagione']          : date('Y'));
$unita          = isset($_POST['unita_raccolta'])             ? $_POST['unita_raccolta']    : 'quarta';
$quantita_ins   = floatval(isset($_POST['quantita_inserita']) ? $_POST['quantita_inserita'] : 0);
$litri_x_quarta = floatval(isset($_POST['litri_x_quarta'])   ? $_POST['litri_x_quarta']    : 0);
$note           = isset($_POST['note'])                       ? trim($_POST['note'])        : '';
if ($note === '') $note = null;

if ($id <= 0 || $quantita_ins <= 0 || $campo === '') {
    echo json_encode(['success' => false, 'message' => 'Dati non validi']);
    exit;
}

if ($unita === 'quarta') {
    $quarte      = $quantita_ins;
    $kg_raccolti = round($quantita_ins * 12.5, 2);
} else {
    $kg_raccolti = $quantita_ins;
    $quarte      = round($quantita_ins / 12.5, 4);
}
$litri_olio = ($litri_x_quarta > 0) ? round($quarte * $litri_x_quarta, 2) : null;

// 10 SET + id = 11 params
// data_raccolta(s) campo(s) stagione(i) unita(s) quantita_ins(d) quarte(d) kg(d) litri_x_quarta(d) litri_olio(d) note(s) id(i)
$stmt = $conn->prepare("UPDATE produzione SET data_raccolta=?, campo=?, stagione=?, unita_raccolta=?, quantita_inserita=?, quarte_raccolte=?, kg_raccolti=?, litri_x_quarta=?, litri_olio=?, note=? WHERE id=?");
if (!$stmt) { echo json_encode(['success'=>false,'message'=>$conn->error]); exit; }
$stmt->bind_param('ssisdddddsi', $data_raccolta, $campo, $stagione, $unita, $quantita_ins, $quarte, $kg_raccolti, $litri_x_quarta, $litri_olio, $note, $id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}
$stmt->close();
$conn->close();
?>
