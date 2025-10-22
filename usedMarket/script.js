// script.js - used for interactive JS on UsedMarket


// Field toggle depending on role chosen
document.addEventListener('DOMContentLoaded', function () {
    // Get the dropdown select element where users choose their role
    const roleSelect = document.querySelector('select[name="role"]');

    // Get the admin code field container (only if registering as admin)
    const adminCodeField = document.getElementById('adminCodeField');

    // Function to toggle visibility of the admin code input
    function toggleAdminField() {
        // Show the field only if the selected role is "admin"
        if (roleSelect.value === 'admin') {
            adminCodeField.style.display = 'block';
        } else {
            adminCodeField.style.display = 'none';
        }
    }

    // Initial check on page load
    toggleAdminField();

    // Event listener to run the toggle function whenever the role selection changes
    roleSelect.addEventListener('change', toggleAdminField);
});


// auto reload page

document.addEventListener("DOMContentLoaded", () => {
  // Find the listing creation form inside the modal
  const form = document.getElementById('addListingForm');

  if (form) {
    // When the form is submitted, intercept it with JavaScript
    form.addEventListener('submit', function (e) {
      e.preventDefault(); // Prevent the page from refreshing

      const formData = new FormData(this); // Package form data including file upload

      // Use Fetch API to send form data asynchronously to customerListings.php
      fetch("customerListings.php", {
        method: "POST",
        body: formData
      })
      .then(response => response.text()) // Convert response to plain text (for debug/logging)
      .then(() => {
        // Close the modal after successful submission
        const modal = bootstrap.Modal.getInstance(document.getElementById('addListingModal'));
        modal.hide();

        // Reload the page after a short delay to show the new listing
        setTimeout(() => location.reload(), 400);
      });
    });
  }
});



// Show/hide password field toggle
document.addEventListener("DOMContentLoaded", function () {
  // Helper function that adds toggle functionality to a password input field
  function setupToggle(toggleId, inputId) {
    const toggle = document.getElementById(toggleId); // The toggle icon/button
    const input = document.getElementById(inputId);   // The input field to toggle

    if (toggle && input) {
      // When the toggle icon is clicked
      toggle.addEventListener("click", function () {
        const isPassword = input.type === "password";

        // Toggle between showing and hiding password
        input.type = isPassword ? "text" : "password";

        // Toggle icon class to indicate visibility state
        const icon = toggle.querySelector("i");
        if (icon) {
          icon.classList.toggle("bi-eye");        
          icon.classList.toggle("bi-eye-slash");
        }
      });
    }
  }

  // Apply password toggle to main password field
  setupToggle("togglePassword", "password");

  // Apply password toggle to confirmation field
  setupToggle("togglePasswordConfirm", "password_confirm");
});

// checkout total calculation
document.addEventListener("DOMContentLoaded", function () {
  const shippingRadios = document.querySelectorAll('input[name="shipping"]');
  const totalAmountEl = document.getElementById("totalAmount");

  if (typeof usedMarketListingPrice !== "undefined" && typeof usedMarketShippingCost !== "undefined" && totalAmountEl) {
    function updateTotal() {
      const selectedShipping = document.querySelector('input[name="shipping"]:checked');
      const shippingCost = (selectedShipping && selectedShipping.value === "1") ? usedMarketShippingCost : 0;
      const total = (parseFloat(usedMarketListingPrice) + shippingCost).toFixed(2);
      totalAmountEl.textContent = total;
    }

    shippingRadios.forEach(radio => {
      radio.addEventListener("change", updateTotal);
    });

    updateTotal(); // Set on load
  }
});