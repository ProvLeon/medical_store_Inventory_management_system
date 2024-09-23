CREATE DATABASE IF NOT EXISTS final_project;

USE final_project;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('med_admin', 'receptionist', 'doctor') NOT NULL,
    person_id INT,
    FOREIGN KEY (person_id) REFERENCES person(pid)
);

INSERT INTO users (username, password, role) VALUES ('recept', 'pass1234', 'receptionist');
INSERT INTO users (username, password, role) VALUES ('doctor', 'pass1234', 'doctor');
INSERT INTO users (username, password, role) VALUES ('medadmin', 'pass1234', 'med_admin');

-- Update users to link with person
UPDATE users SET person_id = (SELECT pid FROM person WHERE name = users.username);
