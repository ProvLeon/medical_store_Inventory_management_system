DROP DATABASE IF EXISTS final_project;
CREATE DATABASE IF NOT EXISTS final_project;

USE final_project;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('med_admin', 'receptionist', 'doctor') NOT NULL
);

CREATE TABLE IF NOT EXISTS medicine (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    cp DECIMAL(10, 2) NOT NULL,
    sp DECIMAL(10, 2) NOT NULL,
    expiry_date DATE NOT NULL,
    chem_amount VARCHAR(50) NOT NULL,
    buy_timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS name_pharma (
    medicine_id INT,
    pharmaco VARCHAR(50) NOT NULL,
    PRIMARY KEY(medicine_id, pharmaco),
    FOREIGN KEY(medicine_id) REFERENCES medicine(id)
);

CREATE TABLE IF NOT EXISTS name_compound (
    medicine_id INT,
    compound VARCHAR(50) NOT NULL,
    PRIMARY KEY(medicine_id, compound),
    FOREIGN KEY(medicine_id) REFERENCES medicine(id)
);

CREATE TABLE IF NOT EXISTS transaction (
    id INT AUTO_INCREMENT PRIMARY KEY,
    txn_timestamp DATETIME NOT NULL,
    buy_sell ENUM('B', 'S') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    notes TEXT
);

CREATE TABLE IF NOT EXISTS transaction_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id INT NOT NULL,
    medicine_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (transaction_id) REFERENCES transaction(id),
    FOREIGN KEY (medicine_id) REFERENCES medicine(id)
);

CREATE TABLE IF NOT EXISTS person (
    pid INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address TEXT
);

CREATE TABLE IF NOT EXISTS person_email (
    pid INT,
    email VARCHAR(45) NOT NULL,
    PRIMARY KEY(pid, email),
    FOREIGN KEY(pid) REFERENCES person(pid)
);

CREATE TABLE IF NOT EXISTS person_tel_no (
    pid INT,
    tel_no VARCHAR(20) NOT NULL,
    PRIMARY KEY(pid, tel_no),
    FOREIGN KEY(pid) REFERENCES person(pid)
);

CREATE TABLE IF NOT EXISTS supplier_pharmaco (
    pid INT,
    pharmaco VARCHAR(50) NOT NULL,
    PRIMARY KEY(pid, pharmaco),
    FOREIGN KEY(pid) REFERENCES person(pid)
);

CREATE TABLE IF NOT EXISTS employee (
    pid INT,
    salary DECIMAL(10, 2) NOT NULL,
    duty_timings VARCHAR(20) NOT NULL,
    FOREIGN KEY(pid) REFERENCES person(pid)
);

CREATE TABLE IF NOT EXISTS txn_person (
    id INT NOT NULL,
    pid_person INT NOT NULL,
    pid_employee INT NOT NULL,
    PRIMARY KEY (id, pid_person, pid_employee),
    FOREIGN KEY (id) REFERENCES transaction(id),
    FOREIGN KEY (pid_person) REFERENCES person(pid),
    FOREIGN KEY (pid_employee) REFERENCES person(pid)
);
