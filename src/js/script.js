'use strict';

// ── STATE ─────────────────────────────────────
const state = { page: 'dashboard', stagione: new Date().getFullYear() };

// ── NAVIGATION ────────────────────────────────
function navigate(page) {
  state.page = page;
  document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-btn,.mob-nav-btn').forEach(b => b.classList.remove('active'));
  const target = document.getElementById('page-' + page);
  if (target) target.classList.add('active');
  document.querySelectorAll('[data-nav="' + page + '"]').forEach(b => b.classList.add('active'));
  if (page === 'dashboard')  loadDashboard();
  if (page === 'costi')      loadCosti();
  if (page === 'produzione') loadProduzione();
  if (page === 'vendite')    loadVendite();
}

// ── TOAST ─────────────────────────────────────
function showToast(msg, type) {
  type = type || 'success';
  const c = document.getElementById('toast-container');
  const t = document.createElement('div');
  t.className = 'toast ' + type;
  t.innerHTML = '<span>' + (type === 'success' ? '✓' : '✕') + '</span> ' + msg;
  c.appendChild(t);
  setTimeout(function() { t.remove(); }, 3100);
}

// ── FORMAT ────────────────────────────────────
function fmt(n, dec) {
  dec = (dec === undefined) ? 2 : dec;
  return parseFloat(n || 0).toLocaleString('it-IT', { minimumFractionDigits: dec, maximumFractionDigits: dec });
}
function fmtDate(d) {
  if (!d) return '—';
  return new Date(d).toLocaleDateString('it-IT', { day: '2-digit', month: 'short', year: 'numeric' });
}

// ── API ───────────────────────────────────────
async function apiFetch(url, options) {
  try {
    const res = await fetch(url, options || {});
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return await res.json();
  } catch(e) {
    console.error('apiFetch error:', url, e);
    showToast('Errore di comunicazione con il server', 'error');
    return null;
  }
}

async function apiPost(url, formData) {
  return apiFetch(url, { method: 'POST', body: formData });
}

// ── MODAL CONFERMA ────────────────────────────
let _modalCallback = null;
function openModal(msg, cb) {
  document.getElementById('modal-msg').textContent = msg;
  document.getElementById('modal-overlay').style.display = 'flex';
  _modalCallback = cb;
}
function closeModal() {
  document.getElementById('modal-overlay').style.display = 'none';
  _modalCallback = null;
}
document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('modal-confirm-btn').addEventListener('click', function() {
    if (_modalCallback) _modalCallback();
    closeModal();
  });
});

// ══════════════════════════════════════════════
// DASHBOARD
// ══════════════════════════════════════════════
async function loadDashboard() {
  const kpiEl   = document.getElementById('dash-kpi');
  const tableEl = document.getElementById('dash-ultime-spese');
  const chartEl = document.getElementById('dash-storico-chart');
  const catEl   = document.getElementById('dash-cat-chart');
  kpiEl.innerHTML = '<div class="loading"><div class="spinner"></div> Caricamento...</div>';

  const data = await apiFetch('api/get_dashboard_data.php');
  if (!data) return;

  const margine      = parseFloat(data.kpi.margine_netto);
  const margineClass = margine >= 0 ? 'text-green' : 'text-red';
  const litriMag     = parseFloat(data.kpi.litri_magazzino || 0);

  kpiEl.innerHTML =
    '<div class="kpi-grid">' +
    kpiCard('ricavi',      '💵', '€ ' + fmt(data.vendite.totale),                  'Ricavi Totali') +
    kpiCard('costi',       '🧾', '€ ' + fmt(data.costi.totale),                    'Costi Stagione') +
    kpiCard('margine',     '📈', '<span class="' + margineClass + '">€ ' + fmt(Math.abs(margine)) + '</span>', 'Margine Netto ' + (margine >= 0 ? '▲' : '▼')) +
    kpiCard('produzione',  '🫒', fmt(data.produzione.quarte, 1) + '<span class="unit"> quarte</span>', 'Olive Raccolte (' + fmt(data.produzione.kg, 0) + ' kg)') +
    kpiCard('magazzino',   '🏺', fmt(data.produzione.litri, 0) + '<span class="unit"> L</span>',      'Olio Prodotto al Frantoio') +
    kpiCard('vendite-olio','🫙', fmt(litriMag, 0) + '<span class="unit"> L</span>',                   'Olio in Magazzino') +
    kpiCard('litro',       '💡', '€ ' + fmt(data.kpi.costo_per_litro),              'Costo / Litro Prodotto') +
    '</div>';

  // Storico
  if (data.storico && data.storico.length > 0) {
    const maxKg = Math.max.apply(null, data.storico.map(function(s) { return s.kg; }));
    let bars = '';
    data.storico.forEach(function(s) {
      const hKg   = maxKg > 0 ? Math.max(6, (s.kg   / maxKg) * 90) : 6;
      const hOlio = maxKg > 0 ? Math.max(4, (s.litri / maxKg) * 90) : 4;
      bars += '<div class="bar-group"><div class="bar-set">' +
        '<div class="bar kg"   style="height:' + hKg   + 'px" title="Kg: '    + fmt(s.kg, 0)    + '"></div>' +
        '<div class="bar olio" style="height:' + hOlio + 'px" title="Litri: ' + fmt(s.litri, 0) + '"></div>' +
        '</div><div class="bar-label">' + s.stagione + '</div></div>';
    });
    chartEl.innerHTML = '<div class="chart-bars">' + bars + '</div>' +
      '<div class="chart-legend"><div class="legend-item"><div class="legend-dot kg"></div> Kg Olive</div><div class="legend-item"><div class="legend-dot olio"></div> Litri Olio</div></div>';
  } else {
    chartEl.innerHTML = '<div class="empty-state"><div class="empty-icon">📊</div><p>Nessun dato storico</p></div>';
  }

  // Costi per categoria
  if (data.costi.per_categoria && data.costi.per_categoria.length > 0) {
    const totC = parseFloat(data.costi.totale) || 1;
    const map  = { 'Input Tecnici': 'input', 'Logistica': 'logist', 'Terzi': 'terzi' };
    let html = '';
    data.costi.per_categoria.forEach(function(c) {
      const pct = (parseFloat(c.tot) / totC) * 100;
      const cls = map[c.categoria] || 'input';
      html += '<div class="cat-row"><div class="cat-name">' + c.categoria.split(' ')[0] + '</div>' +
        '<div class="cat-bar-wrap"><div class="cat-bar ' + cls + '" style="width:' + pct + '%"></div></div>' +
        '<div class="cat-amount">€ ' + fmt(c.tot) + '</div></div>';
    });
    catEl.innerHTML = html;
  } else {
    catEl.innerHTML = '<p class="fs-sm text-clay text-center" style="padding:16px;">Nessuna spesa</p>';
  }

  // Ultime spese
  if (data.ultime_spese && data.ultime_spese.length > 0) {
    const catCls = { 'Input Tecnici': 'input', 'Logistica': 'logist', 'Terzi': 'terzi' };
    tableEl.innerHTML = data.ultime_spese.map(function(s) {
      return '<tr><td>' + fmtDate(s.data_spesa) + '</td>' +
        '<td><span class="badge badge-' + (catCls[s.categoria] || 'input') + '">' + s.categoria + '</span></td>' +
        '<td>' + (s.descrizione || '—') + '</td>' +
        '<td class="text-right fw-700">€ ' + fmt(s.importo) + '</td></tr>';
    }).join('');
  } else {
    tableEl.innerHTML = '<tr><td colspan="4"><div class="empty-state"><div class="empty-icon">🧾</div><p>Nessuna spesa</p></div></td></tr>';
  }

  // Punto di pareggio
  const breakEl = document.getElementById('dash-pareggio');
  if (parseFloat(data.produzione.litri) > 0 && parseFloat(data.costi.totale) > 0) {
    const pp = parseFloat(data.costi.totale) / parseFloat(data.produzione.litri);
    breakEl.innerHTML = '<div class="pareggio-box">' +
      '<h4>📍 Punto di Pareggio Olio</h4>' +
      '<div class="pareggio-value"><span class="currency">€ </span>' + fmt(pp) + '<span class="unit"> / litro</span></div>' +
      '<p class="fs-sm text-clay mt-16">Devi vendere a più di <strong>€ ' + fmt(pp) + '/L</strong> per coprire tutti i costi.</p></div>';
  } else {
    breakEl.innerHTML = '';
  }
}

function kpiCard(type, icon, value, label) {
  return '<div class="kpi-card" data-type="' + type + '">' +
    '<span class="kpi-icon">' + icon + '</span>' +
    '<div class="kpi-value">' + value + '</div>' +
    '<div class="kpi-label">' + label + '</div></div>';
}

// ══════════════════════════════════════════════
// COSTI
// ══════════════════════════════════════════════
async function loadCosti() {
  const tbody = document.getElementById('costi-tbody');
  tbody.innerHTML = '<tr><td colspan="6"><div class="loading"><div class="spinner"></div></div></td></tr>';
  const res = await apiFetch('api/get_costi_list.php');
  const costi = (res && res.costi) ? res.costi : [];
  const catCls = { 'Input Tecnici': 'input', 'Logistica': 'logist', 'Terzi': 'terzi' };

  if (costi.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><div class="empty-icon">🧾</div><p>Nessuna spesa registrata</p></div></td></tr>';
    document.getElementById('costi-totale').textContent = '€ 0,00';
    return;
  }
  tbody.innerHTML = costi.map(function(c) {
    return '<tr>' +
      '<td>' + fmtDate(c.data_spesa) + '</td>' +
      '<td><span class="badge badge-' + (catCls[c.categoria]||'input') + '">' + c.categoria + '</span></td>' +
      '<td>' + (c.descrizione || '—') + '</td>' +
      '<td>' + (c.campo || '—') + '</td>' +
      '<td class="text-right fw-700">€ ' + fmt(c.importo) + '</td>' +
      '<td class="text-center">' +
        '<button class="btn btn-ghost btn-sm" onclick="editCosto(' + JSON.stringify(c) + ')">✏️</button> ' +
        '<button class="btn btn-sm" style="background:var(--red-harvest);color:white;" onclick="deleteCosto(' + c.id + ')">🗑</button>' +
      '</td></tr>';
  }).join('');
  const tot = costi.reduce(function(s, c) { return s + parseFloat(c.importo); }, 0);
  document.getElementById('costi-totale').textContent = '€ ' + fmt(tot);
}

async function saveCosto(e) {
  e.preventDefault();
  const id = document.getElementById('costo_id').value;
  const fd = new FormData(e.target);
  const url = id ? 'api/update_costo.php' : 'api/save_costo.php';
  if (id) fd.append('id', id);
  const data = await apiPost(url, fd);
  if (data && data.success) {
    showToast(id ? 'Spesa aggiornata ✓' : 'Spesa registrata ✓');
    resetFormCosto();
    loadCosti();
  } else {
    showToast((data && data.message) ? data.message : 'Errore nel salvataggio', 'error');
  }
}

function editCosto(c) {
  document.getElementById('costo_id').value        = c.id;
  document.getElementById('costo_data').value       = c.data_spesa;
  document.getElementById('costo_categoria').value  = c.categoria;
  document.getElementById('costo_descrizione').value= c.descrizione || '';
  document.getElementById('costo_importo').value    = c.importo;
  document.getElementById('costo_campo').value      = c.campo || '';
  document.getElementById('costi-form-title').textContent = 'Modifica Spesa';
  document.getElementById('costo-cancel-btn').style.display = 'inline-flex';
  document.getElementById('form-costo').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function resetFormCosto() {
  document.getElementById('form-costo').reset();
  document.getElementById('costo_id').value = '';
  document.getElementById('costo_data').value = new Date().toISOString().split('T')[0];
  document.getElementById('costi-form-title').textContent = 'Nuova Spesa';
  document.getElementById('costo-cancel-btn').style.display = 'none';
}

function deleteCosto(id) {
  openModal('Eliminare questa spesa?', async function() {
    const fd = new FormData();
    fd.append('tabella', 'costi');
    fd.append('id', id);
    const data = await apiPost('api/delete_record.php', fd);
    if (data && data.success) { showToast('Spesa eliminata'); loadCosti(); }
    else showToast('Errore eliminazione', 'error');
  });
}

// ══════════════════════════════════════════════
// PRODUZIONE
// ══════════════════════════════════════════════
async function loadProduzione() {
  const tbody = document.getElementById('prod-tbody');
  tbody.innerHTML = '<tr><td colspan="7"><div class="loading"><div class="spinner"></div></div></td></tr>';
  const res  = await apiFetch('api/get_produzione_list.php');
  const prods = (res && res.produzione) ? res.produzione : [];

  if (prods.length === 0) {
    tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><div class="empty-icon">🫒</div><p>Nessuna raccolta registrata</p></div></td></tr>';
    return;
  }
  tbody.innerHTML = prods.map(function(p) {
    const qtaDisplay = p.unita_raccolta === 'quarta'
      ? fmt(p.quarte_raccolte, 1) + ' quarte <span class="fs-sm text-clay">(' + fmt(p.kg_raccolti, 0) + ' kg)</span>'
      : fmt(p.kg_raccolti, 0) + ' kg <span class="fs-sm text-clay">(' + fmt(p.quarte_raccolte, 1) + ' q.)</span>';
    const resa  = p.litri_x_quarta > 0 ? fmt(p.litri_x_quarta, 2) + ' L/q.' : '—';
    const litri = p.litri_olio > 0     ? '<strong>' + fmt(p.litri_olio) + ' L</strong>' : '—';
    return '<tr>' +
      '<td>' + fmtDate(p.data_raccolta) + '</td>' +
      '<td><strong>' + p.campo + '</strong></td>' +
      '<td>' + qtaDisplay + '</td>' +
      '<td class="text-right">' + resa + '</td>' +
      '<td class="text-right">' + litri + '</td>' +
      '<td class="fs-sm">' + (p.note || '—') + '</td>' +
      '<td class="text-center">' +
        '<button class="btn btn-ghost btn-sm" onclick="editProd(' + JSON.stringify(p) + ')">✏️</button> ' +
        '<button class="btn btn-sm" style="background:var(--red-harvest);color:white;" onclick="deleteProd(' + p.id + ')">🗑</button>' +
      '</td></tr>';
  }).join('');
}

async function saveProduzione(e) {
  e.preventDefault();
  const id = document.getElementById('prod_id').value;
  const fd = new FormData(e.target);
  const url = id ? 'api/update_produzione.php' : 'api/save_produzione.php';
  if (id) fd.append('id', id);
  const data = await apiPost(url, fd);
  if (data && data.success) {
    const litriMsg = data.litri_olio ? ' — ' + fmt(data.litri_olio) + ' litri stimati' : '';
    showToast(id ? 'Raccolta aggiornata ✓' : ('Raccolta registrata ✓' + litriMsg));
    resetFormProd();
    loadProduzione();
  } else {
    showToast((data && data.message) ? data.message : 'Errore nel salvataggio', 'error');
  }
}

function editProd(p) {
  document.getElementById('prod_id').value           = p.id;
  document.getElementById('prod_data').value          = p.data_raccolta;
  document.getElementById('prod_stagione').value      = p.stagione || state.stagione;
  document.getElementById('prod_campo').value         = p.campo;
  document.getElementById('prod_unita').value         = p.unita_raccolta || 'quarta';
  document.getElementById('prod_quantita').value      = p.quantita_inserita;
  document.getElementById('prod_litri_x_quarta').value= p.litri_x_quarta || '';
  document.getElementById('prod_note').value          = p.note || '';
  onProdUnitaChange();
  updateProdPreview();
  document.getElementById('prod-form-title').textContent = 'Modifica Raccolta';
  document.getElementById('prod-cancel-btn').style.display = 'inline-flex';
  document.getElementById('form-produzione').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function resetFormProd() {
  document.getElementById('form-produzione').reset();
  document.getElementById('prod_id').value = '';
  document.getElementById('prod_data').value     = new Date().toISOString().split('T')[0];
  document.getElementById('prod_stagione').value = state.stagione;
  document.getElementById('prod-form-title').textContent = 'Registra Raccolta';
  document.getElementById('prod-cancel-btn').style.display = 'none';
  document.getElementById('prod_preview').textContent = '';
  onProdUnitaChange();
}

function deleteProd(id) {
  openModal('Eliminare questa raccolta?', async function() {
    const fd = new FormData();
    fd.append('tabella', 'produzione');
    fd.append('id', id);
    const data = await apiPost('api/delete_record.php', fd);
    if (data && data.success) { showToast('Raccolta eliminata'); loadProduzione(); }
    else showToast('Errore eliminazione', 'error');
  });
}

function onProdUnitaChange() {
  const unita = document.getElementById('prod_unita') ? document.getElementById('prod_unita').value : 'quarta';
  const label = document.getElementById('prod_qta_label');
  const input = document.getElementById('prod_quantita');
  if (label) label.textContent = unita === 'quarta' ? 'Quantità (Quarte)' : 'Quantità (Kg)';
  if (input) input.placeholder = unita === 'quarta' ? 'es. 80' : 'es. 1000';
  updateProdPreview();
}

function updateProdPreview() {
  const unita = document.getElementById('prod_unita') ? document.getElementById('prod_unita').value : 'quarta';
  const qta   = parseFloat(document.getElementById('prod_quantita') ? document.getElementById('prod_quantita').value : 0) || 0;
  const lxq   = parseFloat(document.getElementById('prod_litri_x_quarta') ? document.getElementById('prod_litri_x_quarta').value : 0) || 0;
  const el    = document.getElementById('prod_preview');
  if (!el) return;
  if (qta <= 0) { el.textContent = ''; return; }
  const quarte = unita === 'quarta' ? qta : qta / 12.5;
  const kg     = unita === 'quarta' ? qta * 12.5 : qta;
  let msg = fmt(quarte, 1) + ' quarte = ' + fmt(kg, 0) + ' kg';
  if (lxq > 0) msg += ' — ≈ ' + fmt(quarte * lxq) + ' litri stimati';
  el.textContent = msg;
}

// ══════════════════════════════════════════════
// VENDITE
// ══════════════════════════════════════════════
async function loadVendite() {
  const tbody = document.getElementById('vendite-tbody');
  tbody.innerHTML = '<tr><td colspan="7"><div class="loading"><div class="spinner"></div></div></td></tr>';
  const res     = await apiFetch('api/get_vendite_list.php');
  const vendite = (res && res.vendite) ? res.vendite : [];

  if (vendite.length === 0) {
    tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><div class="empty-icon">💰</div><p>Nessuna vendita registrata</p></div></td></tr>';
    document.getElementById('vendite-totale').textContent = '€ 0,00';
    return;
  }
  tbody.innerHTML = vendite.map(function(v) {
    const ul = v.unita_misura === 'quarta' ? 'quarte' : (v.unita_misura === 'litri' ? 'L' : 'kg');
    const extra = (v.tipo === 'Olive' && v.unita_misura === 'quarta')
      ? '<br><span class="fs-sm text-clay">(' + fmt(v.kg_effettivi, 0) + ' kg)</span>' : '';
    return '<tr>' +
      '<td>' + fmtDate(v.data_vendita) + '</td>' +
      '<td><span class="badge badge-' + (v.tipo === 'Olio' ? 'olio' : 'olive') + '">' + v.tipo + '</span></td>' +
      '<td class="text-right">' + fmt(v.quantita) + ' ' + ul + extra + '</td>' +
      '<td class="text-right">€ ' + fmt(v.prezzo_unitario) + '</td>' +
      '<td class="text-right fw-700 text-green">€ ' + fmt(v.ricavo_totale) + '</td>' +
      '<td>' + (v.acquirente || '<span class="text-clay">—</span>') + '</td>' +
      '<td class="text-center">' +
        '<button class="btn btn-ghost btn-sm" onclick="editVendita(' + JSON.stringify(v) + ')">✏️</button> ' +
        '<button class="btn btn-sm" style="background:var(--red-harvest);color:white;" onclick="deleteVendita(' + v.id + ')">🗑</button>' +
      '</td></tr>';
  }).join('');
  const tot = vendite.reduce(function(s, v) { return s + parseFloat(v.ricavo_totale); }, 0);
  document.getElementById('vendite-totale').textContent = '€ ' + fmt(tot);
}

async function saveVendita(e) {
  e.preventDefault();
  const id  = document.getElementById('vendita_id').value;
  const fd  = new FormData(e.target);
  const url = id ? 'api/update_vendita.php' : 'api/save_vendita.php';
  if (id) fd.append('id', id);
  const data = await apiPost(url, fd);
  if (data && data.success) {
    showToast(id ? 'Vendita aggiornata ✓' : 'Vendita registrata! Ricavo: € ' + fmt(data.ricavo));
    resetFormVendita();
    loadVendite();
  } else {
    showToast((data && data.message) ? data.message : 'Errore nel salvataggio', 'error');
  }
}

function editVendita(v) {
  document.getElementById('vendita_id').value        = v.id;
  document.getElementById('vendita_data').value       = v.data_vendita;
  document.getElementById('vendita_tipo').value       = v.tipo;
  onVenditaTipoChange();
  document.getElementById('vendita_unita').value      = v.unita_misura;
  document.getElementById('vendita_quantita').value   = v.quantita;
  document.getElementById('vendita_prezzo').value     = v.prezzo_unitario;
  document.getElementById('vendita_acquirente').value = v.acquirente || '';
  updateRicavoPreview();
  document.getElementById('vendite-form-title').textContent = 'Modifica Vendita';
  document.getElementById('vendita-cancel-btn').style.display = 'inline-flex';
  document.getElementById('form-vendita').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function resetFormVendita() {
  document.getElementById('form-vendita').reset();
  document.getElementById('vendita_id').value = '';
  document.getElementById('vendita_data').value = new Date().toISOString().split('T')[0];
  document.getElementById('vendite-form-title').textContent = 'Nuova Vendita';
  document.getElementById('vendita-cancel-btn').style.display = 'none';
  document.getElementById('vendita_ricavo_preview').textContent = '';
  onVenditaTipoChange();
}

function deleteVendita(id) {
  openModal('Eliminare questa vendita?', async function() {
    const fd = new FormData();
    fd.append('tabella', 'vendite');
    fd.append('id', id);
    const data = await apiPost('api/delete_record.php', fd);
    if (data && data.success) { showToast('Vendita eliminata'); loadVendite(); }
    else showToast('Errore eliminazione', 'error');
  });
}

function onVenditaTipoChange() {
  const tipo  = document.getElementById('vendita_tipo') ? document.getElementById('vendita_tipo').value : 'Olio';
  const sel   = document.getElementById('vendita_unita');
  const pLabel= document.getElementById('vendita_prezzo_label');
  if (!sel) return;
  if (tipo === 'Olio') {
    sel.innerHTML = '<option value="litri">Litri (L)</option>';
    if (pLabel) pLabel.textContent = 'Prezzo (€/litro)';
  } else {
    sel.innerHTML = '<option value="kg">Kg</option><option value="quarta">Quarte (1 quarta = 12,5 kg)</option>';
    if (pLabel) pLabel.textContent = 'Prezzo (€/unità)';
  }
  updateRicavoPreview();
}

function updateRicavoPreview() {
  const q     = parseFloat(document.getElementById('vendita_quantita') ? document.getElementById('vendita_quantita').value : 0) || 0;
  const p     = parseFloat(document.getElementById('vendita_prezzo')   ? document.getElementById('vendita_prezzo').value   : 0) || 0;
  const unita = document.getElementById('vendita_unita') ? document.getElementById('vendita_unita').value : 'litri';
  const el    = document.getElementById('vendita_ricavo_preview');
  if (!el) return;
  if (q > 0 && p > 0) {
    const kgEff = unita === 'quarta' ? q * 12.5 : q;
    const extra = unita === 'quarta' ? ' — ' + fmt(kgEff, 0) + ' kg effettivi' : '';
    el.textContent = '= € ' + fmt(q * p) + ' totale' + extra;
  } else {
    el.textContent = '';
  }
}

// ══════════════════════════════════════════════
// SIMULATORE
// ══════════════════════════════════════════════
function onSimUnitaChange() {
  const unita  = document.getElementById('sim_unita') ? document.getElementById('sim_unita').value : 'quarta';
  const qLabel = document.getElementById('sim_qta_label');
  const pLabel = document.getElementById('sim_prezzo_olive_label');
  if (qLabel) qLabel.textContent = unita === 'quarta' ? '🏋️ Quantità (Quarte)' : '🏋️ Quantità (Kg)';
  if (pLabel) pLabel.textContent = unita === 'quarta' ? '🫒 Prezzo Olive (€/quarta)' : '🫒 Prezzo Olive (€/kg)';
}

async function calcolaConvenienza() {
  const btn = document.getElementById('sim-btn');
  btn.textContent = 'Calcolo...';
  btn.disabled = true;

  const dati = {
    quantita:       document.getElementById('sim_quantita').value,
    unita:          document.getElementById('sim_unita').value,
    litri_x_quarta: document.getElementById('sim_litri_x_quarta').value,
    prezzo_olive:   document.getElementById('sim_p_olive').value,
    prezzo_olio:    document.getElementById('sim_p_olio').value,
    costo_molitura: document.getElementById('sim_costo_mol').value,
  };

  const result = await apiFetch('api/simulatore.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(dati),
  });

  btn.textContent = '▶ Calcola Strategia';
  btn.disabled = false;

  if (!result || result.error) {
    showToast((result && result.error) ? result.error : 'Errore nel calcolo', 'error');
    return;
  }

  const box = document.getElementById('sim-result');
  const wins = result.conviene_olio;
  box.innerHTML =
    '<div class="result-compare">' +
    '<div class="result-option ' + (!wins ? 'winner' : '') + '">' +
      '<div class="opt-label">🫒 Vendi Olive</div>' +
      '<div class="opt-value">€ ' + fmt(result.vendita_olive) + '</div>' +
      (!wins ? '<div style="font-size:0.7rem;color:var(--gold);margin-top:4px;font-weight:600;">★ CONSIGLIATO</div>' : '') +
    '</div>' +
    '<div class="result-vs">VS</div>' +
    '<div class="result-option ' + (wins ? 'winner' : '') + '">' +
      '<div class="opt-label">🫙 Produci Olio</div>' +
      '<div class="opt-value">€ ' + fmt(result.vendita_olio) + '</div>' +
      (wins ? '<div style="font-size:0.7rem;color:var(--gold);margin-top:4px;font-weight:600;">★ CONSIGLIATO</div>' : '') +
    '</div></div>' +
    '<div class="result-verdict ' + (wins ? 'olio' : 'olive') + '">' +
      '<strong>' + (wins ? '🫙 Conviene produrre Olio!' : '🫒 Conviene vendere Olive!') + '</strong>' +
      ' Vantaggio: <strong>€ ' + fmt(result.differenza) + '</strong> (+' + result.percentuale + '%)' +
    '</div>' +
    '<div class="result-detail" style="margin-top:10px;">' +
      '<div class="detail-pill" style="background:rgba(90,107,62,0.12);color:var(--olive-dark);border-color:rgba(90,107,62,0.2);">🫒 ' + fmt(result.quarte) + ' quarte = ' + fmt(result.kg_input, 0) + ' kg</div>' +
      '<div class="detail-pill" style="background:rgba(107,63,42,0.12);color:var(--bark);border-color:rgba(107,63,42,0.2);">🫙 ' + fmt(result.litri_olio) + ' litri stimati</div>' +
      '<div class="detail-pill" style="background:rgba(200,150,12,0.12);color:var(--gold);border-color:rgba(200,150,12,0.2);">⚙️ Molitura: € ' + fmt(result.costo_molitura) + '</div>' +
    '</div>';
}

// ══════════════════════════════════════════════
// INIT
// ══════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', function() {
  const oggi = new Date().toISOString().split('T')[0];
  document.querySelectorAll('input[type="date"]').forEach(function(el) {
    if (!el.value) el.value = oggi;
  });
  document.querySelectorAll('[name="stagione"]').forEach(function(el) {
    if (!el.value) el.value = state.stagione;
  });
  onVenditaTipoChange();
  onProdUnitaChange();
  onSimUnitaChange();
  navigate('dashboard');
});
