<?php
session_start();
require 'db.php';

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Validate that the request is a POST request and that the listing ID is numeric
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die("Invalid request.");
}

// Sanitize the input listing ID
$listing_id = intval($_POST['id']);
$user_id = $_SESSION['user_id'];

// Prepare and execute a query to fetch the image path of the listing
// Also ensures that the listing belongs to the currently logged-in user
$stmt = $conn->prepare("SELECT imagePath FROM listings WHERE id = ? AND userId = ?");
$stmt->bind_param("ii", $listing_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

// If no matching listing is found or listing does not belong to the user, stop execution
if ($result->num_rows !== 1) {
    die("Listing not found or access denied.");
}

// Fetch the image path from the result
$row = $result->fetch_assoc();
$image_path = $row['imagePath'];

// Check if the image file exists on the server, and delete it
if (file_exists($image_path)) {
    unlink($image_path);
}

// Prepare and execute a query to delete the listing from the database
// Again, the listing is only deleted if it belongs to the current user
$stmt = $conn->prepare("DELETE FROM listings WHERE id = ? AND userId = ?");
$stmt->bind_param("ii", $listing_id, $user_id);
$stmt->execute();

// After deletion, redirect the user back to the listings management page
header("Location: customerListings.php");
exit;