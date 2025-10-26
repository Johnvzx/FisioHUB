-- Create professionals table if missing and migrate existing professional users
CREATE TABLE IF NOT EXISTS professionals (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert all rows from users that have role = 'professional' into professionals
INSERT IGNORE INTO professionals (name, email, password_hash, created_at)
SELECT name, email, password_hash, created_at FROM users WHERE role = 'professional';

-- Remove migrated professionals from users table
DELETE FROM users WHERE role = 'professional';

-- Ensure remaining users have role 'user'
UPDATE users SET role = 'user' WHERE role IS NULL OR role = '';
