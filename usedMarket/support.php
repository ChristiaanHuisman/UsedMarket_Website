<?php
session_start();

if (!isset($_SESSION['role'])) {
  // No session role – fallback to previous page
  $dashboardLink = 'javascript:history.back()';
} elseif ($_SESSION['role'] === 'customer') {
  $dashboardLink = 'customerDashboard.php';
} elseif (strpos($_SESSION['role'], 'admin') === 0) {
  // Any role starting with 'admin'
  $dashboardLink = 'adminDashboard.php';
} else {
  // Fallback if role is unrecognized
  $dashboardLink = 'javascript:history.back()';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>UM - Support</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <!-- Bootstrap CSS and Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  
  <!-- Favicon (blue square) -->
  <link rel="icon" href='data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"><rect width="32" height="32" rx="8" ry="8" fill="%230d6efd"/></svg>'>

  <!-- Custom CSS -->
  <link rel="stylesheet" href="style.css">
</head>

<body class="d-flex flex-column min-vh-100 bg-light">

  <!-- Navbar: Fixed top bar with site name and back link -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top py-lg-3">
    <div class="container-fluid">

      <!-- Website logo/name with home icon -->
      <a class="navbar-brand" href="<?= $dashboardLink ?>">
        <i class="bi bi-house me-1"></i> UsedMarket
      </a>

      <!-- Navbar toggle for mobile view -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" 
        aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Back button link on the right side -->
      <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link text-white" href="<?= $dashboardLink ?>">
              <i class="bi bi-arrow-left-circle me-1"></i> Back
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Main Page Content -->
  <main class="flex-grow-1 container mt-5 pt-0 pb-5">

    <!-- Header Section -->
    <div class="text-center mb-4">
      <h2>Support</h2>
      <p class="text-muted">We're here to help! Reach out to us through any of the methods below.</p>
    </div>

    <!-- Contact Info Card -->
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-lg border-0">
          <div class="card-body">
            <p class="mb-3">
              <i class="bi bi-telephone-fill text-primary me-2"></i>
              <strong>Phone:</strong> +27 21 345 6789
            </p>
            <p class="mb-3">
              <i class="bi bi-envelope-fill text-primary me-2"></i>
              <strong>Email:</strong> support@usedmarket.co.za
            </p>
            <p class="mb-3">
              <i class="bi bi-instagram text-primary me-2"></i>
              <strong>Instagram:</strong> @usedmarket_sa
            </p>
            <hr>
            <p class="text-center text-muted mb-1">
              UsedMarket&copy; <?= date("Y") ?> --- All rights reserved.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- FAQs Section -->
    <div class="mt-5">
      <h3 class="text-center mb-4">Frequently asked questions</h3>

      <!-- Accordion Wrapper -->
      <div class="accordion" id="faqAccordion">

        <!-- FAQ 1: Posting a Listing -->
        <div class="accordion-item">
          <h2 class="accordion-header" id="faqOneHeading">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
            data-bs-target="#faqOne" aria-expanded="false" aria-controls="faqOne">
              How do I post a new listing?
            </button>
          </h2>
          <div id="faqOne" class="accordion-collapse collapse" aria-labelledby="faqOneHeading"
          data-bs-parent="#faqAccordion">
            <div class="accordion-body">
              To post a listing, log in to your account, click on "Manage my listings,"
              then use the "Create new listing" button to open the form.
            </div>
          </div>
        </div>

        <!-- FAQ 2: Editing or Deleting Listings -->
        <div class="accordion-item">
          <h2 class="accordion-header" id="faqTwoHeading">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqTwo" aria-expanded="false" aria-controls="faqTwo">
              Can I edit or delete my listings?
            </button>
          </h2>
          <div id="faqTwo" class="accordion-collapse collapse" aria-labelledby="faqTwoHeading" data-bs-parent="#faqAccordion">
            <div class="accordion-body">
              Yes. Log in, go to "Manage my listings" and click on the Edit or Delete buttons on any of your active listings.
            </div>
          </div>
        </div>

        <!-- FAQ 3: Platform Cost -->
        <div class="accordion-item">
          <h2 class="accordion-header" id="faqThreeHeading">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqThree" aria-expanded="false" aria-controls="faqThree">
              Is UsedMarket free to use?
            </button>
          </h2>
          <div id="faqThree" class="accordion-collapse collapse" aria-labelledby="faqThreeHeading" data-bs-parent="#faqAccordion">
            <div class="accordion-body">
              Yes! Creating an account, browsing the marketplace, and posting listings are completely free.
            </div>
          </div>
        </div>

        <!-- FAQ 4: Payments -->
        <div class="accordion-item">
          <h2 class="accordion-header" id="faqFourHeading">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqFour" aria-expanded="false" aria-controls="faqFour">
              How are payments handled?
            </button>
          </h2>
          <div id="faqFour" class="accordion-collapse collapse" aria-labelledby="faqFourHeading" data-bs-parent="#faqAccordion">
            <div class="accordion-body">
              Payments are made directly to sellers through the UsedMarket platform. Be sure to confirm all details before proceeding.
            </div>
          </div>
        </div>

        <!-- FAQ 5: Delivery Process -->
        <div class="accordion-item">
          <h2 class="accordion-header" id="faqFiveHeading">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqFive" aria-expanded="false" aria-controls="faqFive">
              How do I receive the product I bought?
            </button>
          </h2>
          <div id="faqFive" class="accordion-collapse collapse" aria-labelledby="faqFiveHeading" data-bs-parent="#faqAccordion">
            <div class="accordion-body">
              UsedMarket does not directly provide delivery services. However, when a product is purchased and shipping is paid for, the item will be couriered from the seller's listed address to the buyer's provided address. All delivery arrangements are based on the details provided by both parties, so it's important to communicate clearly with the seller after purchase.
            </div>
          </div>
        </div>

        <!-- FAQ 6: Product Condition Responsibility -->
        <div class="accordion-item">
          <h2 class="accordion-header" id="faqSixHeading">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqSix" aria-expanded="false" aria-controls="faqSix">
              Is UsedMarket responsible for the condition of items sold?
            </button>
          </h2>
          <div id="faqSix" class="accordion-collapse collapse" aria-labelledby="faqSixHeading" data-bs-parent="#faqAccordion">
            <div class="accordion-body">
              No, UsedMarket is not liable for the condition or quality of any products listed or sold on the platform. We operate purely as a middleman platform to connect buyers and sellers. It is the responsibility of users to inspect items, communicate clearly, and ensure satisfaction before completing a transaction.
            </div>
          </div>
        </div>

      </div> <!-- End Accordion -->

    </div> <!-- End FAQ Section -->

  </main>

  <!-- Footer Include -->
  <?php include 'footer.php'; ?>

  <!-- Bootstrap JavaScript Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>