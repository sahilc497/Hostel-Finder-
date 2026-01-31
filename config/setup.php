<?php
require_once '../config/database.php';

try {
    $sql = file_get_contents('../database.sql');
    $conn->exec($sql);
    echo "Database initialized successfully.";
} catch(PDOException $e) {
    echo "Error initializing database: " . $e->getMessage();
}
?>
