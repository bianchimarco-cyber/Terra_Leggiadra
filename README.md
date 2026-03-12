# 🫒 AgroManager v1.0 — Gestionale Olivicolo

Webapp completa per la gestione economica e produttiva delle aziende olivicole.  
Stack: **PHP 8.2 + MySQL 8 + Apache** su Docker.

---

## 🚀 Avvio Rapido

### Prerequisiti
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) installato

### 1. Avvia l'applicazione
```bash
# Dalla cartella AgroManager/
docker-compose up -d --build
```

### 2. Aspetta ~15 secondi per l'inizializzazione del database

### 3. Apri il browser
```
http://localhost:8080
```

---

## 🛑 Comandi Utili

```bash
# Ferma i container
docker-compose down

# Ferma e cancella il database (reset completo)
docker-compose down -v

# Vedi i log in tempo reale
docker-compose logs -f

# Riavvia dopo modifiche al codice PHP
# (Non necessario — i file sono montati come volume)
# Basta ricaricare la pagina!
```

---

## 📁 Struttura del Progetto

```
AgroManager/
├── docker-compose.yml      # Orchestrazione container
├── Dockerfile              # Build immagine PHP+Apache
├── init.sql                # Schema DB + dati demo
└── src/
    ├── index.php           # Interfaccia principale (SPA)
    ├── db_connect.php      # Connessione MySQL condivisa
    ├── api/
    │   ├── simulatore.php          # Calcoli strategia olive/olio
    │   ├── save_costo.php          # Salvataggio spese
    │   ├── save_produzione.php     # Salvataggio raccolte
    │   ├── save_vendita.php        # Salvataggio vendite
    │   ├── get_dashboard_data.php  # KPI + dati aggregati
    │   ├── get_costi_list.php      # Lista spese
    │   ├── get_produzione_list.php # Lista produzioni
    │   └── get_vendite_list.php    # Lista vendite
    ├── css/
    │   └── style.css       # Design system Terra & Oro
    └── js/
        └── script.js       # Frontend SPA logic
```

---

## ✨ Funzionalità

| Sezione | Descrizione |
|---------|-------------|
| 📊 Dashboard | KPI stagionali, andamento storico, ultime spese, punto di pareggio |
| ✨ Simulatore | "Olive o Olio?" — calcolo automatico convenienza |
| 💰 Costi | Registro spese per categoria (Input Tecnici, Logistica, Terzi) |
| 🚜 Produzione | Raccolta per campo, resa %, litri olio stimati |
| 🏺 Vendite | Registro ricavi olive e olio con calcolo automatico |

---

## 🗄️ Database

**Host (interno):** `db:3306`  
**Database:** `agromanager`  
**User:** `root` / **Password:** `root`

Per accedere direttamente al DB:
```bash
docker exec -it agromanager-db-1 mysql -u root -proot agromanager
```

---

## 🔧 Sviluppo

I file nella cartella `src/` sono montati come **volume Docker**: ogni modifica è immediatamente visibile senza rebuild.

Per modificare il DB schema, edita `init.sql` poi esegui:
```bash
docker-compose down -v && docker-compose up -d --build
```

---

*AgroManager v1.0 — Fatto con 🫒 per l'olivicoltura italiana*
