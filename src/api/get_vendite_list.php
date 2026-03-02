<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

$stagione = intval($_GET['stagione'] ?? date('Y'));

$stmt = $conn->prepare("SELECT * FROM vendite WHERE YEAR(data_vendita) = ? ORDER BY data_vendita DESC LIMIT 100");
$stmt->bind_param('i', $stagione);
$stmt->execute();
$result = $stmt->get_result();

$vendite = [];
while ($row = $result->fetch_assoc()) $vendite[] = $row;

echo json_encode(['vendite' => $vendite, 'stagione' => $stagione]);
$conn->close();
?>
