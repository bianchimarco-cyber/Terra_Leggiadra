-- AgroManager Database Schema
-- Nessun dato demo: tutte le tabelle partono vuote

CREATE TABLE IF NOT EXISTS costi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data_spesa DATE NOT NULL,
    categoria ENUM('Input Tecnici', 'Logistica', 'Terzi') NOT NULL,
    descrizione VARCHAR(255),
    importo DECIMAL(10,2) NOT NULL,
    campo VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS produzione (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data_raccolta DATE NOT NULL,
    campo VARCHAR(100) NOT NULL,
    stagione YEAR NOT NULL,
    unita_raccolta ENUM('quarta','kg') NOT NULL DEFAULT 'quarta',
    quantita_inserita DECIMAL(10,2) NOT NULL,
    quarte_raccolte DECIMAL(10,2) NOT NULL,
    kg_raccolti DECIMAL(10,2) NOT NULL,
    litri_x_quarta DECIMAL(6,3) DEFAULT NULL,
    litri_olio DECIMAL(10,2) DEFAULT NULL,
    note TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS vendite (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data_vendita DATE NOT NULL,
    tipo ENUM('Olive','Olio') NOT NULL,
    quantita DECIMAL(10,2) NOT NULL,
    unita_misura ENUM('kg','quarta','litri') NOT NULL DEFAULT 'litri',
    kg_effettivi DECIMAL(10,2) NOT NULL,
    prezzo_unitario DECIMAL(10,2) NOT NULL,
    ricavo_totale DECIMAL(10,2) NOT NULL,
    acquirente VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
