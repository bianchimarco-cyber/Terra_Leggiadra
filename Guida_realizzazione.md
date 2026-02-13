Ecco il file `GUIDA_REALIZZAZIONE.md` completo. È strutturato come un manuale operativo passo-passo.

Puoi salvare questo contenuto in un file chiamato **GUIDA.md** nella cartella principale del tuo progetto per averlo sempre sott'occhio.

---

```markdown
# 📘 Guida alla Realizzazione: AgroManager v1.0

Questa guida ti accompagna passo dopo passo nella creazione della tua webapp per la gestione olivicola.
**Obiettivo:** Avere un sistema funzionante su Docker con PHP, MySQL e interfaccia Smart.

---

## 🏗️ Fase 1: Preparazione dell'Ambiente
Prima di scrivere codice, creiamo le fondamenta.

### 1. Crea la struttura delle cartelle
Apri il terminale o il tuo editor e crea questa gerarchia esatta:

```text
AgroManager/
├── docker-compose.yml
├── Dockerfile
├── init.sql             <-- (Script creazione DB)
└── src/
    ├── index.php        <-- (Dashboard principale)
    ├── db_connect.php   <-- (File di connessione condiviso)
    ├── api/             <-- (Script PHP per salvare dati e calcoli)
    │   └── simulatore.php
    ├── css/
    │   └── style.css
    └── js/
        └── script.js

```

---

## 🐳 Fase 2: Configurazione Docker

Questi file dicono al computer come costruire il server.

### 2. Crea il `Dockerfile`

```dockerfile
FROM php:8.2-apache
RUN docker-php-ext-install mysqli pdo pdo_mysql
# Abilita mod_rewrite per URL puliti (opzionale ma utile)
RUN a2enmod rewrite
COPY src/ /var/www/html/
RUN chown -R www-data:www-data /var/www/html

```

### 3. Crea il `docker-compose.yml`

```yaml
version: '3.8'
services:
  webapp:
    build: .
    ports:
      - "8080:80"
    volumes:
      - ./src:/var/www/html
    depends_on:
      - db
  db:
    image: mysql:8.0
    restart: always
    environment:
      MYSQL_DATABASE: agromanager
      MYSQL_ROOT_PASSWORD: root
    volumes:
      - ./mysql-data:/var/lib/mysql
      - ./init.sql:/docker-entrypoint-initdb.d/init.sql

```

---

## 🗄️ Fase 3: Il Database (MySQL)

Definiamo le tabelle per costi, produzione e frantoio.

### 4. Crea il file `init.sql`

```sql
CREATE TABLE IF NOT EXISTS costi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data_spesa DATE NOT NULL,
    categoria ENUM('Input Tecnici', 'Logistica', 'Terzi') NOT NULL,
    descrizione VARCHAR(255),
    importo DECIMAL(10,2) NOT NULL
);

CREATE TABLE IF NOT EXISTS produzione (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data_raccolta DATE NOT NULL,
    campo VARCHAR(100),
    kg_raccolti DECIMAL(10,2) NOT NULL
);

-- Tabella per configurazioni (es. prezzi medi di mercato per il simulatore)
CREATE TABLE IF NOT EXISTS settings (
    chiave VARCHAR(50) PRIMARY KEY,
    valore DECIMAL(10,2)
);

```

---

## 💻 Fase 4: Backend (PHP)

Colleghiamo tutto e creiamo la logica del "Simulatore".

### 5. Crea `src/db_connect.php`

```php
<?php
$host = 'db'; // Nome del servizio in docker-compose
$user = 'root';
$pass = 'root';
$db   = 'agromanager';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Errore connessione DB: " . $conn->connect_error);
}
?>

```

### 6. Crea `src/api/simulatore.php`

Questo script riceve i dati dal form via JS e risponde con i calcoli JSON.

```php
<?php
header('Content-Type: application/json');

// Ricevi dati dal frontend (POST)
$data = json_decode(file_get_contents('php://input'), true);

$kg = $data['kg'] ?? 0;
$resa = $data['resa'] ?? 0; // in %
$prezzo_olive = $data['prezzo_olive'] ?? 0; // € al kg
$prezzo_olio = $data['prezzo_olio'] ?? 0;   // € al litro
$costo_molitura = $data['costo_molitura'] ?? 0; // € al quintale (100kg)

// Calcoli
$ricavo_vendita_olive = $kg * $prezzo_olive;

$litri_olio = $kg * ($resa / 100);
$ricavo_lordo_olio = $litri_olio * $prezzo_olio;
$costo_trasformazione = ($kg / 100) * $costo_molitura;
$ricavo_netto_olio = $ricavo_lordo_olio - $costo_trasformazione;

$differenza = $ricavo_netto_olio - $ricavo_vendita_olive;

echo json_encode([
    'vendita_olive' => number_format($ricavo_vendita_olive, 2),
    'vendita_olio' => number_format($ricavo_netto_olio, 2),
    'consiglio' => ($differenza > 0) ? "Fai l'Olio! (Guadagni di più)" : "Vendi le Olive! (Conviene)",
    'delta' => number_format(abs($differenza), 2)
]);
?>

```

---

## 🎨 Fase 5: Frontend (HTML & CSS)

L'interfaccia utente.

### 7. Crea `src/css/style.css`

```css
body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; padding: 20px; }
.dashboard { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; }
.card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.card h2 { color: #2e7d32; border-bottom: 2px solid #e0e0e0; padding-bottom: 10px; }
input { width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;}
button { width: 100%; padding: 10px; background: #2e7d32; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; margin-top: 10px;}
button:hover { background: #1b5e20; }
.result-box { margin-top: 15px; padding: 10px; background: #e8f5e9; border-radius: 5px; font-weight: bold; text-align: center; }

```

### 8. Crea `src/index.php`

```php
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroManager</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>AgroManager 🚜</h1>
    
    <div class="dashboard">
        <div class="card">
            <h2>✨ Simulatore Decisionale</h2>
            <label>Kg Olive Raccolti:</label>
            <input type="number" id="sim_kg" value="1000">
            
            <label>Resa Stimata (%):</label>
            <input type="number" id="sim_resa" value="15">
            
            <label>Prezzo Mercato Olive (€/Kg):</label>
            <input type="number" id="sim_p_olive" value="0.90" step="0.01">
            
            <label>Prezzo Mercato Olio (€/Litro):</label>
            <input type="number" id="sim_p_olio" value="12.00" step="0.50">
            
            <label>Costo Molitura (€/Quintale):</label>
            <input type="number" id="sim_costo_mol" value="16.00">
            
            <button onclick="calcolaConvenienza()">Calcola Strategia</button>
            
            <div id="risultato" class="result-box" style="display:none;"></div>
        </div>

        <div class="card">
            <h2>💰 Registra Spesa</h2>
            <form action="api/save_costo.php" method="POST">
                <input type="date" name="data" required>
                <input type="text" name="descrizione" placeholder="Es. Gasolio Trattore">
                <input type="number" name="importo" placeholder="Importo €" step="0.01" required>
                <button type="submit">Salva Spesa</button>
            </form>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html>

```

---

## ⚡ Fase 6: Interattività (JavaScript)

Rendiamo il simulatore "vivo" senza ricaricare la pagina.

### 9. Crea `src/js/script.js`

```javascript
async function calcolaConvenienza() {
    // 1. Prendi i valori dagli input
    const dati = {
        kg: document.getElementById('sim_kg').value,
        resa: document.getElementById('sim_resa').value,
        prezzo_olive: document.getElementById('sim_p_olive').value,
        prezzo_olio: document.getElementById('sim_p_olio').value,
        costo_molitura: document.getElementById('sim_costo_mol').value
    };

    // 2. Invia i dati al backend PHP
    try {
        const response = await fetch('api/simulatore.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dati)
        });

        const risultato = await response.json();

        // 3. Mostra il risultato
        const box = document.getElementById('risultato');
        box.style.display = 'block';
        box.innerHTML = `
            <p>Vendendo Olive: <strong>€ ${risultato.vendita_olive}</strong></p>
            <p>Facendo Olio: <strong>€ ${risultato.vendita_olio}</strong></p>
            <hr>
            <p style="color: #d32f2f;">${risultato.consiglio}</p>
            <small>Differenza: € ${risultato.delta}</small>
        `;
    } catch (error) {
        console.error('Errore:', error);
        alert('Errore nel calcolo');
    }
}

```

---

## 🚀 Fase 7: Avvio (Lancio)

1. Apri il terminale nella cartella `AgroManager`.
2. Lancia il comando:
```bash
docker-compose up -d --build

```


3. Aspetta qualche secondo che MySQL si inizializzi.
4. Apri il browser su: [http://localhost:8080](https://www.google.com/search?q=http://localhost:8080)

**Fatto!** Ora hai un ambiente di sviluppo completo. Ogni volta che modifichi un file nella cartella `src`, aggiorna semplicemente la pagina del browser per vedere le modifiche.

```

```
