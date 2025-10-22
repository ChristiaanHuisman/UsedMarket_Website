<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'] ?? 'customer';
$dashboardLink = ($role === 'customer') ? 'customerDashboard.php' : 'adminDashboard.php';

$user_id = $_SESSION['user_id'];
$errors = [];
$success = "";

// Retrieve current user data from database
$stmt = $conn->prepare("SELECT name, email, phoneNumber, streetAddress, city, province, bankName, accountNumber FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User not found.");
}

$provinces = ["Eastern Cape", "Free State", "Gauteng", "KwaZulu-Natal", "Limpopo", "Mpumalanga", "North West", "Northern Cape", "Western Cape"];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = str_replace(' ', '', $_POST['phone']);
    $street = trim($_POST['street']);
    $city = trim($_POST['city']);
    $province = $_POST['province'];
    $bank = trim($_POST['bank']);
    $account_number = trim($_POST['account_number']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // Validate name
    if (!$name) {
        $errors[] = "Name is required.";
    }

    // Validate email format and uniqueness
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email is required.";
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->bind_param("si", $email, $user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "Email is already taken.";
    }
    $stmt->close();

    // Validate phone number - exactly 10 digits (no spaces)
    $cleanedPhone = str_replace(' ', '', $phone);
    if (!ctype_digit($cleanedPhone) || strlen($cleanedPhone) !== 10) {
        $errors[] = "Phone number must be exactly 10 digits and contain digits only.";
    }

    // Validate required address fields and province
    if (!$street) {
        $errors[] = "Street address is required.";
    }

    if (!$city) {
        $errors[] = "City is required.";
    }

    if (!in_array($province, $provinces)) {
        $errors[] = "Invalid province selected.";
    }

    // Validate bank name and account number format
    if (!$bank) {
        $errors[] = "Bank name is required.";
    }

    if (!preg_match('/^\d+$/', $account_number)) {
        $errors[] = "Account number must contain digits only.";
    }

    // Validate password if provided (optional)
    if ($password || $password_confirm) {
        if ($password !== $password_confirm) {
            $errors[] = "Passwords do not match.";
        } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_])[A-Za-z\d\W_]{8,25}$/', $password)) {
            $errors[] = "Password must be 8-25 characters long, include at least one uppercase letter, one number, one special character, and no spaces.";
        }
    }

    // Proceed with database update if there are no validation errors
    if (empty($errors)) {
        // Check if the user submitted a new password
        if ($password) {
            // If a new password was entered, hash it
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            // Prepare mySQL statement to update all fields, preventing SQL injection
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phoneNumber = ?, streetAddress = ?, city = ?,
            province = ?, bankName = ?, accountNumber = ?, password = ? WHERE id = ?");
            // Bind parameters to the mySQL query (9 strings + 1 integer)
            $stmt->bind_param("sssssssssi", $name, $email, $cleanedPhone, $street, $city, $province, $bank,
            $account_number, $hashed, $user_id);
        } else {
            // If no new password was submitted, update all other fields
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phoneNumber = ?, streetAddress = ?, city = ?,
            province = ?, bankName = ?, accountNumber = ? WHERE id = ?");
            // Bind parameters (8 strings + 1 integer)
            $stmt->bind_param("ssssssssi", $name, $email, $cleanedPhone, $street, $city, $province, $bank,
            $account_number, $user_id);
        }

        // Execute the prepared statement and check if it successfully updated
        if ($stmt->execute()) {
            $success = "Account updated successfully.";
            // Update the session name
            $_SESSION['name'] = $name;
        } else {
            // If an error occurred, add the error message to an array
            $errors[] = "Database error: " . $stmt->error;
        }

        // Close the prepared statement
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>UM - Edit account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="icon" href='data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"><rect width="32" height="32" rx="8" ry="8" fill="%230d6efd"/></svg>'>
    <link rel="stylesheet" href="style.css" />
</head>
<body class="d-flex flex-column min-vh-100 bg-light">

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
                    <a class="nav-link text-white" href="<?= $dashboardLink ?>">
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

<main class="flex-grow-1 container mt-5 pt-0 pb-5" style="max-width: 700px;">
    <h2 class="text-center">Edit account</h2>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul class="mb-0 list-unstyled">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" novalidate>
        <?php
        // Helper function for form value persistence
        function value($field, $fallback) {
            return htmlspecialchars($_POST[$field] ?? $fallback);
        }
        ?>
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="<?= value('name', $user['name']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= value('email', $user['email']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Phone number</label>
            <input type="text" name="phone" class="form-control" value="<?= value('phone', $user['phoneNumber']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Street address</label>
            <input type="text" name="street" class="form-control" value="<?= value('street', $user['streetAddress']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">City</label>
            <input type="text" name="city" class="form-control" value="<?= value('city', $user['city']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Province</label>
            <select name="province" class="form-select">
                <?php foreach ($provinces as $p): ?>
                    <option value="<?= $p ?>" <?= (value('province', $user['province']) === $p) ? 'selected' : '' ?>><?= $p ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Bank name</label>
            <input type="text" name="bank" class="form-control" value="<?= value('bank', $user['bankName']) ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Bank account number</label>
            <input type="text" name="account_number" class="form-control" value="<?= value('account_number', $user['accountNumber']) ?>">
        </div>

        <h5>Change password <small class="text-muted">(leave blank to keep current password)</small></h5>

        <div class="mb-3">
            <label class="form-label">New password</label>
            <div class="input-group">
                <input type="password" id="password" name="password" class="form-control">
                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                    <i class="bi bi-eye-slash"></i>
                </button>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Confirm new password</label>
            <div class="input-group">
                <input type="password" id="password_confirm" name="password_confirm" class="form-control">
                <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                    <i class="bi bi-eye-slash"></i>
                </button>
            </div>
        </div>

        <div class="text-center">
            <button type="submit" name="update_user" class="btn btn-success mt-3">
                <i class="bi bi-check-circle me-1"></i> Save changes
            </button>
        </div>
        <hr class="my-5 hr-thick">
    </form>
        <form method="POST" action="deleteAccount.php" onsubmit="return confirm('Are you sure you want to delete your account?');">
    <input type="hidden" name="user_id" value="<?= $user_id ?>">
        <div class="text-center">
        <h2>Delete account permanently</h2>
        <h5><small class="text-muted">(only if you are certain, we will be sad to see you go)</small></h5>
        <button type="submit" class="btn btn-danger me-1 mt-3"><i class="bi bi-trash me-1"></i> Delete account</button>
        </div>
    </form>
</main>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>

</body>
</html>