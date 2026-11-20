<?php
/**
 * Database Connection File
 * Used by all PHP pages for MySQL connection
 */

$host = "localhost";
$user = "root";
$pass = "";
$db   = "ecommerce_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to utf8mb4 for proper character support
mysqli_set_charset($conn, "utf8mb4");

?>

