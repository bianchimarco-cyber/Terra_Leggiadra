<?php
header('Content-Type: application/json');
require_once '../db_connect.php';
$stagione = intval(date('Y'));
$stmt = $conn->prepare("SELECT id, data_vendita, tipo, quantita, unita_misura, kg_effettivi, prezzo_unitario, ricavo_totale, acquirente FROM vendite WHERE YEAR(data_vendita) = ? ORDER BY data_vendita DESC LIMIT 200");
$stmt->bind_param('i', $stagione);
$stmt->execute();
$res = $stmt->get_result();
$rows = [];
while ($row = $res->fetch_assoc()) $rows[] = $row;
echo json_encode(['vendite' => $rows]);
$conn->close();
?>
