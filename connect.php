<?php
    // Database connection configuration variables
    $host = "localhost";      // Database server address
    $username = "root";       // Default MySQL username in XAMPP
    $password = "";           // Default MySQL password in XAMPP is empty
    $database = "dashboard";  // Name of the database

    // Create a new connection instance using MySQLi
    $conn = new mysqli($host, $username, $password, $database);

    // Check if the connection encountered an error
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    // Set character set to utf8mb4 for proper character encoding
    $conn->set_charset("utf8mb4");
?>