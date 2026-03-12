<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$data = json_decode(file_get_contents('php://input'), true);

$kg             = floatval($data['kg'] ?? 0);
$resa           = floatval($data['resa'] ?? 0);
$prezzo_olive   = floatval($data['prezzo_olive'] ?? 0);
$prezzo_olio    = floatval($data['prezzo_olio'] ?? 0);
$costo_molitura = floatval($data['costo_molitura'] ?? 0);

if ($kg <= 0) {
    echo json_encode(['error' => 'Inserire kg validi']);
    exit;
}

// Calcoli
$ricavo_vendita_olive = $kg * $prezzo_olive;

$litri_olio = $kg * ($resa / 100);
$ricavo_lordo_olio = $litri_olio * $prezzo_olio;
$costo_trasformazione = ($kg / 100) * $costo_molitura;
$ricavo_netto_olio = $ricavo_lordo_olio - $costo_trasformazione;

$differenza = $ricavo_netto_olio - $ricavo_vendita_olive;
$percentuale_vantaggio = $ricavo_vendita_olive > 0 ? abs($differenza / $ricavo_vendita_olive * 100) : 0;

$conviene_olio = $differenza > 0;

echo json_encode([
    'kg_input'          => number_format($kg, 2, '.', ''),
    'litri_olio'        => number_format($litri_olio, 2, '.', ''),
    'costo_molitura'    => number_format($costo_trasformazione, 2, '.', ''),
    'vendita_olive'     => number_format($ricavo_vendita_olive, 2, '.', ''),
    'ricavo_lordo_olio' => number_format($ricavo_lordo_olio, 2, '.', ''),
    'vendita_olio'      => number_format($ricavo_netto_olio, 2, '.', ''),
    'differenza'        => number_format(abs($differenza), 2, '.', ''),
    'conviene_olio'     => $conviene_olio,
    'percentuale'       => number_format($percentuale_vantaggio, 1, '.', ''),
    'consiglio'         => $conviene_olio 
        ? "Conviene produrre olio! Guadagni €" . number_format(abs($differenza), 2, ',', '.') . " in più."
        : "Conviene vendere olive! Guadagni €" . number_format(abs($differenza), 2, ',', '.') . " in più."
]);
?>
