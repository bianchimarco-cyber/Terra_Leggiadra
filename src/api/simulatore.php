<?php
header('Content-Type: application/json');

// Leggi JSON dal body della richiesta POST
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) $data = [];

// Passo 1: leggi i valori inviati dal JS
$quantita       = floatval(isset($data['quantita'])       ? $data['quantita']       : 0);
$unita          = isset($data['unita'])                   ? $data['unita']          : 'quarta';
$litri_x_quarta = floatval(isset($data['litri_x_quarta']) ? $data['litri_x_quarta'] : 0);
$prezzo_olive   = floatval(isset($data['prezzo_olive'])   ? $data['prezzo_olive']   : 0);
$prezzo_olio    = floatval(isset($data['prezzo_olio'])    ? $data['prezzo_olio']    : 0);
$costo_molitura = floatval(isset($data['costo_molitura']) ? $data['costo_molitura'] : 0);

// Validazione
if ($quantita <= 0) {
    echo json_encode(['error' => 'Inserire una quantita valida']);
    exit;
}
if ($litri_x_quarta <= 0) {
    echo json_encode(['error' => 'Inserire i litri per quarta (resa)']);
    exit;
}

// Passo 2: normalizza sempre in quarte e kg
// Se l'utente ha inserito in quarte: 1 quarta = 12.5 kg
// Se l'utente ha inserito in kg: dividi per 12.5 per avere le quarte
if ($unita === 'quarta') {
    $quarte = $quantita;
    $kg     = $quantita * 12.5;
} else {
    $kg     = $quantita;
    $quarte = $quantita / 12.5;
}

// Passo 3: Scenario A — Vendita olive
// Il prezzo è per unità scelta (€/quarta oppure €/kg)
$ricavo_olive = $quantita * $prezzo_olive;

// Passo 4: Scenario B — Produzione olio
// Litri = quante quarte × litri che produce ogni quarta
$litri_olio = $quarte * $litri_x_quarta;
// Ricavo lordo = litri prodotti × prezzo al litro
$ricavo_lordo_olio = $litri_olio * $prezzo_olio;
// Costo molitura = in €/quintale (100 kg), quindi kg / 100 × costo
$costo_trasformazione = ($kg / 100) * $costo_molitura;
// Ricavo netto = lordo - molitura
$ricavo_netto_olio = $ricavo_lordo_olio - $costo_trasformazione;

// Passo 5: confronto
$differenza    = $ricavo_netto_olio - $ricavo_olive;
$conviene_olio = ($differenza > 0);
$percentuale   = $ricavo_olive > 0 ? abs($differenza / $ricavo_olive * 100) : 0;

// Risponde con JSON — tutti i campi che il JS si aspetta
echo json_encode([
    'quarte'         => number_format($quarte, 2, '.', ''),
    'kg_input'       => number_format($kg, 2, '.', ''),
    'litri_olio'     => number_format($litri_olio, 2, '.', ''),
    'costo_molitura' => number_format($costo_trasformazione, 2, '.', ''),
    'vendita_olive'  => number_format($ricavo_olive, 2, '.', ''),
    'vendita_olio'   => number_format($ricavo_netto_olio, 2, '.', ''),
    'differenza'     => number_format(abs($differenza), 2, '.', ''),
    'conviene_olio'  => $conviene_olio,
    'percentuale'    => number_format($percentuale, 1, '.', ''),
]);
?>
