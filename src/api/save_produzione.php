<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

$data_raccolta  = isset($_POST['data_raccolta'])              ? $_POST['data_raccolta']    : date('Y-m-d');
$campo          = isset($_POST['campo'])                      ? trim($_POST['campo'])      : '';
$stagione       = intval(isset($_POST['stagione'])            ? $_POST['stagione']         : date('Y'));
$unita          = isset($_POST['unita_raccolta'])             ? $_POST['unita_raccolta']   : 'quarta';
$quantita_ins   = floatval(isset($_POST['quantita_inserita']) ? $_POST['quantita_inserita']: 0);
$litri_x_quarta = floatval(isset($_POST['litri_x_quarta'])   ? $_POST['litri_x_quarta']   : 0);
$note           = isset($_POST['note'])                       ? trim($_POST['note'])       : '';
if ($note === '') $note = null;

if ($quantita_ins <= 0 || $campo === '') {
    echo json_encode(['success' => false, 'message' => 'Inserire campo e quantita validi']);
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

// Colonne: data_raccolta(s) campo(s) stagione(i) unita_raccolta(s) quantita_inserita(d) quarte_raccolte(d) kg_raccolti(d) litri_x_quarta(d) litri_olio(d) note(s) = 10
$stmt = $conn->prepare("INSERT INTO produzione (data_raccolta, campo, stagione, unita_raccolta, quantita_inserita, quarte_raccolte, kg_raccolti, litri_x_quarta, litri_olio, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) { echo json_encode(['success'=>false,'message'=>'Prepare: '.$conn->error]); exit; }
$stmt->bind_param('ssisddddds', $data_raccolta, $campo, $stagione, $unita, $quantita_ins, $quarte, $kg_raccolti, $litri_x_quarta, $litri_olio, $note);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id' => $conn->insert_id, 'quarte' => $quarte, 'kg_raccolti' => $kg_raccolti, 'litri_olio' => $litri_olio]);
} else {
    echo json_encode(['success' => false, 'message' => 'Execute: '.$stmt->error]);
}
$stmt->close();
$conn->close();
?>
