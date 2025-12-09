-- Central SQL script for schema and data updates
-- MySQL 8 compatible

-- Example: create table only if it does not exist
CREATE TABLE IF NOT EXISTS example_table (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(191) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Example: add column only if it does not exist
-- NOTE: MySQL 8 has IF EXISTS/IF NOT EXISTS only for some operations.
-- To be fully safe, you can check INFORMATION_SCHEMA first.
-- Replace `your_db_name` with your actual DB (here: nscrm)

-- Check column
-- SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
--  WHERE TABLE_SCHEMA = 'nscrm'
--    AND TABLE_NAME = 'users'
--    AND COLUMN_NAME = 'some_new_column';

-- If no row is returned, then run:
-- ALTER TABLE users ADD COLUMN some_new_column VARCHAR(191) NULL AFTER email;

-- Example: drop index if exists (MySQL 8 syntax)
-- ALTER TABLE users DROP INDEX IF EXISTS idx_users_email;

-- Example: create unique index if not exists
-- MySQL does not have CREATE INDEX IF NOT EXISTS.
-- You can check INFORMATION_SCHEMA.STATISTICS first:
-- SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
--  WHERE TABLE_SCHEMA = 'nscrm'
--    AND TABLE_NAME = 'users'
--    AND INDEX_NAME = 'uniq_users_email';
-- If no row, then run:
-- CREATE UNIQUE INDEX uniq_users_email ON users(email);

-- Add your own queries below this line

