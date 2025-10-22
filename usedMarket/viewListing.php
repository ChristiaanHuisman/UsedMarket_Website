<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Validate listing ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid listing ID.");
}

$listing_id = intval($_GET['id']);

// Fetch listing with seller info
$stmt = $conn->prepare("
    SELECT 
        listings.*,
        users.name,
        users.email,
        users.phoneNumber,
        users.province,
        users.city
    FROM listings
    JOIN users ON listings.userId = users.id
    WHERE listings.id = ?
");
$stmt->bind_param("i", $listing_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Listing not found.");
}

$listing = $result->fetch_assoc();
$stmt->close();

// Determine shipping cost based on buyer's and seller's province
$user_province = $_SESSION['province'];
$shipping_cost = ($user_province === $listing['province']) ? 50 : 100;


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UM - Listing view</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

<!-- Bootstrap + Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link rel="icon" href='data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"><rect width="32" height="32" rx="8" ry="8" fill="%230d6efd"/></svg>'>
    <link rel="stylesheet" href="style.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top py-lg-3">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= $dashboardLink ?>">
            <i class="bi bi-house me-1"></i> UsedMarket
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" 
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="position-absolute start-50 translate-middle-x text-white d-none d-lg-block">
            Currently logged in as <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Guest') ?></strong>
        </div>

        <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link text-white" href="customerDashboard.php">
                        <i class="bi bi-arrow-left-circle me-1"></i> Back
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="logout.php">
                        <i class="bi bi-box-arrow-right me-1"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Main Content -->
<main class="flex-grow-1 container mt-5 pt-0 pb-5">
    <?php if (!empty($_SESSION['purchase_errors'])): ?>
  <div class="alert alert-danger mt-4">
    <?php foreach ($_SESSION['purchase_errors'] as $error): ?>
      <div><?= htmlspecialchars($error) ?></div>
    <?php endforeach; unset($_SESSION['purchase_errors']); ?>
  </div>
<?php endif; ?>

<?php if (!empty($_SESSION['purchase_success'])): ?>
  <div class="alert alert-success mt-4">
    <?= $_SESSION['purchase_success']; unset($_SESSION['purchase_success']); ?>
  </div>
<?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <img src="<?= htmlspecialchars($listing['imagePath']) ?>" class="img-fluid rounded border" alt="Listing image">
        </div>
        <div class="col-md-6">
            <h2><?= htmlspecialchars($listing['title']) ?></h2>
            <p class="text-muted mb-2"><strong>R<?= htmlspecialchars($listing['price']) ?></strong></p>
            <p><strong>Category:</strong> <?= htmlspecialchars($listing['category']) ?></p>
            <p><strong>Date listed:</strong> <?= date("j F Y", strtotime($listing['dateListed'])) ?></p>

            <p><strong>Condition:</strong> <?= htmlspecialchars($listing['listingCondition']) ?></p>
            
            <p><strong>Description:</strong><br>
    <?= $listing['description'] ? nl2br(htmlspecialchars($listing['description'])) : 'None' ?>
</p>

<p><strong>Location:</strong> <?= htmlspecialchars($listing['city']) ?>, <?= htmlspecialchars($listing['province']) ?></p>
<!-- Buy button -->
            <button class="btn btn-success mt-2" data-bs-toggle="modal" data-bs-target="#buyModal">
                <i class="bi bi-cart-check me-1"></i> Buy now
            </button>
            <hr class="my-5 hr-thick">

            <h5><i class="bi bi-person-fill me-1"></i> Seller information</h5>
            <p><strong>Name:</strong> <?= htmlspecialchars($listing['name']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($listing['phoneNumber']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($listing['email']) ?></p>
            
            

            <a href="customerDashboard.php" class="btn btn-outline-primary mt-2">
                <i class="bi bi-arrow-left me-1"></i> Back to listings
            </a>
        </div>
    </div>
</main>

<!-- Buy Modal -->
<div class="modal fade" id="buyModal" tabindex="-1" aria-labelledby="buyModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="processPayment.php" class="modal-content">
      <input type="hidden" name="listing_id" value="<?= $listing['id'] ?>">

      <div class="modal-header">
        <h5 class="modal-title" id="buyModalLabel">Complete your purchase</h5>
        <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal" aria-label="Close">
                <i class="bi bi-x-lg"></i>
            </button>
      </div>

      <div class="modal-body">
        <p><strong>Product:</strong> <?= htmlspecialchars($listing['title']) ?></p>
        <p><strong>Price:</strong> R<?= htmlspecialchars($listing['price']) ?></p>

        <div class="mb-3">
          <label class="form-label">Delivery option:</label><br>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="shipping" value="1" checked>
            <label class="form-check-label">Deliver to me (shipping: R<?= $shipping_cost ?>)</label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="radio" name="shipping" value="0">
            <label class="form-check-label">I'll collect it myself (no shipping cost)</label>
          </div>
        </div>

        <h6>Payment information</h6>
        <div class="mb-2">
          <label class="form-label">Card number</label>
          <input type="text" class="form-control" name="card_number" required>
        </div>
        <div class="row">
          <div class="col">
            <label class="form-label">Expiry date (MM/YY)</label>
            <input type="text" class="form-control" name="expiry" required>
          </div>
          <div class="col">
            <label class="form-label">CVV</label>
            <input type="text" class="form-control" name="cvv" required>
          </div>
        </div>
      </div>
<div class="modal-body"><p><strong>Total Amount:</strong> R<span id="totalAmount"></span></p></div>
      <div class="modal-footer justify-content-center">
        <button type="submit" class="btn btn-success mb-0"><i class="bi bi-check-circle me-1"></i> Confirm purchase</button>
      </div>
    </form>
  </div>
</div>

<?php include 'footer.php'; ?>

<!-- Scripts -->
 <script>
  const usedMarketListingPrice = <?= json_encode($listing['price']) ?>;
  const usedMarketShippingCost = <?= ($user_province === $listing['province']) ? 50 : 100 ?>;
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
</body>
</html>