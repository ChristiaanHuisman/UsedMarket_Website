<?php
require 'db.php';

// Fetch 8 random listings
$stmt = $conn->query("
    SELECT listings.id, listings.title, listings.price, listings.imagePath, listings.listingCondition, users.province
    FROM listings
    JOIN users ON listings.userId = users.id
    ORDER BY RAND()
    LIMIT 8
");
$featured = $stmt->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UM - Home</title>

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Favicon -->
    <link rel="icon" href='data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32">
        <rect width="32" height="32" rx="8" ry="8" fill="%230d6efd"/></svg>'>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="style.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

    <!-- Fixed navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top py-lg-3">
        <div class="container-fluid">
            <!-- Website Name on the Left -->
            <a class="navbar-brand" href="index.php"><i class="bi bi-house me-1"></i> UsedMarket</a>

<!-- Navbar Toggler for mobile -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" 
      aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

            <!-- Right-side Login Link -->
            <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
                <ul class="navbar-nav">
                    <!-- Support link -->
                    <li class="nav-item">
                        <a class="nav-link text-white" href="support.php">
                            <i class="bi bi-question-circle me-1"></i> Support
                        </a>
                    </li>
                    <li class="nav-item">
                        <!-- Link to login page -->
                        <a class="nav-link text-white" href="login.php"><i class="bi bi-person-circle me-1"></i> Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main page content -->
    <main class="flex-grow-1 container mt-5 pt-0 pb-5">
        <h1>Welcome to <strong>UsedMarket</strong></h1>
        <p>Your community marketplace to buy and sell anything easily.</p>
        <h2 class="mt-5 mb-4">Featured listings</h2>
<div class="row">
    <?php foreach ($featured as $listing): ?>
        <div class="col-md-4 mb-4 d-flex">
    <a href="login.php?id=<?= $listing['id'] ?>" class="text-decoration-none text-dark w-100">
        <div class="card shadow-sm featured-card d-flex flex-column h-100">
            <img src="<?= htmlspecialchars($listing['imagePath'] ?? 'placeholder.jpg') ?>" class="card-img-top" alt="Listing image">
            <div class="card-body flex-grow-1">
                <h4 class="card-title"><?= htmlspecialchars($listing['title']) ?></h4>
                <p class="card-text mb-1"><strong>R<?= htmlspecialchars($listing['price']) ?></strong></p>
                <p class="card-text mb-1 text-muted"><?= htmlspecialchars($listing['province']) ?></p>
                <p class="card-text mb-1 text-muted"><strong>Condition: </strong><?= htmlspecialchars($listing['listingCondition']) ?></p>
            </div>
        </div>
    </a>
</div>

    <?php endforeach; ?>
</div>
<div class="text-center mt-4">
    <p>Please <a href="login.php">login</a> to continue browsing <strong>UsedMarket.</strong></p>
</div>
    </main>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Bootstrap 5 JS (required for navbar toggle) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>