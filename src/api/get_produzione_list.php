<?php
header('Content-Type: application/json');
require_once '../db_connect.php';
$stagione = intval(date('Y'));
$stmt = $conn->prepare("SELECT id, data_raccolta, campo, unita_raccolta, quantita_inserita, quarte_raccolte, kg_raccolti, litri_x_quarta, litri_olio, note FROM produzione WHERE stagione = ? ORDER BY data_raccolta DESC LIMIT 200");
$stmt->bind_param('i', $stagione);
$stmt->execute();
$res = $stmt->get_result();
$rows = [];
while ($row = $res->fetch_assoc()) $rows[] = $row;
echo json_encode(['produzione' => $rows]);
$conn->close();
?>
