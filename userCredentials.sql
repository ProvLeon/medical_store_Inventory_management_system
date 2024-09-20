CREATE DATABASE IF NOT EXISTS final_project;

USE final_project;

CREATE TABLE IF NOT EXISTS users (
username varchar(15),
password varchar(32),
role varchar(15)
);

INSERT INTO users VALUES ('recept', 'pass1234', 'receptionist');

INSERT INTO users VALUES ('doctor', 'pass1234', 'doctor');

INSERT INTO users VALUES ('medadmin', 'pass1234', 'med_admin')
