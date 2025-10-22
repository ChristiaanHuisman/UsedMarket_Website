<?php

// Database connection parameters to change for hosting
$host   = "localhost"; // Host of mySQL server (localhost)
$user   = "root"; // mySQL username (default for local development)
$pass   = ""; // mySQL password (empty by default for XAMPP)
$dbname = "usedmarket"; // Database name

//$host   = "sql202.infinityfree.com"; // Host of mySQL server
//$user   = "if0_39214164"; // mySQL username
//$pass   = "ITECA3B12"; // mySQL password
//$dbname = "if0_39214164_usedmarket"; // Database name

$conn = new mysqli($host, $user, $pass, $dbname); // Initialize new mySQLi connection object

if ($conn->connect_error) {
    // If connection fails, stop execution and display relavent error message
    die("Connection failed: " . $conn->connect_error);
}

// If there isn't an error, connection is successful and the $conn object is available

?>