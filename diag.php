<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>GLOBEXA Diagnostic Tool</h1>";

// 1. Check PHP version
echo "<h3>1. PHP Environment</h3>";
echo "PHP Version: " . phpversion() . "<br>";

// 2. Check Database Connection
echo "<h3>2. Database Connection</h3>";
$host = 'localhost';
$dbname = 'globexa';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    echo "✅ Success: Connected to MySQL server.<br>";
    
    // Check if database exists
    $stmt = $pdo->query("SHOW DATABASES LIKE '$dbname'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Success: Database '$dbname' exists.<br>";
        $pdo->exec("USE $dbname");
        
        // Check tables
        echo "<h3>3. Tables Check</h3>";
        $tables = ['users', 'destinations', 'hotels', 'taxis', 'bookings', 'payments'];
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
                echo "✅ Table '$table' exists (Rows: $count)<br>";
            } else {
                echo "❌ Table '$table' MISSING!<br>";
            }
        }
        
    } else {
        echo "❌ Error: Database '$dbname' NOT FOUND. Did you import the SQL file?<br>";
    }
} catch (PDOException $e) {
    echo "❌ Error: Connection failed. " . $e->getMessage() . "<br>";
    echo "Check if XAMPP MySQL is started and if the username/password in includes/db.php is correct.<br>";
}

echo "<h3>4. File Path Check</h3>";
echo "Current Directory: " . __DIR__ . "<br>";
echo "Index File: " . (file_exists('index.php') ? "✅ Found" : "❌ NOT FOUND") . "<br>";
?>
