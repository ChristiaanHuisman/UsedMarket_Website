<?php
session_start();
require 'db.php';

// Ensure user is logged in and role is set
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

// Validate that the request is a proper POST request and user_id is numeric
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['user_id']) || !is_numeric($_POST['user_id'])) {
    die("Invalid request.");
}

$target_user_id = (int) $_POST['user_id'];
$current_user_id = (int) $_SESSION['user_id'];
$current_user_role = $_SESSION['role'];

// Check permissions: allow if user is deleting their own account OR if user is an admin
$is_self_deleting = ($current_user_id === $target_user_id);
$is_admin = (strpos($current_user_role, 'admin') === 0);

if (!$is_self_deleting && !$is_admin) {
    die("Permission denied.");
}

// Step 1: Fetch and delete listing image files associated with the user
$stmtFetch = $conn->prepare("SELECT imagePath FROM listings WHERE userId = ?");
if (!$stmtFetch) {
    die("Error preparing to fetch listings.");
}
$stmtFetch->bind_param("i", $target_user_id);
if (!$stmtFetch->execute()) {
    $stmtFetch->close();
    die("Error fetching listings.");
}
$result = $stmtFetch->get_result();
while ($row = $result->fetch_assoc()) {
    $image_path = $row['imagePath'];
    if ($image_path && file_exists($image_path)) {
        unlink($image_path); // Remove image file from server
    }
}
$stmtFetch->close();

// Step 2: Delete all listings belonging to the user
$stmtListings = $conn->prepare("DELETE FROM listings WHERE userId = ?");
if (!$stmtListings) {
    die("Error preparing to delete listings.");
}
$stmtListings->bind_param("i", $target_user_id);
if (!$stmtListings->execute()) {
    $stmtListings->close();
    die("Error deleting listings.");
}
$stmtListings->close();

// Step 3: Delete the user from the users table
$stmtUser = $conn->prepare("DELETE FROM users WHERE id = ?");
if (!$stmtUser) {
    die("Error preparing to delete user.");
}
$stmtUser->bind_param("i", $target_user_id);
if (!$stmtUser->execute()) {
    $stmtUser->close();
    die("Failed to delete user.");
}
$stmtUser->close();

// Step 4: If the user deleted their own account, end the session and redirect to homepage
if ($is_self_deleting) {
    session_destroy();
    header("Location: index.php");
    exit;
}

// Otherwise, redirect admin back to the admin dashboard
$_SESSION['delete_success'] = true;
header("Location: adminDashboard.php");
exit;