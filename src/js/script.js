// =============================================
// AgroManager — script.js
// =============================================

'use strict';

// ── STATE ─────────────────────────────────────
const state = {
  page: 'dashboard',
  stagione: new Date().getFullYear(),
  dashboard: null,
  loading: false
};

// ── NAVIGATION ────────────────────────────────
function navigate(page) {
  state.page = page;

  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-btn, .mob-nav-btn').forEach(b => b.classList.remove('active'));

  const target = document.getElementById('page-' + page);
  if (target) target.classList.add('active');

  document.querySelectorAll(`[data-nav="${page}"]`).forEach(b => b.classList.add('active'));

  if (page === 'dashboard') loadDashboard();
  if (page === 'costi')     loadCosti();
  if (page === 'produzione') loadProduzione();
  if (page === 'vendite')   loadVendite();
}

// ── TOAST ─────────────────────────────────────
function showToast(msg, type = 'success') {
  const container = document.getElementById('toast-container');
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `<span>${type === 'success' ? '✓' : '✕'}</span> ${msg}`;
  container.appendChild(toast);
  setTimeout(() => toast.remove(), 3100);
}

// ── FORMAT ────────────────────────────────────
function fmt(n, dec = 2) {
  return parseFloat(n || 0).toLocaleString('it-IT', { minimumFractionDigits: dec, maximumFractionDigits: dec });
}

function fmtDate(d) {
  if (!d) return '—';
  return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'short', year: 'numeric' });
}

// ── API HELPERS ───────────────────────────────
async function apiFetch(url, options = {}) {
  try {
    const res = await fetch(url, options);
    const data = await res.json();
    return data;
  } catch (e) {
    console.error('API error:', e);
    showToast('Errore di comunicazione con il server', 'error');
    return null;
  }
}

// ── DASHBOARD ─────────────────────────────────
async function loadDashboard() {
  const container = document.getElementById('dash-kpi');
  const tableEl   = document.getElementById('dash-ultime-spese');
  const chartEl   = document.getElementById('dash-storico-chart');

  container.innerHTML = '<div class="loading"><div class="spinner"></div> Caricamento dati...</div>';

  const data = await apiFetch(`api/get_dashboard_data.php`);
  if (!data) return;
  state.dashboard = data;

  // KPI
  const margine = parseFloat(data.kpi.margine_netto);
  const margineClass = margine >= 0 ? 'text-green' : 'text-red';

  // Calcola litri in magazzino = prodotti - venduti (solo olio)
  const litriVenduti = (data.vendite.dettaglio || [])
    .filter(v => v.tipo === 'Olio')
    .reduce((s, v) => s + parseFloat(v.qtot || 0), 0);
  const litriMagazzino = Math.max(0, parseFloat(data.produzione.litri || 0) - litriVenduti);

  container.innerHTML = `
    <div class="kpi-grid">
      <div class="kpi-card" data-type="ricavi">
        <span class="kpi-icon">💵</span>
        <div class="kpi-value">€ ${fmt(data.vendite.totale)}</div>
        <div class="kpi-label">Ricavi Totali</div>
      </div>
      <div class="kpi-card" data-type="costi">
        <span class="kpi-icon">🧾</span>
        <div class="kpi-value">€ ${fmt(data.costi.totale)}</div>
        <div class="kpi-label">Costi Stagione</div>
      </div>
      <div class="kpi-card" data-type="margine">
        <span class="kpi-icon">📈</span>
        <div class="kpi-value ${margineClass}">€ ${fmt(Math.abs(margine))}</div>
        <div class="kpi-label">Margine Netto ${margine >= 0 ? '▲' : '▼'}</div>
      </div>
      <div class="kpi-card" data-type="produzione">
        <span class="kpi-icon">🫒</span>
        <div class="kpi-value">${fmt(data.produzione.kg, 0)}<span class="unit"> kg</span></div>
        <div class="kpi-label">Olive Raccolte</div>
      </div>
      <div class="kpi-card" data-type="magazzino">
        <span class="kpi-icon">🏺</span>
        <div class="kpi-value">${fmt(data.produzione.litri, 0)}<span class="unit"> L</span></div>
        <div class="kpi-label">Olio Prodotto al Frantoio</div>
      </div>
      <div class="kpi-card" data-type="vendite-olio">
        <span class="kpi-icon">🫙</span>
        <div class="kpi-value">${fmt(litriMagazzino, 0)}<span class="unit"> L</span></div>
        <div class="kpi-label">Olio in Magazzino</div>
      </div>
      <div class="kpi-card" data-type="litro">
        <span class="kpi-icon">💡</span>
        <div class="kpi-value">€ ${fmt(data.kpi.costo_per_litro)}</div>
        <div class="kpi-label">Costo / Litro Prodotto</div>
      </div>
    </div>`;

  // Grafico Storico
  if (data.storico && data.storico.length > 0) {
    const maxKg = Math.max(...data.storico.map(s => s.kg));
    const barsHTML = data.storico.map(s => {
      const hKg = maxKg > 0 ? Math.max(6, (s.kg / maxKg) * 90) : 6;
      const hOlio = maxKg > 0 ? Math.max(4, (s.litri / maxKg) * 90) : 4;
      return `<div class="bar-group">
        <div class="bar-set">
          <div class="bar kg" style="height:${hKg}px" title="Kg: ${fmt(s.kg, 0)}"></div>
          <div class="bar olio" style="height:${hOlio}px" title="Litri: ${fmt(s.litri, 0)}"></div>
        </div>
        <div class="bar-label">${s.stagione}</div>
      </div>`;
    }).join('');

    chartEl.innerHTML = `
      <div class="chart-bars">${barsHTML}</div>
      <div class="chart-legend">
        <div class="legend-item"><div class="legend-dot kg"></div> Kg Olive</div>
        <div class="legend-item"><div class="legend-dot olio"></div> Litri Olio</div>
      </div>`;
  } else {
    chartEl.innerHTML = '<div class="empty-state"><div class="empty-icon">📊</div><p>Nessun dato storico disponibile</p></div>';
  }

  // Costi per categoria
  const catEl = document.getElementById('dash-cat-chart');
  if (data.costi.per_categoria && data.costi.per_categoria.length > 0) {
    const totCosti = parseFloat(data.costi.totale) || 1;
    const catMap = { 'Input Tecnici': 'input', 'Logistica': 'logist', 'Terzi': 'terzi' };
    catEl.innerHTML = data.costi.per_categoria.map(c => {
      const pct = (parseFloat(c.tot) / totCosti) * 100;
      const cls = catMap[c.categoria] || 'input';
      return `<div class="cat-row">
        <div class="cat-name">${c.categoria.split(' ')[0]}</div>
        <div class="cat-bar-wrap"><div class="cat-bar ${cls}" style="width:${pct}%"></div></div>
        <div class="cat-amount">€ ${fmt(c.tot)}</div>
      </div>`;
    }).join('');
  } else {
    catEl.innerHTML = '<p class="text-clay fs-sm text-center" style="padding:16px;">Nessuna spesa registrata</p>';
  }

  // Ultime spese
  if (data.ultime_spese && data.ultime_spese.length > 0) {
    tableEl.innerHTML = data.ultime_spese.map(s => {
      const catClass = { 'Input Tecnici': 'input', 'Logistica': 'logist', 'Terzi': 'terzi' };
      return `<tr>
        <td>${fmtDate(s.data_spesa)}</td>
        <td><span class="badge badge-${catClass[s.categoria] || 'input'}">${s.categoria}</span></td>
        <td>${s.descrizione || '—'}</td>
        <td class="text-right fw-700">€ ${fmt(s.importo)}</td>
      </tr>`;
    }).join('');
  } else {
    tableEl.innerHTML = '<tr><td colspan="4"><div class="empty-state"><div class="empty-icon">🧾</div><p>Nessuna spesa registrata</p></div></td></tr>';
  }

  // Punto di pareggio
  const breakEl = document.getElementById('dash-pareggio');
  if (data.produzione.litri > 0 && data.costi.totale > 0) {
    const pp = (parseFloat(data.costi.totale) / parseFloat(data.produzione.litri));
    breakEl.innerHTML = `
      <div class="pareggio-box">
        <h4>📍 Punto di Pareggio Olio</h4>
        <div class="pareggio-value">
          <span class="currency">€ </span>${fmt(pp)}<span class="unit"> / litro</span>
        </div>
        <p class="fs-sm text-clay mt-16">Devi vendere l'olio a più di <strong>€ ${fmt(pp)}/L</strong> per coprire tutti i costi di stagione.</p>
      </div>`;
  } else {
    breakEl.innerHTML = '';
  }
}

// ── COSTI ─────────────────────────────────────
async function loadCosti() {
  const tbody = document.getElementById('costi-tbody');
  tbody.innerHTML = '<tr><td colspan="5"><div class="loading"><div class="spinner"></div></div></td></tr>';

  const data = await apiFetch(`api/get_dashboard_data.php`);
  if (!data) return;

  const catMap = { 'Input Tecnici': 'input', 'Logistica': 'logist', 'Terzi': 'terzi' };

  // We'll show all costs by fetching separately
  const res = await apiFetch('api/get_costi_list.php');
  const costi = res?.costi || [];

  if (costi.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5"><div class="empty-state"><div class="empty-icon">🧾</div><p>Nessuna spesa registrata questa stagione</p></div></td></tr>';
    return;
  }

  tbody.innerHTML = costi.map(c => `<tr>
    <td>${fmtDate(c.data_spesa)}</td>
    <td><span class="badge badge-${catMap[c.categoria] || 'input'}">${c.categoria}</span></td>
    <td>${c.descrizione || '—'}</td>
    <td>${c.campo || '—'}</td>
    <td class="text-right fw-700">€ ${fmt(c.importo)}</td>
  </tr>`).join('');

  // Update totale
  const tot = costi.reduce((s, c) => s + parseFloat(c.importo), 0);
  const totEl = document.getElementById('costi-totale');
  if (totEl) totEl.textContent = `€ ${fmt(tot)}`;
}

async function saveCosto(e) {
  e.preventDefault();
  const form = e.target;
  const fd = new FormData(form);
  
  const data = await apiFetch('api/save_costo.php', { method: 'POST', body: fd });
  if (data?.success) {
    showToast('Spesa registrata con successo ✓');
    form.reset();
    form.querySelector('[name="data"]').value = new Date().toISOString().split('T')[0];
    loadCosti();
    if (state.page === 'dashboard') loadDashboard();
  } else {
    showToast(data?.message || 'Errore nel salvataggio', 'error');
  }
}

// ── PRODUZIONE ────────────────────────────────
async function loadProduzione() {
  const tbody = document.getElementById('prod-tbody');
  tbody.innerHTML = '<tr><td colspan="6"><div class="loading"><div class="spinner"></div></div></td></tr>';

  const res = await apiFetch('api/get_produzione_list.php');
  const prods = res?.produzione || [];

  if (prods.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><div class="empty-icon">🫒</div><p>Nessuna produzione registrata questa stagione</p></div></td></tr>';
    return;
  }

  tbody.innerHTML = prods.map(p => `<tr>
    <td>${fmtDate(p.data_raccolta)}</td>
    <td><strong>${p.campo}</strong></td>
    <td class="text-right">${fmt(p.kg_raccolti, 0)} kg</td>
    <td class="text-right">${p.resa_percentuale ? fmt(p.resa_percentuale) + '%' : '—'}</td>
    <td class="text-right">${p.litri_olio ? fmt(p.litri_olio) + ' L' : '—'}</td>
    <td>${p.note || '—'}</td>
  </tr>`).join('');
}

async function saveProduzione(e) {
  e.preventDefault();
  const form = e.target;
  const fd = new FormData(form);

  const data = await apiFetch('api/save_produzione.php', { method: 'POST', body: fd });
  if (data?.success) {
    showToast(`Produzione registrata! Litri stimati: ${fmt(data.litri_stimati)} L`);
    form.reset();
    form.querySelector('[name="data_raccolta"]').value = new Date().toISOString().split('T')[0];
    form.querySelector('[name="stagione"]').value = state.stagione;
    loadProduzione();
    updateResa();
  } else {
    showToast(data?.message || 'Errore', 'error');
  }
}

function updateResa() {
  const kg = parseFloat(document.getElementById('prod_kg')?.value) || 0;
  const resa = parseFloat(document.getElementById('prod_resa')?.value) || 0;
  const preview = document.getElementById('prod_litri_preview');
  if (preview) {
    const litri = kg * (resa / 100);
    preview.textContent = litri > 0 ? `≈ ${fmt(litri)} litri stimati` : '';
  }
}

// ── VENDITE ───────────────────────────────────
function unitaLabel(unita) {
  if (unita === 'quarta') return 'quarte';
  if (unita === 'litri')  return 'L';
  return 'kg';
}

// Quando cambia tipo (Olive/Olio) aggiorna le opzioni unità misura e label
function onTipoVenditaChange() {
  const tipo = document.getElementById('vend_tipo')?.value;
  const unitaSel = document.getElementById('vend_unita');
  const prezzoLabel = document.getElementById('vend_prezzo_label');
  if (!unitaSel) return;
  if (tipo === 'Olio') {
    unitaSel.innerHTML = '<option value="litri">Litri (L)</option>';
    if (prezzoLabel) prezzoLabel.textContent = 'Prezzo (€/litro)';
  } else {
    unitaSel.innerHTML = '<option value="kg">Kg</option><option value="quarta">Quarte (1 quarta = 12,5 kg)</option>';
    if (prezzoLabel) prezzoLabel.textContent = 'Prezzo (€/unità)';
  }
  updateRicavo();
}

async function loadVendite() {
  const tbody = document.getElementById('vendite-tbody');
  tbody.innerHTML = '<tr><td colspan="6"><div class="loading"><div class="spinner"></div></div></td></tr>';

  const res = await apiFetch('api/get_vendite_list.php');
  const vendite = res?.vendite || [];

  if (vendite.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><div class="empty-icon">💰</div><p>Nessuna vendita registrata questa stagione</p></div></td></tr>';
    return;
  }

  tbody.innerHTML = vendite.map(v => {
    const ul = unitaLabel(v.unita_misura);
    const qtaExtra = (v.tipo === 'Olive' && v.unita_misura === 'quarta')
      ? `<br><span class="fs-sm text-clay">(${fmt(v.kg_effettivi, 0)} kg effettivi)</span>`
      : '';
    return `<tr>
      <td>${fmtDate(v.data_vendita)}</td>
      <td><span class="badge badge-${v.tipo === 'Olio' ? 'olio' : 'olive'}">${v.tipo}</span></td>
      <td class="text-right">${fmt(v.quantita)} ${ul}${qtaExtra}</td>
      <td class="text-right">€ ${fmt(v.prezzo_unitario)}</td>
      <td class="text-right fw-700 text-green">€ ${fmt(v.ricavo_totale)}</td>
      <td>${v.acquirente || '<span class="text-clay">—</span>'}</td>
    </tr>`;
  }).join('');

  const tot = vendite.reduce((s, v) => s + parseFloat(v.ricavo_totale), 0);
  const totEl = document.getElementById('vendite-totale');
  if (totEl) totEl.textContent = `€ ${fmt(tot)}`;
}

async function saveVendita(e) {
  e.preventDefault();
  const form = e.target;
  const fd = new FormData(form);

  const data = await apiFetch('api/save_vendita.php', { method: 'POST', body: fd });
  if (data?.success) {
    const kgInfo = data.kg_effettivi && parseFloat(data.kg_effettivi) !== parseFloat(fd.get('quantita'))
      ? ` (${fmt(data.kg_effettivi)} kg)` : '';
    showToast(`Vendita registrata! Ricavo: € ${fmt(data.ricavo)}${kgInfo}`);
    form.reset();
    form.querySelector('[name="data_vendita"]').value = new Date().toISOString().split('T')[0];
    onTipoVenditaChange(); // reset unità misura al default
    loadVendite();
  } else {
    showToast(data?.message || 'Errore nel salvataggio', 'error');
  }
}

function updateRicavo() {
  const q = parseFloat(document.getElementById('vend_quantita')?.value) || 0;
  const p = parseFloat(document.getElementById('vend_prezzo')?.value) || 0;
  const unita = document.getElementById('vend_unita')?.value || 'kg';
  const preview = document.getElementById('vend_ricavo_preview');
  if (preview && q > 0 && p > 0) {
    const kgEff = unita === 'quarta' ? q * 12.5 : q;
    const extra = unita === 'quarta' ? ` — ${fmt(kgEff, 0)} kg effettivi` : '';
    preview.textContent = `= € ${fmt(q * p)} totale${extra}`;
  } else if (preview) {
    preview.textContent = '';
  }
}

// ── SIMULATORE ────────────────────────────────
async function calcolaConvenienza() {
  const btn = document.getElementById('sim-btn');
  btn.textContent = 'Calcolo...';
  btn.disabled = true;

  const dati = {
    kg:             document.getElementById('sim_kg').value,
    resa:           document.getElementById('sim_resa').value,
    prezzo_olive:   document.getElementById('sim_p_olive').value,
    prezzo_olio:    document.getElementById('sim_p_olio').value,
    costo_molitura: document.getElementById('sim_costo_mol').value
  };

  const result = await apiFetch('api/simulatore.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(dati)
  });

  btn.textContent = '▶ Calcola Strategia';
  btn.disabled = false;

  if (!result || result.error) {
    showToast(result?.error || 'Errore nel calcolo', 'error');
    return;
  }

  const box = document.getElementById('sim-result');
  box.classList.add('visible');

  const oilWins = result.conviene_olio;

  box.innerHTML = `
    <div class="result-compare">
      <div class="result-option ${!oilWins ? 'winner' : ''}">
        <div class="opt-label">🫒 Vendi Olive</div>
        <div class="opt-value">€ ${fmt(result.vendita_olive)}</div>
        ${!oilWins ? '<div style="font-size:0.7rem;color:var(--gold);margin-top:4px;font-weight:600;">★ CONSIGLIATO</div>' : ''}
      </div>
      <div class="result-vs">VS</div>
      <div class="result-option ${oilWins ? 'winner' : ''}">
        <div class="opt-label">🫙 Produci Olio</div>
        <div class="opt-value">€ ${fmt(result.vendita_olio)}</div>
        ${oilWins ? '<div style="font-size:0.7rem;color:var(--gold);margin-top:4px;font-weight:600;">★ CONSIGLIATO</div>' : ''}
      </div>
    </div>
    <div class="result-verdict ${oilWins ? 'olio' : 'olive'}">
      <strong>${oilWins ? '🫙 Conviene produrre Olio!' : '🫒 Conviene vendere Olive!'}</strong>
      Vantaggio economico: <strong>€ ${fmt(result.differenza)}</strong> (+${result.percentuale}%)
    </div>
    <div class="result-detail" style="margin-top:10px;">
      <div class="detail-pill" style="background:rgba(90,107,62,0.12);color:var(--olive-dark);border-color:rgba(90,107,62,0.2);">
        🫙 Litri stimati: ${fmt(result.litri_olio)} L
      </div>
      <div class="detail-pill" style="background:rgba(107,63,42,0.12);color:var(--bark);border-color:rgba(107,63,42,0.2);">
        ⚙️ Costo molitura: € ${fmt(result.costo_molitura)}
      </div>
    </div>`;
}

// ── INIT ──────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  const oggi = new Date().toISOString().split('T')[0];
  document.querySelectorAll('input[type="date"]').forEach(el => {
    if (!el.value) el.value = oggi;
  });
  document.querySelectorAll('[name="stagione"]').forEach(el => {
    el.value = state.stagione;
  });

  // Inizializza il selettore unità vendita
  onTipoVenditaChange();

  // Avvia dashboard
  navigate('dashboard');
});
