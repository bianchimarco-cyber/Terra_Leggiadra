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

<!-- ══ TOPBAR ════════════════════════════════ -->
<header class="topbar">
  <a class="topbar-brand" href="#" onclick="navigate('dashboard')">
    <div class="logo-icon">🫒</div>
    <div>
      <h1>AgroManager</h1>
      <span>Gestionale Olivicolo</span>
    </div>
  </a>

  <nav class="topbar-nav">
    <button class="nav-btn active" data-nav="dashboard" onclick="navigate('dashboard')">📊 Dashboard</button>
    <button class="nav-btn" data-nav="simulatore" onclick="navigate('simulatore')">✨ Simulatore</button>
    <button class="nav-btn" data-nav="costi" onclick="navigate('costi')">💰 Costi</button>
    <button class="nav-btn" data-nav="produzione" onclick="navigate('produzione')">🚜 Produzione</button>
    <button class="nav-btn" data-nav="vendite" onclick="navigate('vendite')">🏺 Vendite</button>
  </nav>

  <div class="topbar-right">
    <select id="stagione-select" class="stagione-badge" style="cursor:pointer;background:transparent;"></select>
  </div>
</header>

<!-- ══ MAIN ══════════════════════════════════ -->
<main class="app-container">

  <!-- ── DASHBOARD ──────────────────────────── -->
  <div id="page-dashboard" class="page active">
    <div class="section-header">
      <h2><small>Panoramica</small>Dashboard Stagionale</h2>
    </div>

    <div id="dash-kpi">
      <div class="loading"><div class="spinner"></div> Caricamento...</div>
    </div>

    <div class="grid-main-side mt-20">
      <!-- Grafico storico -->
      <div class="card">
        <div class="card-header">
          <div class="card-title"><span class="icon">📈</span> Andamento Storico</div>
          <div class="card-subtitle">Kg olive e litri olio per stagione</div>
        </div>
        <div id="dash-storico-chart">
          <div class="loading"><div class="spinner"></div></div>
        </div>
      </div>

      <!-- Pannello laterale -->
      <div style="display:flex;flex-direction:column;gap:20px;">
        <!-- Costi per categoria -->
        <div class="card">
          <div class="card-header">
            <div class="card-title"><span class="icon">🥧</span> Costi per Categoria</div>
          </div>
          <div class="cat-chart" id="dash-cat-chart">
            <div class="loading"><div class="spinner"></div></div>
          </div>
        </div>

        <!-- Punto di pareggio -->
        <div id="dash-pareggio"></div>
      </div>
    </div>

    <!-- Ultime spese -->
    <div class="card mt-20">
      <div class="card-header">
        <div class="card-title"><span class="icon">🧾</span> Ultime Spese</div>
        <button class="btn btn-ghost btn-sm" onclick="navigate('costi')">Vedi tutte →</button>
      </div>
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Data</th><th>Categoria</th><th>Descrizione</th><th class="text-right">Importo</th>
            </tr>
          </thead>
          <tbody id="dash-ultime-spese">
            <tr><td colspan="4"><div class="loading"><div class="spinner"></div></div></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- ── SIMULATORE ─────────────────────────── -->
  <div id="page-simulatore" class="page">
    <div class="section-header">
      <h2><small>Strumento Smart</small>Simulatore Decisionale</h2>
    </div>

    <div class="grid-main-side">
      <div>
        <div class="simulatore-hero">
          <h2>✨ Olive o Olio?</h2>
          <p>Inserisci i dati di mercato per scoprire la strategia più redditizia per la tua stagione.</p>

          <div class="sim-inputs" style="margin-top:20px;">
            <div class="sim-input-group">
              <label>🏋️ Kg Olive Raccolti</label>
              <input type="number" id="sim_kg" value="1000" min="1">
            </div>
            <div class="sim-input-group">
              <label>📊 Resa Stimata (%)</label>
              <input type="number" id="sim_resa" value="15" step="0.5" min="1" max="30">
            </div>
            <div class="sim-input-group">
              <label>🫒 Prezzo Olive (€/kg)</label>
              <input type="number" id="sim_p_olive" value="0.90" step="0.05">
            </div>
            <div class="sim-input-group">
              <label>🫙 Prezzo Olio (€/litro)</label>
              <input type="number" id="sim_p_olio" value="12.00" step="0.50">
            </div>
            <div class="sim-input-group" style="grid-column:1/-1">
              <label>⚙️ Costo Molitura (€/quintale)</label>
              <input type="number" id="sim_costo_mol" value="16.00" step="0.50">
            </div>
          </div>

          <button class="btn-sim" id="sim-btn" onclick="calcolaConvenienza()">▶ Calcola Strategia</button>
        </div>

        <!-- Risultato -->
        <div id="sim-result"></div>
      </div>

      <!-- Pannello info -->
      <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
          <div class="card-header">
            <div class="card-title"><span class="icon">📖</span> Come funziona</div>
          </div>
          <p class="fs-sm text-clay" style="line-height:1.7;">
            Il simulatore confronta due scenari economici in base ai prezzi di mercato correnti:
          </p>
          <div style="margin-top:14px;display:flex;flex-direction:column;gap:10px;">
            <div style="padding:12px;background:var(--fog);border-radius:var(--radius-sm);">
              <div style="font-size:0.8rem;font-weight:600;color:var(--terra);margin-bottom:4px;">🫒 Scenario 1 — Vendita Olive</div>
              <div class="fs-sm text-clay">Ricavo = Kg × Prezzo/kg</div>
            </div>
            <div style="padding:12px;background:var(--fog);border-radius:var(--radius-sm);">
              <div style="font-size:0.8rem;font-weight:600;color:var(--terra);margin-bottom:4px;">🫙 Scenario 2 — Produzione Olio</div>
              <div class="fs-sm text-clay">Litri = Kg × Resa%<br>Ricavo Netto = (Litri × Prezzo/L) − Costo Molitura</div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <div class="card-title"><span class="icon">💡</span> Prezzi di Riferimento</div>
          </div>
          <div style="display:flex;flex-direction:column;gap:8px;">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--cream);">
              <span class="fs-sm text-clay">Olive da mensa</span>
              <span class="fs-sm fw-700">€ 0.80–1.20/kg</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--cream);">
              <span class="fs-sm text-clay">Olive da olio</span>
              <span class="fs-sm fw-700">€ 0.60–1.00/kg</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--cream);">
              <span class="fs-sm text-clay">Olio EVO (ingrosso)</span>
              <span class="fs-sm fw-700">€ 8.00–14.00/L</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;">
              <span class="fs-sm text-clay">Molitura media</span>
              <span class="fs-sm fw-700">€ 12–22/quintale</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ── COSTI ──────────────────────────────── -->
  <div id="page-costi" class="page">
    <div class="section-header">
      <h2><small>Contabilità</small>Registro Spese</h2>
      <div id="costi-totale-wrap" style="font-family:var(--font-display);font-size:1.1rem;color:var(--bark);">
        Totale: <span id="costi-totale" class="fw-700">—</span>
      </div>
    </div>

    <div class="grid-main-side">
      <!-- Tabella costi -->
      <div class="card">
        <div class="card-header">
          <div class="card-title"><span class="icon">📋</span> Spese Registrate</div>
        </div>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Data</th><th>Categoria</th><th>Descrizione</th><th>Campo</th><th class="text-right">Importo</th>
              </tr>
            </thead>
            <tbody id="costi-tbody">
              <tr><td colspan="5"><div class="loading"><div class="spinner"></div></div></td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Form nuova spesa -->
      <div class="card">
        <div class="card-header">
          <div class="card-title"><span class="icon">➕</span> Nuova Spesa</div>
        </div>
        <form onsubmit="saveCosto(event)">
          <div class="form-group">
            <label class="form-label">Data Spesa</label>
            <input type="date" name="data" class="form-input" required>
          </div>
          <div class="form-group">
            <label class="form-label">Categoria</label>
            <select name="categoria" class="form-select" required>
              <option value="Input Tecnici">🌿 Input Tecnici</option>
              <option value="Logistica" selected>🚜 Logistica e Mezzi</option>
              <option value="Terzi">🤝 Servizi Terzi</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Descrizione</label>
            <input type="text" name="descrizione" class="form-input" placeholder="Es. Gasolio Trattore, Concime NPK...">
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Importo (€)</label>
              <input type="number" name="importo" class="form-input" step="0.01" min="0.01" placeholder="0.00" required>
            </div>
            <div class="form-group">
              <label class="form-label">Campo (opz.)</label>
              <input type="text" name="campo" class="form-input" placeholder="Es. Nord, Sud...">
            </div>
          </div>
          <button type="submit" class="btn btn-primary">💾 Registra Spesa</button>
        </form>
      </div>
    </div>
  </div>

  <!-- ── PRODUZIONE ─────────────────────────── -->
  <div id="page-produzione" class="page">
    <div class="section-header">
      <h2><small>Raccolta &amp; Frantoio</small>Gestione Produzione</h2>
    </div>

    <div class="grid-main-side">
      <!-- Tabella produzione -->
      <div class="card">
        <div class="card-header">
          <div class="card-title"><span class="icon">📋</span> Produzioni Registrate</div>
        </div>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Data</th><th>Campo</th><th class="text-right">Kg Raccolti</th>
                <th class="text-right">Resa %</th><th class="text-right">Litri Olio</th><th>Note</th>
              </tr>
            </thead>
            <tbody id="prod-tbody">
              <tr><td colspan="6"><div class="loading"><div class="spinner"></div></div></td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Form nuova raccolta -->
      <div class="card">
        <div class="card-header">
          <div class="card-title"><span class="icon">➕</span> Registra Raccolta</div>
        </div>
        <form onsubmit="saveProduzione(event)">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Data Raccolta</label>
              <input type="date" name="data_raccolta" class="form-input" required>
            </div>
            <div class="form-group">
              <label class="form-label">Stagione</label>
              <input type="number" name="stagione" class="form-input" required>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Campo / Appezzamento</label>
            <input type="text" name="campo" class="form-input" placeholder="Es. Nord, Collina, Podere A..." required>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Kg Raccolti</label>
              <input type="number" id="prod_kg" name="kg_raccolti" class="form-input" step="0.5" min="1" placeholder="1000" required oninput="updateResa()">
            </div>
            <div class="form-group">
              <label class="form-label">Resa Stimata (%)</label>
              <input type="number" id="prod_resa" name="resa_percentuale" class="form-input" step="0.1" min="0" max="30" placeholder="15.0" oninput="updateResa()">
              <div class="form-hint" id="prod_litri_preview" style="color:var(--olive);font-weight:500;"></div>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Note (opzionale)</label>
            <textarea name="note" class="form-textarea" rows="2" placeholder="Qualità, condizioni meteo..."></textarea>
          </div>
          <button type="submit" class="btn btn-primary">💾 Registra Produzione</button>
        </form>
      </div>
    </div>
  </div>

  <!-- ── VENDITE ────────────────────────────── -->
  <div id="page-vendite" class="page">
    <div class="section-header">
      <h2><small>Magazzino &amp; Ricavi</small>Registro Vendite</h2>
      <div style="font-family:var(--font-display);font-size:1.1rem;color:var(--olive-dark);">
        Ricavi: <span id="vendite-totale" class="fw-700">—</span>
      </div>
    </div>

    <div class="grid-main-side">
      <!-- Tabella vendite -->
      <div class="card">
        <div class="card-header">
          <div class="card-title"><span class="icon">📋</span> Vendite Registrate</div>
        </div>
        <div class="table-wrapper">
          <table>
            <thead>
              <tr>
                <th>Data</th><th>Tipo</th><th class="text-right">Quantità</th>
                <th class="text-right">Prezzo/Unità</th><th class="text-right">Ricavo</th>
              </tr>
            </thead>
            <tbody id="vendite-tbody">
              <tr><td colspan="5"><div class="loading"><div class="spinner"></div></div></td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Form nuova vendita -->
      <div class="card">
        <div class="card-header">
          <div class="card-title"><span class="icon">➕</span> Registra Vendita</div>
        </div>
        <form onsubmit="saveVendita(event)">
          <div class="form-group">
            <label class="form-label">Data Vendita</label>
            <input type="date" name="data_vendita" class="form-input" required>
          </div>
          <div class="form-group">
            <label class="form-label">Tipo Prodotto</label>
            <select name="tipo" class="form-select" required>
              <option value="Olio">🫙 Olio EVO</option>
              <option value="Olive">🫒 Olive</option>
            </select>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Quantità</label>
              <input type="number" id="vend_quantita" name="quantita" class="form-input" step="0.1" min="0.1" placeholder="50" required oninput="updateRicavo()">
            </div>
            <div class="form-group">
              <label class="form-label">Prezzo Unitario (€)</label>
              <input type="number" id="vend_prezzo" name="prezzo_unitario" class="form-input" step="0.01" placeholder="12.00" required oninput="updateRicavo()">
              <div class="form-hint" id="vend_ricavo_preview" style="color:var(--olive);font-weight:500;"></div>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Acquirente (opzionale)</label>
            <input type="text" name="acquirente" class="form-input" placeholder="Es. Privato, Ristorante...">
          </div>
          <button type="submit" class="btn btn-primary">💾 Registra Vendita</button>
        </form>
      </div>
    </div>
  </div>

</main>

<!-- ══ MOBILE NAV ════════════════════════════ -->
<nav class="mobile-nav">
  <button class="mob-nav-btn active" data-nav="dashboard" onclick="navigate('dashboard')">
    <span class="micon">📊</span>Dashboard
  </button>
  <button class="mob-nav-btn" data-nav="simulatore" onclick="navigate('simulatore')">
    <span class="micon">✨</span>Simula
  </button>
  <button class="mob-nav-btn" data-nav="costi" onclick="navigate('costi')">
    <span class="micon">💰</span>Costi
  </button>
  <button class="mob-nav-btn" data-nav="produzione" onclick="navigate('produzione')">
    <span class="micon">🚜</span>Prod.
  </button>
  <button class="mob-nav-btn" data-nav="vendite" onclick="navigate('vendite')">
    <span class="micon">🏺</span>Vendite
  </button>
</nav>

<!-- ══ TOAST CONTAINER ═══════════════════════ -->
<div id="toast-container" class="toast-container"></div>

<script src="js/script.js"></script>
</body>
</html>
