<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

$tabella = isset($_POST['tabella']) ? $_POST['tabella'] : '';
$id      = intval(isset($_POST['id']) ? $_POST['id'] : 0);

$allowed = ['costi', 'produzione', 'vendite'];
if (!in_array($tabella, $allowed) || $id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Parametri non validi']);
    exit;
}

$stmt = $conn->prepare("DELETE FROM `$tabella` WHERE id = ?");
$stmt->bind_param('i', $id);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'affected' => $stmt->affected_rows]);
} else {
    echo json_encode(['success' => false, 'message' => $stmt->error]);
}
$stmt->close();
$conn->close();
?>
