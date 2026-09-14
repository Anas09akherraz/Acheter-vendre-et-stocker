CREATE DATABASE IF NOT EXISTS gestion_stock_db;
USE gestion_stock_db;

-- 1. جدول العملاء (Clients)
CREATE TABLE clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    telephone VARCHAR(20) NOT NULL
);

-- 2. جدول الموردين (Fournisseurs)
CREATE TABLE fournisseurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    telephone VARCHAR(20) NOT NULL
);

-- 3. جدول المخزون (Stock / Articles)
CREATE TABLE articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    designation VARCHAR(150) NOT NULL,
    unite VARCHAR(20) DEFAULT 'PCS',
    prix_unitaire DECIMAL(10,2) DEFAULT 0.00,
    quantite_stock INT DEFAULT 0
);

-- 4. جدول الوثائق (Facture, BL, Devis, BC)
CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_doc ENUM('devis', 'bl_vente', 'facture_vente', 'bc_achat', 'bl_achat', 'facture_achat') NOT NULL,
    numero_doc VARCHAR(50) NOT NULL,
    date_doc DATE NOT NULL,
    client_id INT NULL,
    fournisseur_id INT NULL,
    num_bc VARCHAR(50) NULL,
    num_bl VARCHAR(50) NULL,
    matricule VARCHAR(50) NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
    FOREIGN KEY (fournisseur_id) REFERENCES fournisseurs(id) ON DELETE SET NULL
);

-- 5. جدول تفاصيل الوثيقة (Lignes de Document)
CREATE TABLE document_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_id INT NOT NULL,
    article_id INT NOT NULL,
    quantite INT NOT NULL,
    prix_unitaire DECIMAL(10,2) NOT NULL,
    unite VARCHAR(20) DEFAULT 'PCS',
    total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    FOREIGN KEY (article_id) REFERENCES articles(id)
);
