<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>PostgreSQL Connection Test</h2>";
echo "1. Script started...<br>";

if (!extension_loaded('pdo_pgsql')) {
    echo "<b style='color:red;'>CRITICAL: The 'pdo_pgsql' extension is NOT enabled in your PHP!</b><br>";
    echo "You must enable it in your <code>php.ini</code> file.<br>";
    echo "Look for <code>;extension=pdo_pgsql</code> and remove the semicolon.<br><br>";
}

$host = 'localhost';
$port = '5432';
$db_name = 'hostel_finder';
$user = 'postgres';
$pass = 'postgres';

echo "2. Attempting PDO PostgreSQL connection to <b>$host:$port</b>...<br>";
flush();

try {
    // Try connecting to 'postgres' first to see if server is alive
    $dsn_maintenance = "pgsql:host=$host;port=$port;dbname=postgres";
    $conn_m = new PDO($dsn_maintenance, $user, $pass, [PDO::ATTR_TIMEOUT => 2]);
    echo "<i>- Maintenance connection to 'postgres' database works.</i><br>";
    
    // Now try target database
    $dsn = "pgsql:host=$host;port=$port;dbname=$db_name";
    $conn = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 2
    ]);
    echo "<b style='color:green;'>SUCCESS: Connected to '$db_name'!</b><br>";
    
    // Test a simple query
    $stmt = $conn->query("SELECT current_database(), current_user, version()");
    $info = $stmt->fetch();
    echo "<br><b>Database Info:</b><br>";
    echo "Database: " . $info['current_database'] . "<br>";
    echo "User: " . $info['current_user'] . "<br>";
    echo "Version: " . $info['version'] . "<br>";

} catch (PDOException $e) {
    echo "<b style='color:red;'>FAILED:</b> " . $e->getMessage() . "<br>";
    echo "<br><b>Troubleshooting Tips:</b><br>";
    echo "1. Check if PostgreSQL service is running.<br>";
    echo "2. Ensure <code>php_pdo_pgsql</code> extension is enabled in php.ini.<br>";
    echo "3. Verify database <code>hostel_finder</code> exists.<br>";
    echo "4. Double-check your credentials (postgres/postgres).<br>";
}

echo "<br>End of test.";
