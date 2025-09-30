-- Migration script to fix missing channel_source column and other issues
-- LogisticaLeslie System - Fixes for error logs
-- Date: 2025-09-29

USE fix360_logisticaleslie;

-- Add channel_source column to orders table if it doesn't exist
SET @query = (
    SELECT IF(
        (SELECT COUNT(*) 
         FROM INFORMATION_SCHEMA.COLUMNS 
         WHERE TABLE_SCHEMA = DATABASE() 
         AND TABLE_NAME = 'orders' 
         AND COLUMN_NAME = 'channel_source') = 0,
        'ALTER TABLE `orders` ADD COLUMN `channel_source` ENUM(''web'', ''whatsapp'', ''phone'', ''email'') DEFAULT ''web'' AFTER `qr_code`;',
        'SELECT ''Column channel_source already exists'' AS message;'
    )
);

PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update existing orders to have default channel_source value
UPDATE `orders` 
SET `channel_source` = 'web' 
WHERE `channel_source` IS NULL;

-- Verify the changes
SELECT 'Migration completed successfully' AS status;
SELECT COLUMN_NAME, COLUMN_TYPE, COLUMN_DEFAULT, IS_NULLABLE
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = 'orders'
AND COLUMN_NAME = 'channel_source';
