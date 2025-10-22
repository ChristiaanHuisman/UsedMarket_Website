<?php
session_start();
require 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$province_filter = isset($_GET['province']) ? $_GET['province'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
// Initialize base query and params
$sql = "SELECT listings.*, users.province 
        FROM listings 
        JOIN users ON listings.userId = users.id";

$conditions = [];
$params = [];
$types = '';

// Add filters dynamically
if ($province_filter) {
    $conditions[] = "users.province = ?";
    $params[] = $province_filter;
    $types .= 's';
}
if ($category_filter) {
    $conditions[] = "listings.category = ?";
    $params[] = $category_filter;
    $types .= 's';
}

if ($conditions) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY RAND()";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$listings = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UM - Customer dashboard</title>
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
        <a class="navbar-brand" href="customerDashboard.php"><i class="bi bi-house me-1"></i> UsedMarket</a>

<!-- Navbar Toggler for mobile -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" 
      aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
        
        <!-- Center: Absolute centered text -->
        <div class="position-absolute start-50 translate-middle-x text-white d-none d-lg-block">
            Currently logged in as <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Guest') ?></strong>
        </div>
        
        <div class="collapse navbar-collapse justify-content-end "id="navbarSupportedContent">
            <ul class="navbar-nav">
                <!-- Other links -->
                <li class="nav-item">
                    <a class="nav-link text-white" href="support.php">
                    <i class="bi bi-question-circle me-1"></i> Support
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="editAccount.php"><i class="bi bi-person-circle me-1"></i> Edit account</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="logout.php"><i class="bi bi-box-arrow-right me-1"></i> Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Page content -->
<main class="flex-grow-1 container mt-5 pt-0 pb-5">
    <form method="GET" class="mb-4">
      <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between flex-wrap gap-3">

        <!-- Left group heading and filters -->
        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center gap-3">

          <h2>Browse listings</h2>

            <div class="col-auto p-0">
            <select name="province" class="form-select border-primary text-primary" onchange="this.form.submit()">
              <option value="">All provinces</option>
              <?php $provinces = ["Gauteng", "Western Cape", "KwaZulu-Natal", "Eastern Cape", "Free State", "Limpopo",
              "Mpumalanga", "North West", "Northern Cape"];
              foreach ($provinces as $prov): ?>
                <option value="<?= htmlspecialchars($prov) ?>" <?= ($province_filter === $prov) ?
                'selected' : '' ?>><?= htmlspecialchars($prov) ?></option>
              <?php endforeach; ?>
            </select>
            </div>

            <div class="col-auto p-0">
            <select name="category" class="form-select border-primary text-primary" onchange="this.form.submit()">
              <option value="">All categories</option>
              <?php $categories = ["Home & Living", "Electronics", "Vehicles", "Clothing", "Tools & DIY", "Books & Media",
              "Leisure & Hobbies", "Baby & Kids", "Other"];
              foreach ($categories as $cate): ?>
                <option value="<?= htmlspecialchars($cate) ?>" <?= ($category_filter === $cate) ?
                'selected' : '' ?>><?= htmlspecialchars($cate) ?></option>
              <?php endforeach; ?>
            </select>
            </div>
        </div>

        <!-- Right side manage button -->
        <div class="col-auto p-0 mt-2 mt-md-0">
          <a href="customerListings.php" class="btn btn-warning"><i class="bi bi-pencil-square me-1"></i> Manage my listings</a>
        </div>

      </div>
    </form>

    <!-- Listing Grid -->
    <div class="row">
        <?php while ($row = $listings->fetch_assoc()): ?>
            <div class="col-md-4 mb-4">
    <a href="viewListing.php?id=<?= $row['id'] ?>" class="text-decoration-none text-dark">
        <div class="card h-100 shadow-sm featured-card d-flex flex-column h-100">
            <img src="<?= htmlspecialchars($row['imagePath'] ?? 'placeholder.jpg') ?>" class="card-img-top" alt="Listing image">
            <div class="card-body flex-grow-1">
                <h4 class="card-title"><?= htmlspecialchars($row['title']) ?></h4>
                <p class="card-text mb-1"><strong>R<?= htmlspecialchars($row['price']) ?></strong></p>
                <p class="card-text mb-1 text-muted"><?= htmlspecialchars($row['province']) ?></p>
                <p class="card-text mb-1 text-muted"><strong>Condition: </strong><?= htmlspecialchars($row['listingCondition']) ?></p>
            </div>
        </div>
    </a>
</div>
        <?php endwhile; ?>
    </div>
</main>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>