<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

$stagione = intval($_GET['stagione'] ?? date('Y'));

$stmt = $conn->prepare("SELECT * FROM produzione WHERE stagione = ? ORDER BY data_raccolta DESC LIMIT 100");
$stmt->bind_param('i', $stagione);
$stmt->execute();
$result = $stmt->get_result();

$prods = [];
while ($row = $result->fetch_assoc()) $prods[] = $row;

echo json_encode(['produzione' => $prods, 'stagione' => $stagione]);
$conn->close();
?>
