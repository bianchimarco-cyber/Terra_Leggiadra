<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

$data_raccolta   = $_POST['data_raccolta'] ?? date('Y-m-d');
$campo           = $_POST['campo'] ?? '';
$stagione        = intval($_POST['stagione'] ?? date('Y'));
$kg_raccolti     = floatval($_POST['kg_raccolti'] ?? 0);
$resa            = floatval($_POST['resa_percentuale'] ?? 0);
$note            = $_POST['note'] ?? null;

if ($kg_raccolti <= 0 || empty($campo)) {
    echo json_encode(['success' => false, 'message' => 'Dati non validi']);
    exit;
}

$litri_olio = $resa > 0 ? ($kg_raccolti * $resa / 100) : null;

$stmt = $conn->prepare("INSERT INTO produzione (data_raccolta, campo, stagione, kg_raccolti, resa_percentuale, litri_olio, kg_magazzino, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$litri_mag = $litri_olio ?? 0;
$stmt->bind_param('ssiiddds', $data_raccolta, $campo, $stagione, $kg_raccolti, $resa, $litri_olio, $litri_mag, $note);

if ($stmt->execute()) {
    echo json_encode([
        'success' => true, 
        'id' => $conn->insert_id, 
        'litri_stimati' => $litri_olio,
        'message' => 'Produzione registrata con successo'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Errore nel salvataggio: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>
