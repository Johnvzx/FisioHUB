USE mydatabase;

ALTER TABLE professionals 
ADD COLUMN IF NOT EXISTS specialty VARCHAR(150) DEFAULT NULL 
AFTER email;
