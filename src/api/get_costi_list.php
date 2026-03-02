<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

$stagione = intval($_GET['stagione'] ?? date('Y'));

$stmt = $conn->prepare("SELECT id, data_spesa, categoria, descrizione, importo, campo FROM costi WHERE YEAR(data_spesa) = ? ORDER BY data_spesa DESC LIMIT 100");
$stmt->bind_param('i', $stagione);
$stmt->execute();
$result = $stmt->get_result();

$costi = [];
while ($row = $result->fetch_assoc()) $costi[] = $row;

echo json_encode(['costi' => $costi, 'stagione' => $stagione]);
$conn->close();
?>
