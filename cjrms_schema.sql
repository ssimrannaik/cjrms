-- Criminal Justice Records Management System (CJRMS) Database Schema
-- Compatible with MySQL 8.x and XAMPP
-- Engine: InnoDB for ACID compliance and foreign key support

CREATE DATABASE IF NOT EXISTS cjrms;
USE cjrms;

-- Drop tables in reverse dependency order to avoid foreign key conflicts
DROP TABLE IF EXISTS Case_Lawyer_Link;
DROP TABLE IF EXISTS Case_Criminal_Link;
DROP TABLE IF EXISTS Complaint_Criminal_Link;
DROP TABLE IF EXISTS Judgement;
DROP TABLE IF EXISTS Cases;
DROP TABLE IF EXISTS Complaint;
DROP TABLE IF EXISTS Victim;
DROP TABLE IF EXISTS Criminal;
DROP TABLE IF EXISTS Lawyer;
DROP TABLE IF EXISTS Court;
DROP TABLE IF EXISTS Users;

-- Users table for authentication and role management
CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('Police', 'Court', 'Admin', 'Lawyer') NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Victim table
CREATE TABLE Victim (
    victim_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    contact_info TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Criminal table
CREATE TABLE Criminal (
    criminal_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    age INT,
    identification_detail TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Court table
CREATE TABLE Court (
    court_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(200),
    type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Lawyer table
CREATE TABLE Lawyer (
    lawyer_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('Prosecution', 'Defense', 'Private') NOT NULL,
    contact_info TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Complaint table
CREATE TABLE Complaint (
    complaint_id INT AUTO_INCREMENT PRIMARY KEY,
    description TEXT NOT NULL,
    date DATE NOT NULL,
    crime_type VARCHAR(100) NOT NULL,
    victim_id INT NOT NULL,
    complaint_status ENUM('New', 'Under Investigation', 'Withdrawn', 'Charge-Sheeted', 'Promoted to Case') DEFAULT 'New',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (victim_id) REFERENCES Victim(victim_id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES Users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Cases table (renamed from Case to avoid MySQL reserved word)
CREATE TABLE Cases (
    case_id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT UNIQUE NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    case_type VARCHAR(100) NOT NULL,
    case_status ENUM('Open', 'Under Trial', 'Closed') NOT NULL DEFAULT 'Open',
    court_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES Complaint(complaint_id) ON DELETE CASCADE,
    FOREIGN KEY (court_id) REFERENCES Court(court_id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Judgement table
CREATE TABLE Judgement (
    judgement_id INT AUTO_INCREMENT PRIMARY KEY,
    case_id INT NOT NULL,
    date DATE NOT NULL,
    verdict VARCHAR(100) NOT NULL,
    punishment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (case_id) REFERENCES Cases(case_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Complaint_Criminal_Link junction table
CREATE TABLE Complaint_Criminal_Link (
    complaint_id INT NOT NULL,
    criminal_id INT NOT NULL,
    linked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (complaint_id, criminal_id),
    FOREIGN KEY (complaint_id) REFERENCES Complaint(complaint_id) ON DELETE CASCADE,
    FOREIGN KEY (criminal_id) REFERENCES Criminal(criminal_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Case_Criminal_Link junction table
CREATE TABLE Case_Criminal_Link (
    case_id INT NOT NULL,
    criminal_id INT NOT NULL,
    linked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (case_id, criminal_id),
    FOREIGN KEY (case_id) REFERENCES Cases(case_id) ON DELETE CASCADE,
    FOREIGN KEY (criminal_id) REFERENCES Criminal(criminal_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Case_Lawyer_Link junction table
CREATE TABLE Case_Lawyer_Link (
    case_id INT NOT NULL,
    lawyer_id INT NOT NULL,
    role VARCHAR(100) NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (case_id, lawyer_id),
    FOREIGN KEY (case_id) REFERENCES Cases(case_id) ON DELETE CASCADE,
    FOREIGN KEY (lawyer_id) REFERENCES Lawyer(lawyer_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Create indexes for better performance
CREATE INDEX idx_complaint_status ON Complaint(complaint_status);
CREATE INDEX idx_complaint_date ON Complaint(date);
CREATE INDEX idx_case_status ON Cases(case_status);
CREATE INDEX idx_case_dates ON Cases(start_date, end_date);
CREATE INDEX idx_criminal_name ON Criminal(name);
CREATE INDEX idx_victim_name ON Victim(name);

-- Show tables created
SHOW TABLES;
