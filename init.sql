-- AgroManager Database Schema

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
    kg_raccolti DECIMAL(10,2) NOT NULL,
    resa_percentuale DECIMAL(5,2) DEFAULT NULL,
    litri_olio DECIMAL(10,2) DEFAULT NULL,
    kg_magazzino DECIMAL(10,2) DEFAULT 0,
    note TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS vendite (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data_vendita DATE NOT NULL,
    tipo ENUM('Olive', 'Olio') NOT NULL,
    quantita DECIMAL(10,2) NOT NULL,
    prezzo_unitario DECIMAL(10,2) NOT NULL,
    ricavo_totale DECIMAL(10,2) NOT NULL,
    acquirente VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS settings (
    chiave VARCHAR(50) PRIMARY KEY,
    valore VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Default settings
INSERT INTO settings (chiave, valore) VALUES
    ('prezzo_olive_default', '0.90'),
    ('prezzo_olio_default', '12.00'),
    ('costo_molitura_default', '16.00'),
    ('resa_default', '15.00'),
    ('stagione_corrente', YEAR(CURDATE()))
ON DUPLICATE KEY UPDATE valore = VALUES(valore);

-- Sample data for demo
INSERT INTO costi (data_spesa, categoria, descrizione, importo, campo) VALUES
    (DATE_SUB(CURDATE(), INTERVAL 60 DAY), 'Input Tecnici', 'Concime NPK - Appezzamento Nord', 245.00, 'Nord'),
    (DATE_SUB(CURDATE(), INTERVAL 45 DAY), 'Logistica', 'Gasolio Trattore - Lavorazioni Autunnali', 189.50, NULL),
    (DATE_SUB(CURDATE(), INTERVAL 30 DAY), 'Terzi', 'Costo Molitura al Frantoio Cooperativo', 320.00, NULL),
    (DATE_SUB(CURDATE(), INTERVAL 20 DAY), 'Input Tecnici', 'Fitofarmaco Anti-mosca', 78.90, 'Sud'),
    (DATE_SUB(CURDATE(), INTERVAL 10 DAY), 'Logistica', 'Manutenzione Abbacchiatori', 150.00, NULL);

INSERT INTO produzione (data_raccolta, campo, stagione, kg_raccolti, resa_percentuale, litri_olio, kg_magazzino) VALUES
    (DATE_SUB(CURDATE(), INTERVAL 25 DAY), 'Nord', YEAR(CURDATE()), 2500.00, 16.5, 412.50, 412.50),
    (DATE_SUB(CURDATE(), INTERVAL 20 DAY), 'Sud', YEAR(CURDATE()), 1800.00, 14.8, 266.40, 266.40),
    (DATE_SUB(CURDATE(), INTERVAL 15 DAY), 'Est', YEAR(CURDATE()), 3200.00, 17.2, 550.40, 550.40);

INSERT INTO vendite (data_vendita, tipo, quantita, prezzo_unitario, ricavo_totale, acquirente) VALUES
    (DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Olio', 150.00, 12.50, 1875.00, 'Privati Locali'),
    (DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'Olio', 80.00, 13.00, 1040.00, 'Ristorante Da Mario');
