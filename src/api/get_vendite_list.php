<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

$stagione = intval(date('Y'));

$stmt = $conn->prepare("SELECT id, data_vendita, tipo, quantita, unita_misura, kg_effettivi, prezzo_unitario, ricavo_totale, acquirente FROM vendite WHERE YEAR(data_vendita) = ? ORDER BY data_vendita DESC LIMIT 200");
$stmt->bind_param('i', $stagione);
$stmt->execute();
$result = $stmt->get_result();

$vendite = [];
while ($row = $result->fetch_assoc()) $vendite[] = $row;

echo json_encode(['vendite' => $vendite, 'stagione' => $stagione]);
$conn->close();
?>
