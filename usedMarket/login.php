<?php
session_start();
require 'db.php';

// Initialize error message and email to preserve input if validation fails
$error = "";
$email = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitize email input (remove surrounding whitespace)
    $email = trim($_POST['email'] ?? '');

    // Get raw password input
    $password = $_POST['password'] ?? '';

    // === Server-side Validation ===

    // Check if email or password is empty
    if ($email === "" || $password === "") {
        $error = "Please enter both email and password.";

    // Check for valid email format using PHP's built-in filter
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";

    } else {
        // === Email format is valid, attempt to fetch user ===

        // Prepare SQL statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, name, password, role, province FROM users WHERE email = ?");

        // Bind the email parameter as a string ("s")
        $stmt->bind_param("s", $email);

        // Execute the query
        $stmt->execute();

        // Get the result set from executed statement
        $result = $stmt->get_result();

        // If a user with the provided email exists
        if ($result->num_rows === 1) {

            // Fetch user details into associative array
            $user = $result->fetch_assoc();

            // Check the submitted password against the hashed password in database
            if (password_verify($password, $user['password'])) {

                // Successful login, set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['province'] = $user['province'];

                // Redirect to admin or customer dashboard based on role
                if (in_array($user['role'], ['adminManageCustomers', 'adminManageAll', 'adminReportsBase', 'adminReportsAll', 'adminAll'])) {
                    header("Location: adminDashboard.php");
                } else {
                    header("Location: customerDashboard.php");
                }

                // End further script execution
                exit;

            } else {
                // Password does not match
                $error = "Incorrect password.";
            }

        } else {
            // No user found with the entered email
            $error = "No account found with that email.";
        }

        // Close prepared statement
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>UM - Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />

    <!-- Custom Favicon -->
    <link rel="icon" href='data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"><rect width="32" height="32" rx="8" ry="8" fill="%230d6efd"/></svg>' />

    <!-- Custom Styles -->
    <link rel="stylesheet" href="style.css" />
</head>

<body class="d-flex flex-column min-vh-100 bg-light">

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top py-lg-3">
        <div class="container-fluid">

            <!-- Brand -->
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-house me-1"></i> UsedMarket
            </a>

            <!-- Toggler for small screens -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Items -->
            <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="support.php">
                            <i class="bi bi-question-circle me-1"></i> Support
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow-1 d-flex justify-content-center align-items-center py-5 mt-5 mb-5">
        <div class="col-md-6 col-lg-5 bg-white p-4 shadow-lg rounded">

            <!-- Title -->
            <h3 class="text-center">Login to your account</h3>

            <!-- Error Message -->
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="login.php" method="POST" novalidate>

                <!-- Email Field -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-envelope-fill"></i>
                        </span>
                        <input type="text" class="form-control" id="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($email); ?>" autocomplete="email">
                    </div>
                </div>

                <!-- Password Field -->
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock-fill"></i>
                        </span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" autocomplete="current-password">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="bi bi-eye-slash" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-grid">
                    <button type="submit" class="btn btn-success">Login</button>
                </div>
            </form>

            <!-- Register Link -->
            <div class="mt-3 text-center">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>

</body>
</html>