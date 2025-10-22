<?php
session_start();
require 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Helper function to clean input
function clean($val) {
    return htmlspecialchars(trim($val));
}

// Capture form POST data
$listing_id = isset($_POST['listing_id']) ? intval($_POST['listing_id']) : 0;
$buyer_id = $_SESSION['user_id'];
$shipping = isset($_POST['shipping']) ? ($_POST['shipping'] == "1" ? 1 : 0) : 1;
$card_number = clean($_POST['card_number'] ?? '');
$expiry = clean($_POST['expiry'] ?? '');
$cvv = clean($_POST['cvv'] ?? '');

$errors = [];

// ====== Validation (server-side only) ======

// Card number: 16 digits
if (!preg_match("/^\d{16}$/", $card_number)) {
    $errors[] = "Card number must be exactly 16 digits.";
}

// Expiry: MM/YY
if (!preg_match("/^(0[1-9]|1[0-2])\/\d{2}$/", $expiry)) {
    $errors[] = "Expiry must be in MM/YY format.";
}

// CVV: 3 digits
if (!preg_match("/^\d{3}$/", $cvv)) {
    $errors[] = "CVV must be 3 digits.";
}

// Get listing price and seller's province
$stmt = $conn->prepare("SELECT price, userId FROM listings WHERE id = ?");
$stmt->bind_param("i", $listing_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $errors[] = "Listing not found.";
} else {
    $listing = $result->fetch_assoc();
    $price = $listing['price'];
    $seller_id = $listing['userId'];
}

$stmt->close();

// Determine shipping cost
$shipping_cost = 0;
if (!empty($listing)) {
    // Get buyer's and seller's province to calculate shipping
    $stmt = $conn->prepare("SELECT province FROM users WHERE id = ?");
    $stmt->bind_param("i", $buyer_id);
    $stmt->execute();
    $buyer_result = $stmt->get_result();
    $buyer = $buyer_result->fetch_assoc();
    $stmt->close();

    $stmt = $conn->prepare("SELECT province FROM users WHERE id = ?");
    $stmt->bind_param("i", $seller_id);
    $stmt->execute();
    $seller_result = $stmt->get_result();
    $seller = $seller_result->fetch_assoc();
    $stmt->close();

    if ($shipping) {
        $shipping_cost = ($buyer['province'] === $seller['province']) ? 50.00 : 100.00;
    }
}

// Stop if errors
if (!empty($errors)) {
    $_SESSION['purchase_errors'] = $errors;
    header("Location: viewListing.php?id=$listing_id");
    exit;
}

// ====== Insert into orders table ======
$stmt = $conn->prepare("INSERT INTO orders (datePurchased, shipping, shippingCost, buyerId, listingId) 
                        VALUES (CURDATE(), ?, ?, ?, ?)");
$stmt->bind_param("iddi", $shipping, $shipping_cost, $buyer_id, $listing_id);

if ($stmt->execute()) {
    $stmt->close();

// Fetch the image path of the listing to delete the file
$stmt = $conn->prepare("SELECT imagePath FROM listings WHERE id = ?");
$stmt->bind_param("i", $listing_id);
$stmt->execute();
$stmt->bind_result($image_path);
$stmt->fetch();
$stmt->close();

// Delete the image file if it exists and is safe
if (!empty($image_path) && file_exists($image_path)) {
    unlink($image_path);
}

    // Delete the listing after purchase
    $stmt = $conn->prepare("DELETE FROM listings WHERE id = ?");
    $stmt->bind_param("i", $listing_id);
    $stmt->execute();
    $stmt->close();

    // Confirmation message
    $_SESSION['purchase_success'] = "Purchase successful. Order details will be emailed to you.";
    header("Location: customerDashboard.php");
    exit;

} else {
    $errors[] = "Failed to process order. Please try again.";
    $_SESSION['purchase_errors'] = $errors;
    header("Location: viewListing.php?id=$listing_id");
    exit;
}
?>