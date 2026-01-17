<?php
// backend/diagnose.php
header('Content-Type: text/plain');

echo "PHP Version: " . phpversion() . "\n";
echo "Loaded Configuration File: " . php_ini_loaded_file() . "\n";
echo "Extension Directory (ini): " . ini_get('extension_dir') . "\n";

$ext_dir = ini_get('extension_dir');
// Handle relative paths (common in XAMPP)
if (strpos($ext_dir, ':') === false) {
    // It's likely relative to PHP executable, usually c:\xampp\php\ext
    $guess = 'c:\xmpp\php\ext'; // based on user workspace path c:\xmpp
    if (is_dir($guess))
        $ext_dir = $guess;
}

echo "derived ext_dir: $ext_dir\n";

if (is_dir($ext_dir)) {
    echo "Extension directory exists.\n";
    $mysqli_dll = $ext_dir . DIRECTORY_SEPARATOR . 'php_mysqli.dll';
    $pdo_mysql_dll = $ext_dir . DIRECTORY_SEPARATOR . 'php_pdo_mysql.dll';

    echo "Checking for php_mysqli.dll: " . (file_exists($mysqli_dll) ? "FOUND" : "MISSING") . "\n";
    echo "Checking for php_pdo_mysql.dll: " . (file_exists($pdo_mysql_dll) ? "FOUND" : "MISSING") . "\n";
} else {
    echo "CRITICAL: Extension directory does not exist or cannot be found.\n";
}

echo "\nLoaded Extensions:\n";
print_r(get_loaded_extensions());

echo "\nTest Connection attempts:\n";
if (extension_loaded('mysqli')) {
    echo "MySQLi extension is LOADED.\n";
} else {
    echo "MySQLi extension is NOT LOADED.\n";
}

if (extension_loaded('pdo_mysql')) {
    echo "PDO MySQL extension is LOADED.\n";
} else {
    echo "PDO MySQL extension is NOT LOADED.\n";
}
?>