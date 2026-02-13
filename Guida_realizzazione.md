# 📘 Guida alla Realizzazione: AgroManager v1.0

Questa guida ti accompagna passo dopo passo nella creazione della tua webapp per la gestione olivicola.
**Obiettivo:** Avere un sistema funzionante su Docker con PHP, MySQL e interfaccia Smart.


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
