<?php
require 'db.php';

// Initialize error and success message arrays
$errors = [];
$success = "";

// Initialize form values for sticky fields
$name = $email = $phone = $street = $city = $province = $bank = $account_number = $role_input = $admin_code = "";

// Handle form submission and validation
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitize input helper function
    function clean($val) {
        return htmlspecialchars(trim($val));
    }

    // Capture and sanitize POST values
    $name = clean($_POST['name']);
    $email = clean($_POST['email']);
    $phone = clean($_POST['phone']);
    $street = clean($_POST['street']);
    $city = clean($_POST['city']);
    $province = clean($_POST['province']);
    $bank = clean($_POST['bank']);
    $account_number = clean($_POST['account_number']);
    $password = $_POST['password']; // Raw password will be hashed later
    $role_input = $_POST['role'];
    $admin_code = isset($_POST['admin_code']) ? clean($_POST['admin_code']) : "";

    // Check for empty required fields
    if (
        !$name || !$email || !$phone || !$street || !$city || !$province || !$bank || !$account_number || !$password
    ) {
        $errors[] = "Please fill in all required fields.";
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Phone validation: remove spaces, ensure exactly 10 digits
    $cleanedPhone = str_replace(' ', '', $phone);
    if (!ctype_digit($cleanedPhone) || strlen($cleanedPhone) !== 10) {
        $errors[] = "Phone number must be exactly 10 digits and contain digits only.";
    }

    // Account number validation: must be digits only, can start with 0
    if (!ctype_digit($account_number)) {
        $errors[] = "Account number must contain digits only.";
    }

    // Password validation: 8–25 characters, at least one uppercase, one digit, one special character, no spaces
    if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[^\s]{8,25}$/', $password)) {
        $errors[] = "Password must be 8-25 characters long, contain at least one uppercase letter, one number, one special character, and no spaces.";
    }

    // Role assignment logic
    // Should ideally be moved to a safe storing and checking mechanism
    $role = "customer";
    if ($role_input === "admin") {
        switch ($admin_code) {
            case "1010":
                $role = "adminManageCustomers";
                break;
            case "1020":
                $role = "adminManageAll";
                break;
            case "1030":
                $role = "adminReportsBase";
                break;
            case "1040":
                $role = "adminReportsAll";
                break;
            case "1050":
                $role = "adminAll";
                break;
            default:
                $errors[] = "Invalid admin code.";
                break;
        }
    }

    // If no validation errors, proceed to insert user
    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, phoneNumber, streetAddress, city, province, bankName, accountNumber, password, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssss", $name, $email, $cleanedPhone, $street, $city, $province, $bank, $account_number, $hashed, $role);
        if ($stmt->execute()) {
            $success = "Registration successful. <a href='login.php'>Login here</a>";
        } else {
            $errors[] = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UM - Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap and Icon CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href='data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"><rect width="32" height="32" rx="8" ry="8" fill="%230d6efd"/></svg>'>
    <link rel="stylesheet" href="style.css">
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

<!-- Fixed top navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top py-lg-3">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php"><i class="bi bi-house me-1"></i> UsedMarket</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link text-white" href="support.php"><i class="bi bi-question-circle me-1"></i> Support</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Registration form section -->
<main class="flex-grow-1 d-flex justify-content-center align-items-center py-5 mt-5 mb-5">
    <div class="col-md-8 bg-white p-4 shadow-lg rounded login-container">
        <h3 class="text-center">Create an Account</h3>

        <!-- Show validation errors -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $e) echo "<p>$e</p>"; ?>
            </div>
        <?php endif; ?>

        <!-- Show success message -->
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?= $success ?>
            </div>
        <?php endif; ?>

        <!-- Registration form -->
        <form action="register.php" method="POST" novalidate>
            <div class="row">

                <!-- Name -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($name) ?>">
                </div>

                <!-- Email -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="text" name="email" class="form-control" placeholder="you@example.com" value="<?= htmlspecialchars($email) ?>">
                </div>

                <!-- Phone number -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone number</label>
                    <input type="text" name="phone" class="form-control" placeholder="Format: 0823456789" value="<?= htmlspecialchars($phone) ?>">
                </div>

                <!-- Street -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Street address</label>
                    <input type="text" name="street" class="form-control" value="<?= htmlspecialchars($street) ?>">
                </div>

                <!-- City -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($city) ?>">
                </div>

                <!-- Province -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Province</label>
                    <select name="province" class="form-select">
                        <option value="">Select...</option>
                        <?php
                            $provinces = ["Gauteng", "Western Cape", "KwaZulu-Natal", "Eastern Cape", "Free State", "Limpopo", "Mpumalanga", "North West", "Northern Cape"];
                            foreach ($provinces as $prov) {
                                $selected = ($province === $prov) ? "selected" : "";
                                echo "<option value=\"$prov\" $selected>$prov</option>";
                            }
                        ?>
                    </select>
                </div>

                <!-- Bank name -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bank name</label>
                    <input type="text" name="bank" class="form-control" value="<?= htmlspecialchars($bank) ?>">
                </div>

                <!-- Account number -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Bank account number</label>
                    <input type="text" name="account_number" class="form-control" value="<?= htmlspecialchars($account_number) ?>">
                </div>

                <!-- Password -->
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-group">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter secure password">
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <!-- Role selection -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Account type</label>
                    <select name="role" class="form-select" id="roleSelect">
                        <option value="customer" <?= ($role_input === "customer") ? 'selected' : '' ?>>Customer</option>
                        <option value="admin" <?= ($role_input === "admin") ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>

                <!-- Admin code input (conditionally shown) -->
                <div class="col-12 mb-3" id="adminCodeField" style="display: none;">
                    <label class="form-label">Admin code</label>
                    <input type="text" name="admin_code" class="form-control" value="<?= htmlspecialchars($admin_code) ?>">
                </div>

                <!-- Submit -->
                <div class="col-12 d-grid">
                    <button type="submit" class="btn btn-success">Register</button>
                </div>
            </div>
        </form>

        <!-- Login link -->
        <div class="mt-3 text-center">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>
</body>
</html>