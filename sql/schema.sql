-- InsightPulse BI — Sales Analytics Platform
-- Run this once in phpMyAdmin (or via mysql CLI) before using the app.
-- This creates a separate "insightpulse_bi" database, independent from any
-- older "sales_dashboard" database you may already have — so both versions
-- can coexist in the same XAMPP / phpMyAdmin install.

CREATE DATABASE IF NOT EXISTS insightpulse_bi
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE insightpulse_bi;

CREATE TABLE IF NOT EXISTS users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  username      VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS sales (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  user_id        INT           NOT NULL,
  order_id       VARCHAR(50)   NOT NULL,
  order_date     DATE          NOT NULL,
  customer_name  VARCHAR(150)  NOT NULL,
  product        VARCHAR(150)  NOT NULL,
  category       VARCHAR(100)  NOT NULL,
  region         VARCHAR(100)  NOT NULL,
  quantity       INT           NOT NULL,
  unit_price     DECIMAL(10,2) NOT NULL,
  total_amount   DECIMAL(12,2) NOT NULL,
  created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_user_order (user_id, order_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_order_date (order_date),
  INDEX idx_category (category),
  INDEX idx_region (region),
  INDEX idx_product (product),
  INDEX idx_user (user_id)
) ENGINE=InnoDB;
