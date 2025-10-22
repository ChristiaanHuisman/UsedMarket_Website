<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || strpos($_SESSION['role'], 'admin') !== 0) {
    header("Location: login.php");
    exit;
}

$admin_name = $_SESSION['name'];
$admin_role = $_SESSION['role'];

if (isset($_SESSION['delete_success'])) {
    $deleteSuccess = $_SESSION['delete_success'];
    unset($_SESSION['delete_success']); // Clear it after using
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UM - Admin dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href='data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"><rect width="32" height="32" rx="8" ry="8" fill="%230d6efd"/></svg>'>
    <link rel="stylesheet" href="style.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">
    <!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top py-lg-3">
    <div class="container-fluid">
        <a class="navbar-brand" href="adminDashboard.php"><i class="bi bi-house me-1"></i> UsedMarket</a>

<!-- Navbar Toggler for mobile -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" 
      aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

        <!-- Center: Absolute centered text -->
        <div class="position-absolute start-50 translate-middle-x text-white d-none d-lg-block">
            Currently logged in as <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Guest') ?></strong>
        </div>
        <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
            <ul class="navbar-nav">
                <!-- Support link -->
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
<main class="flex-grow-1 container mt-5 pt-0 pb-5">
    <h2 class="mb-4">Admin dashboard</h2>

    <!-- Manage Customers -->
<?php if (in_array($admin_role, ['adminManageCustomers', 'adminManageAll', 'adminAll'])): ?>
    <div class="card mb-3">
    <div class="card-body">
        <h5><i class="bi bi-people-fill me-2"></i> Manage customers</h5>
        <p>Manage all users with customer role.</p>
        
        <!-- Manage Customers Form -->
        <form method="POST" action="adminDashboard.php" class="mb-3" novalidate>
            <div class="input-group">
                <input type="text" name="search_email" class="form-control" placeholder="Enter customer email" value="<?= htmlspecialchars($_POST['search_email'] ?? '') ?>">
                <button type="submit" name="search_customer" class="btn btn-primary">Search</button>
            </div>
        </form>

        <?php if (!empty($deleteSuccess)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Customer account successfully deleted.
    </div>
    <a href="adminDashboard.php" class="btn btn-secondary"><i class="bi bi-x-lg me-1"></i> Close</a>
<?php endif; ?>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_customer'])) {
            $email = trim($_POST['search_email']);
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo "<div class='alert alert-danger'>Invalid email format.</div>
                <a href='adminDashboard.php' class='btn btn-secondary'><i class='bi bi-x-lg me-1'></i> Close</a>";
            } else {
                $stmt = $conn->prepare("SELECT id, name, email, phoneNumber, streetAddress, city, province, bankName, accountNumber FROM users WHERE email = ? AND role = 'customer'");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows === 0) {
                    echo "<div class='alert alert-warning'>No customer found with that email.</div>
                    <a href='adminDashboard.php' class='btn btn-secondary'><i class='bi bi-x-lg me-1'></i> Close</a>";
                } else {
                    $customer = $result->fetch_assoc();

                    // Get customer ID
    $customer_id = $customer['id'];

    // Count the number of listings by this customer
    $stmtListings = $conn->prepare("SELECT COUNT(*) AS listing_count FROM listings WHERE userId = ?");
    $stmtListings->bind_param("i", $customer_id);
    $stmtListings->execute();
    $listingResult = $stmtListings->get_result();
    $listingCount = $listingResult->fetch_assoc()['listing_count'] ?? 0;
    $stmtListings->close();
        ?>
                    <!-- Customer Info -->
                    <div class="border rounded p-3 bg-light mt-3">
                        <h6 class="text-primary">Customer details:</h6>
                        <ul class="mb-2">
                            <li><strong>Name:</strong> <?= htmlspecialchars($customer['name']) ?></li>
                            <li><strong>Email:</strong> <?= htmlspecialchars($customer['email']) ?></li>
                            <li><strong>Phone number:</strong> <?= htmlspecialchars($customer['phoneNumber']) ?></li>
                            <li><strong>Province:</strong> <?= htmlspecialchars($customer['province']) ?></li>
                            <li><strong>City:</strong> <?= htmlspecialchars($customer['city']) ?></li>
                            <li><strong>Street address:</strong> <?= htmlspecialchars($customer['streetAddress']) ?></li>
                            <li><strong>Bank name:</strong> <?= htmlspecialchars($customer['bankName']) ?></li>
                            <li><strong>Bank account number:</strong> <?= htmlspecialchars($customer['accountNumber']) ?></li>
                            <li><strong>Total listings:</strong> <?= $listingCount ?></li>
                        </ul>
                        <form method="POST" action="deleteAccount.php" onsubmit="return confirm('Are you sure you want to delete this customer?');">
    <input type="hidden" name="user_id" value="<?= $customer['id'] ?>">
    <button type="submit" class="btn btn-danger me-2"><i class="bi bi-trash me-1"></i> Delete customer</button>
    <a href="adminDashboard.php" class="btn btn-secondary"><i class="bi bi-x-lg me-1"></i> Close</a>
</form>

                    </div>
        <?php
                }
                $stmt->close();
                
            }
        }

        
        ?>
    </div>
</div>

<?php endif; ?>

<!-- Manage Admins -->
<?php if (in_array($admin_role, ['adminManageAll', 'adminAll'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5><i class="bi bi-shield-shaded me-2"></i> Manage admins</h5>
            <p>Manage all users with admin-level roles.</p>
            <a href="admin_manage_admins.php" class="btn btn-warning">Manage admins</a>
        </div>
    </div>
<?php endif; ?>

    <?php if (in_array($admin_role, ['adminReportsBase', 'adminReportsAll', 'adminAll'])): ?>
    <div class="card mb-3">
        <div class="card-body">
            <h5><i class="bi bi-bar-chart-fill me-2"></i> Customer & listing reports</h5>
            <p>Get a breakdown of customers and listings by location and category.</p>
            <form method="POST" action="adminDashboard.php">
                <button type="submit" name="get_base_report" class="btn btn-primary">
                    Generate base report
                </button>
            </form>

            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['get_base_report'])): ?>
                <?php
                // TOTAL CUSTOMERS
                $result = $conn->query("SELECT COUNT(*) AS total_customers FROM users WHERE role = 'customer'");
                $totalCustomers = $result->fetch_assoc()['total_customers'];

                // CUSTOMERS BY PROVINCE
                $provinceQuery = $conn->query("SELECT province, COUNT(*) AS count FROM users WHERE role = 'customer' GROUP BY province");

                // TOTAL LISTINGS
                $result = $conn->query("SELECT COUNT(*) AS total_listings FROM listings");
                $totalListings = $result->fetch_assoc()['total_listings'];

                // LISTINGS BY CATEGORY
                $categoryQuery = $conn->query("SELECT category, COUNT(*) AS count FROM listings GROUP BY category");
                ?>

                <div class="mt-4 p-3 border rounded bg-light">
                    <h6 class="text-primary">Report summary:</h6>
                    <ul>
                        <li><strong>Total customers:</strong> <?= $totalCustomers ?></li>
                        <li><strong>Customers by province:</strong>
                            <ul>
                                <?php while ($row = $provinceQuery->fetch_assoc()): ?>
                                    <li><?= htmlspecialchars($row['province']) ?>: <?= $row['count'] ?></li>
                                <?php endwhile; ?>
                            </ul>
                        </li>
                        <li><strong>Total listings:</strong> <?= $totalListings ?></li>
                        <li><strong>Listings by category:</strong>
                            <ul>
                                <?php while ($row = $categoryQuery->fetch_assoc()): ?>
                                    <li><?= htmlspecialchars($row['category']) ?>: <?= $row['count'] ?></li>
                                <?php endwhile; ?>
                            </ul>
                        </li>
                    </ul>

                    <form method="POST" action="generateReportPdf.php" class="d-inline">
                        <button type="submit" name="download_pdf" class="btn btn-success">
                            <!-- Functionality still to be implemented -->
                            <i class="bi bi-file-earmark-arrow-down me-1"></i> Download as PDF
                        </button>
                    </form>
                    <a href="adminDashboard.php" class="btn btn-secondary ms-2"><i class="bi bi-x-lg me-1"></i> Close</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>


    <?php if (in_array($admin_role, ['adminReportsAll', 'adminAll'])): ?>
        <div class="card mb-3">
            <div class="card-body">
                <h5><i class="bi bi-graph-up-arrow me-2"></i> Advanced reports</h5>
                <p>Includes admin stats and orders grouped by province/category.</p>
                <!-- Functionality still to be implemented -->
                <a href="admin_reports_advanced.php" class="btn btn-secondary">View advanced reports</a>
            </div>
        </div>
    <?php endif; ?>

</main>
<!-- Footer -->
    <?php include 'footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>