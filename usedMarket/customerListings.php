<?php
// Start the session so that session variables can be used
session_start();

// Include the database connection script
require 'db.php';

// Check if the user is not logged in by seeing if the 'user_id' session variable is not set
if (!isset($_SESSION['user_id'])) {
    // If the user is not logged in, redirect them to the login page, this prevents unauthorized access to this page
    header("Location: login.php");
    exit;
}

//Retrieve current userID from the session and store it in this variable
$user_id = $_SESSION['user_id'];


// Fetch user's listings
$stmt = $conn->prepare("SELECT * FROM listings WHERE userId = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$listings = $stmt->get_result();

// Handle listing creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_listing'])) {

    $errors = [];

    // Get & validate form input
    $title = trim($_POST['title']);
    $price = floatval($_POST['price']);
    $description = trim($_POST['description']);
    $condition = $_POST['listingCondition'];
    $category = $_POST['category'];

    if (!$title || !$price || !$condition || !$category || !$user_id) {
        $errors[] = "All fields are required.";
    }

// Image upload validation
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== 0) {
    // Check if an image file was uploaded
    $errors[] = "Image upload failed.";
}

if (empty($errors)) {

    // Extract the file extension and convert it to lowercase
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

    // Allowed image extensions
    $allowed = ['jpg', 'jpeg', 'png'];

    // If the uploaded file type is not allowed, stop with an error
    if (!in_array($ext, $allowed)) {
        die("Invalid file type: $ext");
    }

    // Temporary file path for where the image is stored after upload
    $tmp = $_FILES['image']['tmp_name'];

    // Screate a new unique name
    // All images will be saved as a jpg
    $newName = 'uploads/' . uniqid('listing_', true) . '.jpg';

    // Get the width and height of the uploaded image
    list($w, $h) = getimagesize($tmp);
    if (!$w || !$h) {
        // If dimensions can’t be determined, something is wrong with the image
        die("Could not read image dimensions.");
    }

    // Load the image into memory depending on file type (jpeg or png)
    $src = ($ext === 'png') ? imagecreatefrompng($tmp) : imagecreatefromjpeg($tmp);
    if (!$src) {
        die("Could not create image from file.");
    }

    // Resize the image to a width of 800px and the proportional height
    $dst = imagecreatetruecolor(800, 800 * $h / $w);

    // Copy and resize the original image into the new blank image
    imagecopyresampled($dst, $src, 0, 0, 0, 0, 800, 800 * $h / $w, $w, $h);

    // Save the compressed image to the uploads folder in jpeg format (quality at 85%)
    if (!imagejpeg($dst, $newName, 85)) {
        die("Failed to save resized image.");
    }

    // Free memory
    imagedestroy($src);
    imagedestroy($dst);

    // Save the image path to use when inserting it into the database
    $image_path = $newName;
}


    // INSERT INTO DB
    $stmt = $conn->prepare("INSERT INTO listings (title, price, imagePath, description, listingCondition, category, userId)
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sdssssi", $title, $price, $image_path, $description, $condition, $category, $user_id);

    if ($stmt->execute()) {
        echo "Listing added successfully.";
    } else {
        die("Insert failed: " . $stmt->error);
    }
    $stmt->close();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UM - Customer listings</title>
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

<!-- Page content -->
<main class="flex-grow-1 container mt-5 pt-0 pb-5">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        
        <!-- Left: My listings + Back -->
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2">
            <h2 class="mb-0">My listings</h2>
            <a href="customerDashboard.php" class="btn btn-outline-primary">
                <i class="bi bi-arrow-left-circle me-1"></i> Back to browsing
            </a>
        </div>

        <!-- Right: Create new listing -->
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addListingModal">
            <i class="bi bi-plus-circle me-1"></i> Create new listing
        </button>
    </div>

    <?php if ($listings->num_rows > 0): ?>
        <div class="row">
            <?php while ($row = $listings->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm mylisting-card d-flex flex-column h-100">
                        <img src="<?= htmlspecialchars($row['imagePath']) ?>" class="card-img-top" alt="Listing image">
                        <div class="card-body flex-grow-1">
                            <h4 class="card-title"><?= htmlspecialchars($row['title']) ?></h4>
                            <p class="card-text mb-2"><strong>R<?= htmlspecialchars($row['price']) ?></strong></p>
                            <p class="text-muted mb-3 small">Listed: <?= date('d M Y', strtotime($row['dateListed'])) ?></p>
                            <div class="d-flex justify-content-between">
                                <a href="edit_listing.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil-square me-1"></i> Edit
                                </a>
                                <form action="deleteListing.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this listing?');" style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash me-1"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info">You haven't created any listings yet.</div>
    <?php endif; ?>
    </main>

<!-- Add Listing Modal -->
<div class="modal fade" id="addListingModal" tabindex="-1" aria-labelledby="addListingLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form id="addListingForm" enctype="multipart/form-data" method="POST" class="modal-content">
        <input type="hidden" name="add_listing" value="1">

        <div class="modal-header">
            <h5 class="modal-title" id="addListingLabel">New listing</h5>
            <button type="button" class="btn btn-sm btn-danger" data-bs-dismiss="modal" aria-label="Close">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="modal-body row g-3">
        <div class="col-md-6">
          <label class="form-label">Title</label>
          <input type="text" name="title" class="form-control" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Price (R)</label>
          <input type="number" name="price" class="form-control" step="1" required>
        </div>

        <div class="col-md-12">
          <label class="form-label">Image</label>
          <input type="file" name="image" accept="image/*" class="form-control" required>
        </div>

        <div class="col-md-12">
          <label class="form-label">Description</label>
          <textarea name="description" rows="3" class="form-control"></textarea>
        </div>

        <div class="col-md-6">
          <label class="form-label">Condition</label>
          <select name="listingCondition" class="form-select" required>
            <option value="">Select</option>
            <option>New</option>
            <option>Good</option>
            <option>Used</option>
            <option>Poor</option>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label">Category</label>
          <select name="category" class="form-select" required>
            <option value="">Select</option>
            <option>Home & Living</option>
            <option>Electronics</option>
            <option>Vehicles</option>
            <option>Clothing</option>
            <option>Tools & DIY</option>
            <option>Books & Media</option>
            <option>Leisure & Hobbies</option>
            <option>Baby & Kids</option>
            <option>Other</option>
          </select>
        </div>
      </div>

      <div class="modal-footer justify-content-center">
        <button type="submit" class="btn btn-success mb-0"><i class="bi bi-check-circle me-1"></i> Publish listing</button>
      </div>
    </form>
  </div>
</div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
</body>
</html>