<?php
require_once '../config/database.php';

try {
    // Modify table to add columns if they don't exist
    $sql = "ALTER TABLE pg_listings ADD COLUMN IF NOT EXISTS latitude DECIMAL(10, 8);";
    $conn->exec($sql);
    $sql = "ALTER TABLE pg_listings ADD COLUMN IF NOT EXISTS longitude DECIMAL(11, 8);";
    $conn->exec($sql);
    echo "Database schema updated successfully.";
} catch(PDOException $e) {
    echo "Error checking/updating database: " . $e->getMessage();
}
?>
