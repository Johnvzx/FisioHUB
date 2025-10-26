CREATE TABLE IF NOT EXISTS professionals (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT IGNORE INTO professionals (name, email, password_hash, created_at)
SELECT name, email, password_hash, created_at FROM users WHERE role = 'professional';

DELETE FROM users WHERE role = 'professional';

UPDATE users SET role = 'user' WHERE role IS NULL OR role = '';
