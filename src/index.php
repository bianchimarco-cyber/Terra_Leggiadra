<!DOCTYPE html>
<html lang="it">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>AgroManager — Gestionale Olivicolo</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🫒</text></svg>">
</head>
<body>

<header class="topbar">
  <a class="topbar-brand" href="#" onclick="navigate('dashboard')">
    <div class="logo-icon">🫒</div>
    <div>
      <h1>AgroManager</h1>
      <span>Gestionale Olivicolo</span>
    </div>
  </a>
  <nav class="topbar-nav">
    <button class="nav-btn active" data-nav="dashboard"   onclick="navigate('dashboard')">📊 Dashboard</button>
    <button class="nav-btn"        data-nav="simulatore"  onclick="navigate('simulatore')">✨ Simulatore</button>
    <button class="nav-btn"        data-nav="costi"       onclick="navigate('costi')">💰 Costi</button>
    <button class="nav-btn"        data-nav="produzione"  onclick="navigate('produzione')">🚜 Produzione</button>
    <button class="nav-btn"        data-nav="vendite"     onclick="navigate('vendite')">🏺 Vendite</button>
  </nav>
  <div class="topbar-right">
    <span class="stagione-badge" id="stagione-label">Stagione <?php echo date('Y'); ?></span>
  </div>
</header>

<main class="app-container">

  <!-- ══ DASHBOARD ══ -->
  <div id="page-dashboard" class="page active">
    <div class="section-header">
      <h2><small>Panoramica</small>Dashboard Stagionale</h2>
    </div>
    <div id="dash-kpi"><div class="loading"><div class="spinner"></div> Caricamento...</div></div>

    <div class="grid-main-side mt-20">
      <div class="card">
        <div class="card-header">
          <div class="card-title"><span class="icon">📈</span> Andamento Storico</div>
          <div class="card-subtitle">Kg olive e litri olio per stagione</div>
        </div>
        <div id="dash-storico-chart"><div class="loading"><div class="spinner"></div></div></div>
      </div>
      <div style="display:flex;flex-direction:column;gap:20px;">
        <div class="card">
          <div class="card-header">
            <div class="card-title"><span class="icon">🥧</span> Costi per Categoria</div>
          </div>
          <div class="cat-chart" id="dash-cat-chart"><div class="loading"><div class="spinner"></div></div></div>
        </div>
        <div id="dash-pareggio"></div>
      </div>
    </div>

    <div class="card mt-20">
      <div class="card-header">
        <div class="card-title"><span class="icon">🧾</span> Ultime Spese</div>
        <button class="btn btn-ghost btn-sm" onclick="navigate('costi')">Vedi tutte →</button>
      </div>
      <div class="table-wrapper">
        <table>
          <thead><tr><th>Data</th><th>Categoria</th><th>Descrizione</th><th class="text-right">Importo</th></tr></thead>
          <tbody id="dash-ultime-spese"><tr><td colspan="4"><div class="loading"><div class="spinner"></div></div></td></tr></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ══ SIMULATORE ══ -->
  <div id="page-simulatore" class="page">
    <div class="section-header">
      <h2><small>Strumento Smart</small>Simulatore Decisionale</h2>
    </div>
    <div class="grid-main-side">
      <div>
        <div class="simulatore-hero">
          <h2>✨ Olive o Olio?</h2>
          <p>Inserisci i dati per scoprire la strategia più redditizia.</p>
          <div class="sim-inputs" style="margin-top:20px;">
            <div class="sim-input-group">
              <label>📦 Unità Olive</label>
              <select id="sim_unita" onchange="onSimUnitaChange()" style="width:100%;padding:10px 13px;background:rgba(245,237,216,0.1);border:1.5px solid rgba(245,237,216,0.2);border-radius:6px;color:var(--cream);font-family:var(--font-body);font-size:0.95rem;outline:none;">
                <option value="quarta">Quarte (1 quarta = 12,5 kg)</option>
                <option value="kg">Kg</option>
              </select>
            </div>
            <div class="sim-input-group">
              <label id="sim_qta_label">🏋️ Quantità (Quarte)</label>
              <input type="number" id="sim_quantita" value="" min="1" step="0.5" placeholder="es. 80">
            </div>
            <div class="sim-input-group">
              <label>🫙 Litri Olio per Quarta (resa)</label>
              <input type="number" id="sim_litri_x_quarta" value="" step="0.1" min="0.1" placeholder="es. 2.0">
            </div>
            <div class="sim-input-group">
              <label id="sim_prezzo_olive_label">🫒 Prezzo Olive (€/quarta)</label>
              <input type="number" id="sim_p_olive" value="" step="0.10" placeholder="es. 5.00">
            </div>
            <div class="sim-input-group">
              <label>🫙 Prezzo Olio (€/litro)</label>
              <input type="number" id="sim_p_olio" value="" step="0.50" placeholder="es. 12.00">
            </div>
            <div class="sim-input-group" style="grid-column:1/-1">
              <label>⚙️ Costo Molitura (€/quintale)</label>
              <input type="number" id="sim_costo_mol" value="" step="0.50" placeholder="es. 16.00">
            </div>
          </div>
          <button class="btn-sim" id="sim-btn" onclick="calcolaConvenienza()">▶ Calcola Strategia</button>
        </div>
        <div id="sim-result"></div>
      </div>
      <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
          <div class="card-header"><div class="card-title"><span class="icon">📖</span> Come funziona</div></div>
          <p class="fs-sm text-clay" style="line-height:1.7;">Confronta due scenari in base ai prezzi di mercato:</p>
          <div style="margin-top:14px;display:flex;flex-direction:column;gap:10px;">
            <div style="padding:12px;background:var(--fog);border-radius:var(--radius-sm);">
              <div style="font-size:0.8rem;font-weight:600;color:var(--terra);margin-bottom:4px;">🫒 Scenario 1 — Vendita Olive</div>
              <div class="fs-sm text-clay">Ricavo = Quantità × Prezzo/unità</div>
            </div>
            <div style="padding:12px;background:var(--fog);border-radius:var(--radius-sm);">
              <div style="font-size:0.8rem;font-weight:600;color:var(--terra);margin-bottom:4px;">🫙 Scenario 2 — Produzione Olio</div>
              <div class="fs-sm text-clay">Litri = Quarte × Litri/quarta<br>Ricavo Netto = (Litri × €/L) − Costo Molitura</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ══ COSTI ══ -->
  <div id="page-costi" class="page">
    <div class="section-header">
      <h2><small>Contabilità</small>Registro Spese</h2>
      <div style="font-family:var(--font-display);font-size:1.1rem;color:var(--bark);">Totale: <span id="costi-totale" class="fw-700">—</span></div>
    </div>
    <div class="grid-main-side">
      <div class="card">
        <div class="card-header"><div class="card-title"><span class="icon">📋</span> Spese Registrate</div></div>
        <div class="table-wrapper">
          <table>
            <thead><tr><th>Data</th><th>Categoria</th><th>Descrizione</th><th>Campo</th><th class="text-right">Importo</th><th class="text-center">Azioni</th></tr></thead>
            <tbody id="costi-tbody"><tr><td colspan="6"><div class="loading"><div class="spinner"></div></div></td></tr></tbody>
          </table>
        </div>
      </div>
      <div class="card">
        <div class="card-header"><div class="card-title"><span class="icon">➕</span> <span id="costi-form-title">Nuova Spesa</span></div></div>
        <form id="form-costo" onsubmit="saveCosto(event)">
          <input type="hidden" id="costo_id" value="">
          <div class="form-group">
            <label class="form-label">Data Spesa</label>
            <input type="date" name="data" id="costo_data" class="form-input" required>
          </div>
          <div class="form-group">
            <label class="form-label">Categoria</label>
            <select name="categoria" id="costo_categoria" class="form-select" required>
              <option value="Input Tecnici">🌿 Input Tecnici</option>
              <option value="Logistica">🚜 Logistica e Mezzi</option>
              <option value="Terzi">🤝 Servizi Terzi</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Descrizione</label>
            <input type="text" name="descrizione" id="costo_descrizione" class="form-input" placeholder="Es. Gasolio Trattore...">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Importo (€)</label>
              <input type="number" name="importo" id="costo_importo" class="form-input" step="0.01" min="0.01" placeholder="0.00" required>
            </div>
            <div class="form-group">
              <label class="form-label">Campo (opz.)</label>
              <input type="text" name="campo" id="costo_campo" class="form-input" placeholder="Es. Nord...">
            </div>
          </div>
          <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-primary" style="flex:1;">💾 Salva</button>
            <button type="button" class="btn btn-ghost" id="costo-cancel-btn" style="display:none;" onclick="resetFormCosto()">✕ Annulla</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ══ PRODUZIONE ══ -->
  <div id="page-produzione" class="page">
    <div class="section-header">
      <h2><small>Raccolta &amp; Frantoio</small>Gestione Produzione</h2>
    </div>
    <div class="grid-main-side">
      <div class="card">
        <div class="card-header"><div class="card-title"><span class="icon">📋</span> Produzioni Registrate</div></div>
        <div class="table-wrapper">
          <table>
            <thead><tr><th>Data</th><th>Campo</th><th>Quantità</th><th class="text-right">Resa (L/quarta)</th><th class="text-right">Litri Olio</th><th>Note</th><th class="text-center">Azioni</th></tr></thead>
            <tbody id="prod-tbody"><tr><td colspan="7"><div class="loading"><div class="spinner"></div></div></td></tr></tbody>
          </table>
        </div>
      </div>
      <div class="card">
        <div class="card-header"><div class="card-title"><span class="icon">➕</span> <span id="prod-form-title">Registra Raccolta</span></div></div>
        <form id="form-produzione" onsubmit="saveProduzione(event)">
          <input type="hidden" id="prod_id" value="">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Data Raccolta</label>
              <input type="date" name="data_raccolta" id="prod_data" class="form-input" required>
            </div>
            <div class="form-group">
              <label class="form-label">Stagione</label>
              <input type="number" name="stagione" id="prod_stagione" class="form-input" required>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Campo / Appezzamento</label>
            <input type="text" name="campo" id="prod_campo" class="form-input" placeholder="Es. Nord, Collina..." required>
          </div>
          <div class="form-group">
            <label class="form-label">Unità di Misura</label>
            <select id="prod_unita" name="unita_raccolta" class="form-select" onchange="onProdUnitaChange()">
              <option value="quarta">Quarte (1 quarta = 12,5 kg)</option>
              <option value="kg">Kg</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" id="prod_qta_label">Quantità (Quarte)</label>
            <input type="number" id="prod_quantita" name="quantita_inserita" class="form-input" step="0.5" min="0.5" placeholder="es. 80" required oninput="updateProdPreview()">
          </div>
          <div class="form-group">
            <label class="form-label">Litri Olio per Quarta (resa)</label>
            <input type="number" id="prod_litri_x_quarta" name="litri_x_quarta" class="form-input" step="0.1" min="0" max="20" placeholder="es. 2.0 — lascia vuoto se non sai ancora" oninput="updateProdPreview()">
            <div class="form-hint" id="prod_preview" style="color:var(--olive);font-weight:500;margin-top:5px;min-height:18px;"></div>
          </div>
          <div class="form-group">
            <label class="form-label">Note (opzionale)</label>
            <textarea name="note" id="prod_note" class="form-textarea" rows="2" placeholder="Qualità, condizioni meteo..."></textarea>
          </div>
          <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-primary" style="flex:1;">💾 Salva</button>
            <button type="button" class="btn btn-ghost" id="prod-cancel-btn" style="display:none;" onclick="resetFormProd()">✕ Annulla</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- ══ VENDITE ══ -->
  <div id="page-vendite" class="page">
    <div class="section-header">
      <h2><small>Magazzino &amp; Ricavi</small>Registro Vendite</h2>
      <div style="font-family:var(--font-display);font-size:1.1rem;color:var(--olive-dark);">Ricavi: <span id="vendite-totale" class="fw-700">—</span></div>
    </div>
    <div class="grid-main-side">
      <div class="card">
        <div class="card-header"><div class="card-title"><span class="icon">📋</span> Vendite Registrate</div></div>
        <div class="table-wrapper">
          <table>
            <thead><tr><th>Data</th><th>Tipo</th><th class="text-right">Quantità</th><th class="text-right">Prezzo/Unità</th><th class="text-right">Ricavo</th><th>Acquirente</th><th class="text-center">Azioni</th></tr></thead>
            <tbody id="vendite-tbody"><tr><td colspan="7"><div class="loading"><div class="spinner"></div></div></td></tr></tbody>
          </table>
        </div>
      </div>
      <div class="card">
        <div class="card-header"><div class="card-title"><span class="icon">➕</span> <span id="vendite-form-title">Nuova Vendita</span></div></div>
        <form id="form-vendita" onsubmit="saveVendita(event)">
          <input type="hidden" id="vendita_id" value="">
          <div class="form-group">
            <label class="form-label">Data Vendita</label>
            <input type="date" name="data_vendita" id="vendita_data" class="form-input" required>
          </div>
          <div class="form-group">
            <label class="form-label">Tipo Prodotto</label>
            <select id="vendita_tipo" name="tipo" class="form-select" required onchange="onVenditaTipoChange()">
              <option value="Olio">🫙 Olio EVO</option>
              <option value="Olive">🫒 Olive</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Unità di Misura</label>
            <select id="vendita_unita" name="unita_misura" class="form-select" onchange="updateRicavoPreview()">
              <option value="litri">Litri (L)</option>
            </select>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Quantità</label>
              <input type="number" id="vendita_quantita" name="quantita" class="form-input" step="0.1" min="0.1" placeholder="es. 50" required oninput="updateRicavoPreview()">
            </div>
            <div class="form-group">
              <label class="form-label" id="vendita_prezzo_label">Prezzo (€/litro)</label>
              <input type="number" id="vendita_prezzo" name="prezzo_unitario" class="form-input" step="0.01" min="0.01" placeholder="es. 12.00" required oninput="updateRicavoPreview()">
            </div>
          </div>
          <div class="form-hint" id="vendita_ricavo_preview" style="color:var(--olive);font-weight:500;margin-bottom:10px;min-height:18px;"></div>
          <div class="form-group">
            <label class="form-label">Acquirente</label>
            <input type="text" name="acquirente" id="vendita_acquirente" class="form-input" placeholder="Es. Ristorante Da Mario...">
          </div>
          <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-primary" style="flex:1;">💾 Salva</button>
            <button type="button" class="btn btn-ghost" id="vendita-cancel-btn" style="display:none;" onclick="resetFormVendita()">✕ Annulla</button>
          </div>
        </form>
      </div>
    </div>
  </div>

</main>

<!-- Mobile nav -->
<nav class="mobile-nav">
  <button class="mob-nav-btn active" data-nav="dashboard"  onclick="navigate('dashboard')"><span class="micon">📊</span>Dashboard</button>
  <button class="mob-nav-btn"        data-nav="simulatore" onclick="navigate('simulatore')"><span class="micon">✨</span>Simula</button>
  <button class="mob-nav-btn"        data-nav="costi"      onclick="navigate('costi')"><span class="micon">💰</span>Costi</button>
  <button class="mob-nav-btn"        data-nav="produzione" onclick="navigate('produzione')"><span class="micon">🚜</span>Prod.</button>
  <button class="mob-nav-btn"        data-nav="vendite"    onclick="navigate('vendite')"><span class="micon">🏺</span>Vendite</button>
</nav>

<!-- Modal conferma elimina -->
<div id="modal-overlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:200;align-items:center;justify-content:center;">
  <div style="background:var(--white);border-radius:var(--radius-lg);padding:28px;max-width:380px;width:90%;box-shadow:0 8px 40px rgba(0,0,0,0.2);">
    <h3 style="font-family:var(--font-display);margin-bottom:10px;color:var(--terra);">Conferma eliminazione</h3>
    <p style="font-size:0.9rem;color:var(--clay);margin-bottom:20px;" id="modal-msg">Sei sicuro di voler eliminare questo elemento?</p>
    <div style="display:flex;gap:10px;">
      <button class="btn btn-ghost" style="flex:1;" onclick="closeModal()">Annulla</button>
      <button class="btn" style="flex:1;background:var(--red-harvest);color:white;" id="modal-confirm-btn">Elimina</button>
    </div>
  </div>
</div>

<div id="toast-container" class="toast-container"></div>
<script src="js/script.js"></script>
</body>
</html>
