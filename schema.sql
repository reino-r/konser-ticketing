CREATE DATABASE IF NOT EXISTS konser_ticketing
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE konser_ticketing;

CREATE TABLE IF NOT EXISTS concerts (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  artist    VARCHAR(255)  NOT NULL,
  venue     VARCHAR(255)  NOT NULL,
  date      DATE          NOT NULL,
  time      TIME          NOT NULL,
  price     DECIMAL(12,0) NOT NULL,
  quota     INT           NOT NULL,
  poster    VARCHAR(255)  DEFAULT NULL,
  created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
