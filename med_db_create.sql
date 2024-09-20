drop database if exists final_project;
CREATE DATABASE IF NOT EXISTS final_project;

USE final_project;

CREATE TABLE IF NOT EXISTS medicine (
	name varchar(60) NOT NULL,
	buy_timestamp timestamp NOT NULL,
	expiry_date date NOT NULL,
	chem_amount varchar(10) NOT NULL,
	qty int NOT NULL,
	cp int NOT NULL,
	sp int NOT NULL,
	PRIMARY KEY(name, buy_timestamp, expiry_date, chem_amount, cp)
);

CREATE TABLE IF NOT EXISTS name_pharma (
	name varchar(60),
	buy_timestamp timestamp,
	expiry_date date,
	chem_amount varchar(10),
	cp int,
	pharmaco varchar(50) NOT NULL,
	PRIMARY KEY(name, pharmaco),
	FOREIGN KEY(name, buy_timestamp, expiry_date, chem_amount, cp) REFERENCES medicine(name, buy_timestamp, expiry_date, chem_amount, cp)
);

CREATE TABLE IF NOT EXISTS name_compound (
	name varchar(60),
	buy_timestamp timestamp,
	expiry_date date,
	chem_amount varchar(10),
	cp int,
	compound varchar(50) NOT NULL,
	PRIMARY KEY(name, compound),
	FOREIGN KEY(name, buy_timestamp, expiry_date, chem_amount, cp) REFERENCES medicine(name, buy_timestamp, expiry_date, chem_amount, cp)
);

CREATE TABLE IF NOT EXISTS transaction (
	id int PRIMARY KEY NOT NULL AUTO_INCREMENT,
	txn_timestamp timestamp NOT NULL,
	buy_sell char(1) NOT NULL,
	notes text
);

CREATE TABLE IF NOT EXISTS person (
	pid int PRIMARY KEY NOT NULL AUTO_INCREMENT,
	name varchar(60) NOT NULL,
	address text NOT NULL
);

CREATE TABLE IF NOT EXISTS person_email (
	pid int,
	email varchar(45) PRIMARY KEY NOT NULL,
	FOREIGN KEY(pid) REFERENCES person(pid)
);

CREATE TABLE IF NOT EXISTS person_tel_no (
	pid int,
	tel_no int PRIMARY KEY NOT NULL,
	FOREIGN KEY(pid) REFERENCES person(pid)
);

CREATE TABLE IF NOT EXISTS supplier_pharmaco (
	pid int,
	pharmaco varchar(50) NOT NULL,
	PRIMARY KEY(pid, pharmaco),
	FOREIGN KEY(pid) REFERENCES person(pid)
);

CREATE TABLE IF NOT EXISTS employee (
	pid int,
	salary int NOT NULL,
	duty_timings varchar(20) NOT NULL,
	FOREIGN KEY(pid) REFERENCES person(pid)
);

CREATE TABLE IF NOT EXISTS txn_on (
	name varchar(60) NOT NULL,
	buy_timestamp timestamp NOT NULL,
	chem_amount varchar(10) NOT NULL,
	expiry_date date NOT NULL,
	cp int NOT NULL,
	id int NOT NULL,
	qty_buy_sell int NOT NULL,
	PRIMARY KEY(name, buy_timestamp, chem_amount, expiry_date, cp,
	id),
	FOREIGN KEY(id) REFERENCES transaction(id)
);

CREATE TABLE IF NOT EXISTS txn_person (
	id int NOT NULL,
	pid_person int NOT NULL,
	pid_employee int NOT NULL,
	PRIMARY KEY(id, pid_person, pid_employee),
	FOREIGN KEY(id) REFERENCES transaction(id)
);
