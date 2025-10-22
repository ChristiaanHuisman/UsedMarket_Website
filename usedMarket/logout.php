<?php
session_start(); // Start or resume the session to gain access to session data

$_SESSION = []; // Clear all session variables by assigning an empty array

session_destroy(); // Completely destroy the session on the server

header("Location: index.php"); // Redirect the user to the homepage after logout
exit; // Ensure no further script execution