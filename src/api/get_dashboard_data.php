<?php
header('Content-Type: application/json');
require_once '../db_connect.php';

// Sempre stagione corrente
$stagione = intval(date('Y'));

// Totale costi
$r = $conn->query("SELECT SUM(importo) as tot, COUNT(*) as n FROM costi WHERE YEAR(data_spesa) = $stagione");
$costi = $r->fetch_assoc();

// Costi per categoria
$r = $conn->query("SELECT categoria, SUM(importo) as tot FROM costi WHERE YEAR(data_spesa) = $stagione GROUP BY categoria");
$costi_cat = [];
while ($row = $r->fetch_assoc()) $costi_cat[] = $row;

// Produzione totale (kg olive e litri olio prodotti al frantoio)
$r = $conn->query("SELECT SUM(kg_raccolti) as tot_kg, SUM(litri_olio) as tot_litri, COUNT(*) as num_campi FROM produzione WHERE stagione = $stagione");
$prod = $r->fetch_assoc();

// Vendite totali
$r = $conn->query("SELECT tipo, unita_misura, SUM(ricavo_totale) as tot, SUM(quantita) as qtot, SUM(kg_effettivi) as kg_tot FROM vendite WHERE YEAR(data_vendita) = $stagione GROUP BY tipo, unita_misura");
$vendite_raw = [];
while ($row = $r->fetch_assoc()) $vendite_raw[] = $row;
$ricavo_totale = array_sum(array_column($vendite_raw, 'tot'));

// Ultime spese
$r = $conn->query("SELECT data_spesa, categoria, descrizione, importo FROM costi WHERE YEAR(data_spesa) = $stagione ORDER BY created_at DESC LIMIT 5");
$ultime_spese = [];
while ($row = $r->fetch_assoc()) $ultime_spese[] = $row;

// Andamento storico (ultimi 4 anni incluso corrente)
$anni_fa = $stagione - 3;
$r = $conn->query("SELECT stagione, SUM(kg_raccolti) as kg, SUM(litri_olio) as litri FROM produzione WHERE stagione >= $anni_fa GROUP BY stagione ORDER BY stagione");
$storico = [];
while ($row = $r->fetch_assoc()) $storico[] = $row;

// Costo per litro = totale costi stagione / litri olio prodotti al frantoio
$tot_costi = floatval($costi['tot'] ?? 0);
$tot_litri_prodotti = floatval($prod['tot_litri'] ?? 0);
$costo_litro = $tot_litri_prodotti > 0 ? $tot_costi / $tot_litri_prodotti : 0;

// Margine netto = ricavi vendite - costi totali
$margine = $ricavo_totale - $tot_costi;

echo json_encode([
    'stagione'   => $stagione,
    'costi'      => [
        'totale'        => $costi['tot'] ?? 0,
        'numero'        => $costi['n'] ?? 0,
        'per_categoria' => $costi_cat
    ],
    'produzione' => [
        'kg'         => $prod['tot_kg'] ?? 0,     // olive raccolte (da tabella produzione)
        'litri'      => $prod['tot_litri'] ?? 0,  // litri olio prodotti al frantoio
        'num_campi'  => $prod['num_campi'] ?? 0
    ],
    'vendite'    => [
        'dettaglio' => $vendite_raw,
        'totale'    => $ricavo_totale
    ],
    'kpi' => [
        'costo_per_litro' => number_format($costo_litro, 2, '.', ''), // costi totali / litri prodotti
        'margine_netto'   => number_format($margine, 2, '.', ''),
        'roi'             => $tot_costi > 0 ? number_format(($margine / $tot_costi) * 100, 1, '.', '') : '0'
    ],
    'ultime_spese' => $ultime_spese,
    'storico'      => $storico
]);

$conn->close();
?>
