<?php
require_once 'config/database.php';

$email = 'admin@gmail.com';
$new_password = 'admin123';
$hash = password_hash($new_password, PASSWORD_BCRYPT);

try {
    $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE email = ?");
    $stmt->execute([$hash, $email]);
    
    if ($stmt->rowCount() > 0) {
        echo "<h2>Admin Password Reset Successful!</h2>";
        echo "Email: <b>$email</b><br>";
        echo "Password: <b>$new_password</b><br><br>";
        echo "<a href='auth/admin_login.php'>Go to Login</a>";
    } else {
        echo "<h2>No admin found with that email.</h2>";
        echo "Check if you have run the PostgreSQL schema and if 'admin@gmail.com' exists in the 'admins' table.";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
