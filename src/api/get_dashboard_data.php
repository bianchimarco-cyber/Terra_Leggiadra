<?php
header('Content-Type: application/json');
require_once '../db_connect.php';
$stagione = intval(date('Y'));

$r = $conn->query("SELECT COALESCE(SUM(importo),0) as tot, COUNT(*) as n FROM costi WHERE YEAR(data_spesa) = $stagione");
$costi = $r->fetch_assoc();

$r = $conn->query("SELECT categoria, SUM(importo) as tot FROM costi WHERE YEAR(data_spesa) = $stagione GROUP BY categoria ORDER BY tot DESC");
$costi_cat = [];
while ($row = $r->fetch_assoc()) $costi_cat[] = $row;

$r = $conn->query("SELECT COALESCE(SUM(kg_raccolti),0) as tot_kg, COALESCE(SUM(quarte_raccolte),0) as tot_quarte, COALESCE(SUM(litri_olio),0) as tot_litri, COUNT(*) as num FROM produzione WHERE stagione = $stagione");
$prod = $r->fetch_assoc();

$r = $conn->query("SELECT tipo, unita_misura, SUM(ricavo_totale) as tot, SUM(quantita) as qtot FROM vendite WHERE YEAR(data_vendita) = $stagione GROUP BY tipo, unita_misura");
$vendite_raw = [];
$ricavo_totale = 0;
$litri_venduti = 0;
while ($row = $r->fetch_assoc()) {
    $vendite_raw[] = $row;
    $ricavo_totale += floatval($row['tot']);
    if ($row['tipo'] === 'Olio') $litri_venduti += floatval($row['qtot']);
}

$r = $conn->query("SELECT data_spesa, categoria, descrizione, importo FROM costi WHERE YEAR(data_spesa) = $stagione ORDER BY created_at DESC LIMIT 5");
$ultime_spese = [];
while ($row = $r->fetch_assoc()) $ultime_spese[] = $row;

$anni_fa = $stagione - 3;
$r = $conn->query("SELECT stagione, SUM(kg_raccolti) as kg, SUM(quarte_raccolte) as quarte, SUM(litri_olio) as litri FROM produzione WHERE stagione >= $anni_fa GROUP BY stagione ORDER BY stagione");
$storico = [];
while ($row = $r->fetch_assoc()) $storico[] = $row;

$tot_costi = floatval($costi['tot']);
$tot_litri = floatval($prod['tot_litri']);
$litri_mag = max(0, $tot_litri - $litri_venduti);
$costo_litro = $tot_litri > 0 ? round($tot_costi / $tot_litri, 2) : 0;
$margine = round($ricavo_totale - $tot_costi, 2);

echo json_encode([
    'stagione'   => $stagione,
    'costi'      => ['totale' => $costi['tot'], 'numero' => $costi['n'], 'per_categoria' => $costi_cat],
    'produzione' => ['kg' => $prod['tot_kg'], 'quarte' => $prod['tot_quarte'], 'litri' => $prod['tot_litri'], 'num' => $prod['num']],
    'vendite'    => ['totale' => $ricavo_totale, 'litri_venduti' => $litri_venduti],
    'kpi'        => ['costo_per_litro' => $costo_litro, 'litri_magazzino' => $litri_mag, 'margine_netto' => $margine],
    'ultime_spese' => $ultime_spese,
    'storico'    => $storico,
]);
$conn->close();
?>
