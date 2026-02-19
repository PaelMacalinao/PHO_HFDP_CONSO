-- PHO CONSO HFDP Database Schema
-- Create database
CREATE DATABASE IF NOT EXISTS pho_conso_hfdp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE pho_conso_hfdp;

-- Main data table
CREATE TABLE IF NOT EXISTS hfdp_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year INT NOT NULL,
    cluster VARCHAR(100) NOT NULL,
    concerned_office_facility VARCHAR(255) NOT NULL,
    facility_level ENUM('BHS', 'PCF', 'HOSP') NOT NULL COMMENT 'BHS=Barangay Health Station, PCF=Primary Care Facility, HOSP=Hospital',
    category ENUM('INFRASTRUCTURE', 'EQUIPMENT', 'HUMAN RESOURCE', 'TRANSPORTATION') NOT NULL,
    type_of_health_facility VARCHAR(255),
    number_of_units INT DEFAULT 0,
    facilities TEXT COMMENT 'Specific Item Description',
    target VARCHAR(255),
    costing DECIMAL(15, 2) DEFAULT 0.00,
    fund_source VARCHAR(255) NOT NULL,
    presence_in_existing_plans VARCHAR(100),
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_year (year),
    INDEX idx_cluster (cluster),
    INDEX idx_facility_level (facility_level),
    INDEX idx_category (category),
    INDEX idx_fund_source (fund_source),
    INDEX idx_presence_plans (presence_in_existing_plans)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin users for login
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
