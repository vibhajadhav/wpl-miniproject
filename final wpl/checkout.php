<?php
// Start session to manage user data
session_start();

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit(); // Make sure to exit after redirect
}

// Get the logged-in username
$username = $_SESSION['username'];

// Database connection details
$db_host = 'localhost';
$db_name = 'pharmacy';
$db_user = 'root';
$db_pass = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Function to get customer details
    function getCustomerDetails($username) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM customer WHERE username = :username");
        $stmt->execute(['username' => $username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Function to get company details (for invoice header)
    function getCompanyDetails() {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM company LIMIT 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Function to save payment to database
    function savePayment($amount, $orderId) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("INSERT INTO payment (amount, order_id) VALUES (:amount, :order_id)");
            $stmt->execute([
                'amount' => $amount,
                'order_id' => $orderId
            ]);
            return true;
        } catch (PDOException $e) {
            error_log("Payment save error: " . $e->getMessage());
            return false;
        }
    }

    // Fetch customer and company data
    $customer = getCustomerDetails($username);
    $company = getCompanyDetails();

    // Generate a random order ID
    $order_id = mt_rand(12365, 99999);

} catch (PDOException $e) {
    // Handle database connection error
    die("Database connection failed: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Checkout</title>
  <link rel="stylesheet" href="styles.css" />
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
  />
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
    defer
  ></script>

  <style>
    /* Updated CSS for search bar proportions and navbar styling */
    .navbar {
      padding-top: 0.75rem;
      padding-bottom: 0.75rem;
    }
    
    .search-container {
      position: relative;
      flex: 1;
      max-width: 900px;
      margin: 0 auto;
    }
    
    .search-container form {
      display: flex;
      width: 100%;
      height: 45px; /* Increased height */
    }
    
    .search-container input {
      flex: 1;
      min-width: 0;
      border-top-right-radius: 0;
      border-bottom-right-radius: 0;
      height: 100%;
      font-size: 0.95rem;
    }
    
    .search-container button {
      width: 100px; /* Fixed width for button */
      white-space: nowrap;
      flex-shrink: 0;
      border-top-left-radius: 0;
      border-bottom-left-radius: 0;
      height: 100%;
      font-size: 0.95rem;
    }
    
    #searchResults {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      background: white;
      border: 1px solid #ddd;
      border-top: none;
      z-index: 1000;
      max-height: 300px;
      overflow-y: auto;
      display: none;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .search-result-item {
      padding: 10px;
      cursor: pointer;
      border-bottom: 1px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .search-result-item:hover {
      background-color: #f5f5f5;
    }
    
    .nav-icons {
      display: flex;
      gap: 1.5rem;
    }
    
    .nav-icons a, .nav-icons .dropdown {
      text-decoration: none;
      color: #333;
      font-weight: 500;
    }
    
    .delivery-location {
      cursor: pointer;
    }
    
    /* Checkout specific styles */
    .checkout-section {
      border: 1px solid #eee;
      border-radius: 8px;
      padding: 20px;
      margin-bottom: 20px;
    }
    
    .checkout-header {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
    }
    
    .checkout-number {
      width: 30px;
      height: 30px;
      border-radius: 50%;
      background-color: #007bff;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 10px;
      font-weight: bold;
    }
    
    .address-card {
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 15px;
      cursor: pointer;
      transition: all 0.2s;
    }
    
    .address-card.selected {
      border-color: #007bff;
      background-color: #f0f7ff;
    }
    
    .address-card:hover {
      border-color: #007bff;
    }
    
    .order-item {
      display: flex;
      justify-content: space-between;
      margin-bottom: 10px;
      padding-bottom: 10px;
      border-bottom: 1px solid #eee;
    }
    
    .order-item:last-child {
      border-bottom: none;
    }
    
    .payment-method {
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 10px;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: center;
    }
    
    .payment-method.selected {
      border-color: #007bff;
      background-color: #f0f7ff;
    }
    
    .payment-method:hover {
      border-color: #007bff;
    }
    
    .payment-method input {
      margin-right: 10px;
    }
  </style>
</head>
<body>
<!-- Updated Navigation Bar with Dropdown -->
<nav class="navbar navbar-expand-lg bg-white shadow-sm">
  <div class="container d-flex align-items-center">
    <a class="navbar-brand me-4" href="index.php">
      <img src="logo.png" alt="Logo" height="42" />
    </a>
    
    <div class="delivery-location me-4" data-bs-toggle="modal" data-bs-target="#pincodeModal">
      <div style="font-size: 13px;">Express delivery to</div>
      <b class="current-pincode d-block">Select Pincode</b>
    </div>
    
    <!-- Search Bar with adjusted proportions -->
    <div class="search-container mx-4">
      <form class="d-flex" id="searchForm">
        <input
          class="form-control"
          type="search"
          id="searchInput"
          name="search"
          placeholder="Search for medicines to add..."
          aria-label="Search"
          autocomplete="off"
        />
        <button class="btn btn-primary" type="submit">
          Search
        </button>
      </form>
      <div id="searchResults"></div>
    </div>
    
    <div class="nav-icons ms-auto">
      <a href="cart.php" class="cart d-flex align-items-center">
        <span style="font-size: 18px; margin-right: 5px;">🛒</span> Cart
      </a>
      
      <!-- Contact Us Dropdown -->
      <div class="dropdown">
        <a href="#" class="d-flex align-items-center dropdown-toggle" id="contactDropdown" data-bs-toggle="dropdown" aria-expanded="false">
          <span style="font-size: 16px; margin-right: 5px;">📞</span> Contact Us
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="contactDropdown">
          <li><a class="dropdown-item" href="contact.php">Send Message</a></li>
          <li><a class="dropdown-item" href="tel:+1234567890">Call Us</a></li>
          <li><a class="dropdown-item" href="mailto:support@pharma.com">Email Us</a></li>
        </ul>
      </div>
      
      <div class="dropdown">
    <?php if(isset($_SESSION['username'])): ?>
        <a href="#" class="d-flex align-items-center dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <span style="font-size: 16px; margin-right: 5px;">👤</span> 
            <?php echo htmlspecialchars($_SESSION['username']); ?>
        </a>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
            <li><a class="dropdown-item active" href="profile.php">My Profile</a></li>
            <li><a class="dropdown-item" href="orders.php">Your Orders</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item" href="logout.php">Logout</a>
            </li>
        </ul>
    <?php else: ?>
        <a href="login.php" class="d-flex align-items-center">
            <span style="font-size: 16px; margin-right: 5px;">👤</span> 
            Login
        </a>
    <?php endif; ?>
</div>
        </ul>
      </div>
    </div>
  </div>
</nav>

<!-- Pincode Modal -->
<div class="modal fade pincode-modal" id="pincodeModal" tabindex="-1" aria-labelledby="pincodeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="pincodeModalLabel">Change Delivery Location</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Enter your pincode to check delivery availability</p>
        <input type="text" class="pincode-input" placeholder="Enter 6-digit pincode" maxlength="6" pattern="\\d{6}" required>
        <button class="pincode-submit" onclick="updatePincode()">Check & Update</button>
        <div class="mt-3" id="pincodeMessage"></div>
      </div>
    </div>
  </div>
</div>

<!-- Billing Address Modal -->
<div class="modal fade" id="billingAddressModal" tabindex="-1" aria-labelledby="billingAddressModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="billingAddressModalLabel">Enter Billing Address</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="billingAddressForm">
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="fullName" class="form-label">Full Name</label>
              <input type="text" class="form-control" id="fullName" value="<?php echo $customer['username']; ?>" required>
            </div>
            <div class="col-md-6">
              <label for="phoneNumber" class="form-label">Phone Number</label>
              <input type="tel" class="form-control" id="phoneNumber" value="<?php echo $customer['phoneno']; ?>" required>
            </div>
          </div>
          <div class="mb-3">
            <label for="addressLine1" class="form-label">Address Line 1</label>
            <input type="text" class="form-control" id="addressLine1" value="<?php echo $customer['address']; ?>" required>
          </div>
          <div class="mb-3">
            <label for="addressLine2" class="form-label">Address Line 2 (Optional)</label>
            <input type="text" class="form-control" id="addressLine2">
          </div>
          <div class="row mb-3">
            <div class="col-md-4">
              <label for="city" class="form-label">City</label>
              <input type="text" class="form-control" id="city" required>
            </div>
            <div class="col-md-4">
              <label for="state" class="form-label">State</label>
              <input type="text" class="form-control" id="state" required>
            </div>
            <div class="col-md-4">
              <label for="pincode" class="form-label">Pincode</label>
              <input type="text" class="form-control" id="pincode" required>
            </div>
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="sameAsShipping" checked>
            <label class="form-check-label" for="sameAsShipping">
              Same as shipping address
            </label>
          </div>
          <button type="submit" class="btn btn-primary">Save Address</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Checkout Content -->
<div class="container my-5">
  <h2 class="mb-4">Checkout</h2>
  
  <div class="row">
    <!-- Checkout Steps Column -->
    <div class="col-lg-8">
      <!-- Delivery Address Section -->
      <div class="checkout-section">
        <div class="checkout-header">
          <div class="checkout-number">1</div>
          <h4>Delivery Address</h4>
        </div>
        
        <div id="addressContainer">
          <div class="address-card selected">
            <div class="d-flex justify-content-between">
              <strong id="deliveryName"><?php echo $customer['username']; ?></strong>
              <span class="badge bg-success">Default</span>
            </div>
            <p id="deliveryAddress" class="mb-1"><?php echo $customer['address']; ?></p>
            <p id="deliveryPhone" class="mb-0">Phone: <?php echo $customer['phoneno']; ?></p>
          </div>
          
          <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#billingAddressModal">
            <i class="bi bi-plus"></i> Add New Address
          </button>
        </div>
      </div>
      
      <!-- Order Summary Section -->
      <div class="checkout-section">
        <div class="checkout-header">
          <div class="checkout-number">2</div>
          <h4>Order Summary</h4>
        </div>
        
        <div id="orderItemsContainer">
          <!-- Order items will be dynamically inserted here -->
        </div>
      </div>
      
      <!-- Payment Method Section -->
      <div class="checkout-section">
        <div class="checkout-header">
          <div class="checkout-number">3</div>
          <h4>Payment Method</h4>
        </div>
        
        <div>
          <div class="payment-method selected">
            <input type="radio" name="paymentMethod" id="cod" value="cod" checked>
            <label for="cod">Cash on Delivery</label>
          </div>
          
          <div class="payment-method">
            <input type="radio" name="paymentMethod" id="card" value="card">
            <label for="card">Credit/Debit Card</label>
          </div>
          
          <div class="payment-method">
            <input type="radio" name="paymentMethod" id="upi" value="upi">
            <label for="upi">UPI</label>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Order Total Column -->
    <div class="col-lg-4">
      <div class="summary-card">
        <h4 class="mb-4">Order Total</h4>
        
        <div class="d-flex justify-content-between mb-2">
          <span>Subtotal</span>
          <span id="subtotal">₹0</span>
        </div>
        <div class="d-flex justify-content-between mb-2">
          <span>Delivery</span>
          <span id="delivery">₹49</span>
        </div>
        <div class="d-flex justify-content-between mb-3">
          <span>Discount</span>
          <span id="discount" class="text-danger">-₹0</span>
        </div>
        <hr>
        <div class="d-flex justify-content-between mb-3 fw-bold">
          <span>Total</span>
          <span id="total">₹49</span>
        </div>
        
        <button id="placeOrderBtn" class="btn btn-primary w-100">Place Order</button>
        <p class="text-muted small mt-2">By placing your order, you agree to Pharma's <a href="#">Terms of Use</a></p>
      </div>
    </div>
  </div>
</div>

<!-- Footer -->
<footer class="bg-primary text-white py-4">
  <div class="container">
    <div class="row">
      <div class="col-md-3 mb-3">
        <a href="/" class="d-inline-flex align-items-center mb-2 link-light text-decoration-none">
          <img src="logo.png" height="42" class="me-2" alt="Logo">
        </a>
        <p>Your trusted online pharmacy.</p>
      </div>

      <div class="col-md-3 mb-3">
        <h5>Quick Links</h5>
        <ul class="list-unstyled">
          <li><a href="#" class="text-white text-decoration-none">Home</a></li>
          <li><a href="medicine-list.php" class="text-white text-decoration-none">Medicines</a></li>
          <li><a href="#" class="text-white text-decoration-none">Deals & Offers</a></li>
          <li><a href="#" class="text-white text-decoration-none">New Products</a></li>
        </ul>
      </div>

      <div class="col-md-3 mb-3">
        <h5>Company</h5>
        <ul class="list-unstyled">
          <li><a href="#" class="text-white text-decoration-none">About Us</a></li>
          <li><a href="#" class="text-white text-decoration-none">Careers</a></li>
          <li><a href="contact.php" class="text-white text-decoration-none">Contact Us</a></li>
          <li><a href="#" class="text-white text-decoration-none">FAQ</a></li>
        </ul>
      </div>

      <div class="col-md-3 mb-3">
        <h5>Subscribe</h5>
        <p>Stay updated with our latest offers and products.</p>
        <div class="d-flex flex-column flex-sm-row w-100 gap-2">
          <label for="newsletter1" class="visually-hidden">Email address</label>
          <input id="newsletter1" type="text" class="form-control" placeholder="Email address">
          <button class="btn btn-secondary" type="button">Subscribe</button>
        </div>
      </div>
    </div>

    <div class="d-flex flex-column flex-sm-row justify-content-between py-2 mt-2 border-top">
      <p>&copy; 2023 Pharma, Inc. All rights reserved.</p>
      <ul class="list-unstyled d-flex">
        <li class="ms-3"><a class="link-light" href="#"><svg class="bi" width="24" height="24"><use xlink:href="#twitter"/></svg></a></li>
        <li class="ms-3"><a class="link-light" href="#"><svg class="bi" width="24" height="24"><use xlink:href="#instagram"/></svg></a></li>
        <li class="ms-3"><a class="link-light" href="#"><svg class="bi" width="24" height="24"><use xlink:href="#facebook"/></svg></a></li>
      </ul>
    </div>
  </div>
</footer>

<!-- JavaScript -->
<script>
  // Document Ready
  document.addEventListener('DOMContentLoaded', function() {
    // Load cart from localStorage
    let cart = JSON.parse(localStorage.getItem('PharmaCart')) || [];
    updateOrderSummary(cart);
    
    // Load saved pincode if exists
    const savedPincode = localStorage.getItem('PharmaPincode');
    if (savedPincode) {
      document.querySelector('.current-pincode').textContent = savedPincode;
    }
    
    // Setup event listeners
    setupEventListeners();
    
    // Setup search functionality
    setupSearchFunctionality();
  });
  
  // Function to update pincode
  function updatePincode() {
    const pincodeInput = document.querySelector('.pincode-input');
    const pincodeMessage = document.getElementById('pincodeMessage');
    
    if (pincodeInput.value.length !== 6 || !/^\d+$/.test(pincodeInput.value)) {
      pincodeMessage.innerHTML = '<div class="alert alert-danger">Please enter a valid 6-digit pincode</div>';
      return;
    }
    
    // Save to localStorage
    localStorage.setItem('PharmaPincode', pincodeInput.value);
    
    // Update display
    document.querySelector('.current-pincode').textContent = pincodeInput.value;
    
    // Show success message
    pincodeMessage.innerHTML = '<div class="alert alert-success">Delivery available to this pincode!</div>';
    
    // Close the modal after 1.5 seconds
    setTimeout(() => {
      const modal = bootstrap.Modal.getInstance(document.getElementById('pincodeModal'));
      modal.hide();
    }, 1500);
  }
  
  // Function to update order summary
  function updateOrderSummary(cart) {
    const orderItemsContainer = document.getElementById('orderItemsContainer');
    const subtotalElement = document.getElementById('subtotal');
    const totalElement = document.getElementById('total');
    const discountElement = document.getElementById('discount');
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    
    if (cart.length === 0) {
      orderItemsContainer.innerHTML = `
        <div class="alert alert-warning">
          Your cart is empty. Please add items to your cart before checkout.
        </div>
      `;
      subtotalElement.textContent = '₹0';
      totalElement.textContent = '₹49';
      discountElement.textContent = '-₹0';
      placeOrderBtn.disabled = true;
      return;
    }
    
    // Calculate totals
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const discount = cart.reduce((sum, item) => {
      const originalPrice = item.originalPrice || (item.price * 1.2); // If originalPrice not set, estimate as 20% higher
      return sum + (originalPrice - item.price) * item.quantity;
    }, 0);
    const total = subtotal + 49; // Adding delivery charge
    
    // Update summary
    subtotalElement.textContent = `₹${subtotal.toFixed(2)}`;
    discountElement.textContent = `-₹${discount.toFixed(2)}`;
    totalElement.textContent = `₹${total.toFixed(2)}`;
    placeOrderBtn.disabled = false;
    
    // Generate order items HTML
    let orderItemsHTML = '';
    cart.forEach(item => {
      orderItemsHTML += `
        <div class="order-item">
          <div>
            <h6>${item.name} × ${item.quantity}</h6>
            <p class="text-muted small">${item.description || `${item.name} medicine`}</p>
          </div>
          <div class="text-end">
            <p class="fw-bold">₹${(item.price * item.quantity).toFixed(2)}</p>
          </div>
        </div>
      `;
    });
    
    orderItemsContainer.innerHTML = orderItemsHTML;
  }
  
  // Function to setup event listeners
  function setupEventListeners() {
    // Billing Address Form Submission
    const billingAddressForm = document.getElementById('billingAddressForm');
    if (billingAddressForm) {
      billingAddressForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form values
        const fullName = document.getElementById('fullName').value;
        const phoneNumber = document.getElementById('phoneNumber').value;
        const addressLine1 = document.getElementById('addressLine1').value;
        const addressLine2 = document.getElementById('addressLine2').value;
        const city = document.getElementById('city').value;
        const state = document.getElementById('state').value;
        const pincode = document.getElementById('pincode').value;
        
        // Format address
        const formattedAddress = `${addressLine1}${addressLine2 ? ', ' + addressLine2 : ''}, ${city}, ${state} - ${pincode}`;
        
        // Update delivery address display
        document.getElementById('deliveryName').textContent = fullName;
        document.getElementById('deliveryAddress').textContent = formattedAddress;
        document.getElementById('deliveryPhone').textContent = `Phone: ${phoneNumber}`;
        
        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('billingAddressModal'));
        modal.hide();
      });
    }
    
    // Payment Method Selection
    const paymentMethods = document.querySelectorAll('.payment-method');
    paymentMethods.forEach(method => {
      method.addEventListener('click', function() {
        // Remove selected class from all methods
        paymentMethods.forEach(m => m.classList.remove('selected'));
        
        // Add selected class to clicked method
        this.classList.add('selected');
        
        // Check the radio button
        const radio = this.querySelector('input[type="radio"]');
        radio.checked = true;
      });
    });
    
    // Place Order Button
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    if (placeOrderBtn) {
      placeOrderBtn.addEventListener('click', function() {
        // Get cart data
        const cart = JSON.parse(localStorage.getItem('PharmaCart')) || [];
        
        if (cart.length === 0) {
          alert('Your cart is empty. Please add items to your cart before placing an order.');
          return;
        }
        
        // Calculate total amount
        const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const total = subtotal + 49; // Adding delivery charge
        
        // Get order ID from the page
        const orderId = '<?php echo $order_id; ?>';
        
        // Save payment to database via AJAX
        savePaymentToDatabase(total, orderId);
        
        // Store order data in sessionStorage for the bill page
        sessionStorage.setItem('orderData', JSON.stringify({
          cart: cart,
          orderId: orderId,
          customerName: document.getElementById('deliveryName').textContent,
          customerAddress: document.getElementById('deliveryAddress').textContent,
          customerPhone: document.getElementById('deliveryPhone').textContent,
          paymentMethod: document.querySelector('input[name="paymentMethod"]:checked').value
        }));
        
        // Clear cart after order is placed
        localStorage.removeItem('PharmaCart');
        
        // Redirect to bill page
        window.location.href = 'bill.php';
      });
    }
  }
  
  // Function to save payment to database via AJAX
  function savePaymentToDatabase(amount, orderId) {
    // Create form data
    const formData = new FormData();
    formData.append('amount', amount);
    formData.append('order_id', orderId);
    
    // Send AJAX request
    fetch('save-payment.php', {
      method: 'POST',
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        console.log('Payment saved successfully');
      } else {
        console.error('Error saving payment:', data.error);
      }
    })
    .catch(error => {
      console.error('Error saving payment:', error);
    });
  }
  
  // Setup search functionality for the navbar search bar
  function setupSearchFunctionality() {
    // Navbar search functionality
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    const searchForm = document.getElementById('searchForm');
    
    // Make sure searchResults is properly initialized
    if (searchResults) {
      searchResults.style.display = 'none';
    }
    
    // Debounce function
    function debounce(func, timeout = 300) {
      let timer;
      return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => { func.apply(this, args); }, timeout);
      };
    }
    
    if (searchInput) {
      searchInput.addEventListener('input', debounce(async function() {
        const query = this.value.trim();
        
        if (query.length < 2) {
          searchResults.style.display = 'none';
          return;
        }
        
        try {
          const response = await fetch(`search.php?query=${encodeURIComponent(query)}`);
          const data = await response.json();
          
          if (data.error) {
            console.error(data.error);
            searchResults.innerHTML = '<div class="no-results">Error fetching results</div>';
            searchResults.style.display = 'block';
            return;
          }
          
          // Display results
          if (data.results && data.results.length > 0) {
            let resultsHTML = '';
            data.results.forEach((medicine, index) => {
              resultsHTML += `
                <div class="search-result-item" data-id="${index}" data-name="${medicine.name}" data-price="${medicine.price}">
                  <div>${medicine.name}</div>
                  <div>₹${parseFloat(medicine.price).toFixed(2)}</div>
                </div>
              `;
            });
            searchResults.innerHTML = resultsHTML;
            searchResults.style.display = 'block';
          } else {
            searchResults.innerHTML = '<div class="no-results">No medicines found</div>';
            searchResults.style.display = 'block';
          }
        } catch (error) {
          console.error('Error fetching search results:', error);
          searchResults.innerHTML = '<div class="no-results">Error fetching results</div>';
          searchResults.style.display = 'block';
        }
      }, 300));
    }
    
    // Handle search result clicks
    if (searchResults) {
      searchResults.addEventListener('click', function(e) {
        const resultItem = e.target.closest('.search-result-item');
        if (resultItem) {
          const medicine = {
            id: resultItem.dataset.id,
            name: resultItem.dataset.name,
            price: parseFloat(resultItem.dataset.price),
            originalPrice: parseFloat(resultItem.dataset.price) * 1.2, // Estimate original price as 20% higher
            image: 'placeholder.jpg',
            description: `${resultItem.dataset.name} medicine`
          };
          
          // Add to cart directly
          addToCart(medicine);
          searchResults.style.display = 'none';
          searchInput.value = '';
        }
      });
    }
    
    // Handle search form submission
    if (searchForm) {
      searchForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const query = searchInput.value.trim();
        
        if (query.length < 2) return;
        
        try {
          const response = await fetch(`search.php?query=${encodeURIComponent(query)}`);
          const data = await response.json();
          
          if (data.error) {
            console.error(data.error);
            alert('Error searching for medicines');
            return;
          }
          
          if (data.results && data.results.length > 0) {
            // Add first result to cart
            const medicine = data.results[0];
            addToCart({
              id: Date.now().toString(), // Generate a unique ID
              name: medicine.name,
              price: parseFloat(medicine.price),
              originalPrice: parseFloat(medicine.price) * 1.2,
              image: 'placeholder.jpg',
              description: `${medicine.name} medicine`
            });
            searchInput.value = '';
          } else {
            alert('No matching medicine found');
          }
        } catch (error) {
          console.error('Error fetching search results:', error);
          alert('Error searching for medicines');
        }
      });
    }
    
    // Hide results when clicking outside
    document.addEventListener('click', function(e) {
      if (searchForm && searchResults && !searchForm.contains(e.target)) {
        searchResults.style.display = 'none';
      }
    });
  }
  
  // Function to add item to cart
  function addToCart(product) {
    let cart = JSON.parse(localStorage.getItem('PharmaCart')) || [];
    
    // Check if product already in cart
    const existingItem = cart.find(item => item.id === product.id);
    
    if (existingItem) {
      existingItem.quantity += 1;
    } else {
      cart.push({
        ...product,
        quantity: 1
      });
    }
    
    localStorage.setItem('PharmaCart', JSON.stringify(cart));
    updateOrderSummary(cart);
    
    // Show success message
    alert(`${product.name} added to cart!`);
  }
</script>
</body>
</html>
