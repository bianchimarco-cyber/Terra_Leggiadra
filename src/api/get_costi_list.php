<?php
header('Content-Type: application/json');
require_once '../db_connect.php';
$stagione = intval(date('Y'));
$stmt = $conn->prepare("SELECT id, data_spesa, categoria, descrizione, importo, campo FROM costi WHERE YEAR(data_spesa) = ? ORDER BY data_spesa DESC LIMIT 200");
$stmt->bind_param('i', $stagione);
$stmt->execute();
$res = $stmt->get_result();
$rows = [];
while ($row = $res->fetch_assoc()) $rows[] = $row;
echo json_encode(['costi' => $rows]);
$conn->close();
?>
