<?php
// Start session to manage user data
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
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

    // Function to get customer orders
    function getCustomerOrders($username) {
        global $pdo;
        $stmt = $pdo->prepare("
            SELECT p.order_id, p.amount, c.username, c.address, c.phoneno
            FROM payment p
            JOIN customer c ON c.username = :username
            ORDER BY p.order_id DESC
            LIMIT 10
        ");
        $stmt->execute(['username' => $username]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch data
    $customer = getCustomerDetails($username);
    $orders = getCustomerOrders($username);

    // Handle individual order deletion
    if (isset($_POST['delete_order']) && isset($_POST['order_id'])) {
        $order_id = $_POST['order_id'];
        $stmt = $pdo->prepare("DELETE FROM payment WHERE order_id = :order_id");
        $stmt->execute(['order_id' => $order_id]);
        header("Location: orders.php?deleted=true");
        exit;
    }

    // Handle bulk order deletion
    if (isset($_POST['delete_all_orders'])) {
        $stmt = $pdo->prepare("
            DELETE FROM payment
            WHERE order_id IN (
                SELECT p.order_id
                FROM payment p
                JOIN customer c ON c.username = :username
            )
        ");
        $stmt->execute(['username' => $username]);
        header("Location: orders.php?deleted_all=true");
        exit;
    }

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Your Orders</title>
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
    
    .order-card {
      border: 1px solid #eee;
      border-radius: 8px;
      margin-bottom: 20px;
      overflow: hidden;
    }
    
    .order-header {
      background-color: #f8f9fa;
      padding: 15px;
      border-bottom: 1px solid #eee;
    }
    
    .order-body {
      padding: 15px;
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
      margin-bottom: 0;
      padding-bottom: 0;
    }
    
    .order-footer {
      background-color: #f8f9fa;
      padding: 15px;
      border-top: 1px solid #eee;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .status-badge {
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: bold;
    }
    
    .status-delivered {
      background-color: #d4edda;
      color: #155724;
    }
    
    .status-processing {
      background-color: #fff3cd;
      color: #856404;
    }
    
    .status-shipped {
      background-color: #cce5ff;
      color: #004085;
    }
    
    .empty-orders {
      text-align: center;
      padding: 50px 20px;
      background-color: #f8f9fa;
      border-radius: 8px;
      margin-top: 20px;
    }
    
    .delete-order-btn {
      color: #dc3545;
      cursor: pointer;
    }
    
    .delete-order-btn:hover {
      text-decoration: underline;
    }
    
    .alert-success {
      margin-bottom: 20px;
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
      <form class="d-flex" id="searchForm" action="medicine-list.php" method="get">
        <input
          class="form-control"
          type="search"
          id="searchInput"
          name="search"
          placeholder="Search for medicines..."
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
        <input type="text" class="pincode-input" placeholder="Enter 6-digit pincode" maxlength="6" pattern="\d{6}" required>
        <button class="pincode-submit" onclick="updatePincode()">Check & Update</button>
        <div class="mt-3" id="pincodeMessage"></div>
      </div>
    </div>
  </div>
</div>

<!-- Delete Order Confirmation Modal -->
<div class="modal fade" id="deleteOrderModal" tabindex="-1" aria-labelledby="deleteOrderModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteOrderModalLabel">Confirm Deletion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete this order? This action cannot be undone.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form id="deleteOrderForm" method="post" action="orders.php">
          <input type="hidden" name="order_id" id="deleteOrderId" value="">
          <input type="hidden" name="delete_order" value="1">
          <button type="submit" class="btn btn-danger">Delete Order</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Delete All Orders Confirmation Modal -->
<div class="modal fade" id="deleteAllOrdersModal" tabindex="-1" aria-labelledby="deleteAllOrdersModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteAllOrdersModalLabel">Confirm Deletion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to delete all your orders? This action cannot be undone.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form method="post" action="orders.php">
          <input type="hidden" name="delete_all_orders" value="1">
          <button type="submit" class="btn btn-danger">Delete All Orders</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Orders Content -->
<div class="container my-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Your Orders</h2>
    <!-- Delete All Orders button removed from here -->
  </div>
  
  <?php
  // Show success messages
  if (isset($_GET['deleted']) && $_GET['deleted'] == 'true') {
    echo '<div class="alert alert-success">Order has been successfully deleted.</div>';
  }
  if (isset($_GET['deleted_all']) && $_GET['deleted_all'] == 'true') {
    echo '<div class="alert alert-success">All orders have been successfully deleted.</div>';
  }
  
  if (!isset($_SESSION['username'])) {
    // Show login message if not logged in
    echo '<div class="empty-orders">
            <h3>Please log in to view your orders</h3>
            <p class="text-muted">You need to be logged in to view your order history.</p>
            <a href="login.php" class="btn btn-primary mt-3">Login</a>
          </div>';
  } else {
    // Display orders
    if (count($orders) > 0) {
      foreach ($orders as $order) {
        // Determine random status for demo purposes
        $statuses = ['Delivered', 'Shipped', 'Processing'];
        $status = $statuses[array_rand($statuses)];
        
        // Determine status badge class
        $statusClass = '';
        switch ($status) {
          case 'Delivered':
            $statusClass = 'status-delivered';
            break;
          case 'Shipped':
            $statusClass = 'status-shipped';
            break;
          case 'Processing':
            $statusClass = 'status-processing';
            break;
          default:
            $statusClass = '';
        }
        
        // Format date (using current date for demo)
        $orderDate = date('F j, Y', strtotime('-' . rand(1, 30) . ' days'));
        
        echo '<div class="order-card">
                <div class="order-header d-flex justify-content-between align-items-center">
                  <div>
                    <h5 class="mb-0">Order #' . $order['order_id'] . '</h5>
                    <small class="text-muted">Placed on ' . $orderDate . '</small>
                  </div>
                  <span class="status-badge ' . $statusClass . '">' . $status . '</span>
                </div>
                <div class="order-body">
                  <h6>Order Details</h6>
                  <div class="order-item">
                    <div>
                      <p class="mb-0"><strong>Total Amount:</strong></p>
                    </div>
                    <div>
                      <p class="mb-0">₹' . number_format($order['amount'], 2) . '</p>
                    </div>
                  </div>
                  <div class="order-item">
                    <div>
                      <p class="mb-0"><strong>Shipping Address:</strong></p>
                    </div>
                    <div>
                      <p class="mb-0">' . $order['address'] . '</p>
                    </div>
                  </div>
                  <div class="order-item">
                    <div>
                      <p class="mb-0"><strong>Contact:</strong></p>
                    </div>
                    <div>
                      <p class="mb-0">' . $order['phoneno'] . '</p>
                    </div>
                  </div>
                </div>
                <div class="order-footer">
                  <div>
                    <strong>Total: ₹' . number_format($order['amount'], 2) . '</strong>
                  </div>
                  <div>
                    <a href="#" class="btn btn-sm btn-outline-primary me-2">Track Order</a>
                    <a href="#" class="btn btn-sm btn-primary me-2">View Details</a>
                    <a href="#" class="delete-order-btn" data-order-id="' . $order['order_id'] . '" data-bs-toggle="modal" data-bs-target="#deleteOrderModal">Delete Order</a>
                  </div>
                </div>
              </div>';
      }
    } else {
      // No orders found
      echo '<div class="empty-orders">
              <h3>No orders found</h3>
              <p class="text-muted">You haven\'t placed any orders yet.</p>
              <a href="medicine-list.php" class="btn btn-primary mt-3">Start Shopping</a>
            </div>';
    }
  }
  ?>
</div>

<!-- Footer -->
<footer class="bg-dark text-white py-4">
  <div class="container">
    <div class="row">
      <!-- Company Info -->
      <div class="col-md-3 mb-3">
        <h5>Company</h5>
        <ul class="list-unstyled">
          <li><a href="#" class="text-white text-decoration-none">About Us</a></li>
          <li><a href="#" class="text-white text-decoration-none">Careers</a></li>
          <li><a href="contact.php" class="text-white text-decoration-none">Contact Us</a></li>
          <li><a href="#" class="text-white text-decoration-none">FAQ</a></li>
        </ul>
      </div>
      <!-- Featured Categories -->
      <div class="col-md-3 mb-3">
        <h5>Featured Categories</h5>
        <ul class="list-unstyled">
          <li><a href="#" class="text-white text-decoration-none">Medicine</a></li>
          <li><a href="#" class="text-white text-decoration-none">Vitamins</a></li>
          <li><a href="#" class="text-white text-decoration-none">Skincare</a></li>
          <li><a href="#" class="text-white text-decoration-none">Healthcare</a></li>
        </ul>
      </div>
      <!-- Policy Info -->
      <div class="col-md-3 mb-3">
        <h5>Policy Info</h5>
        <ul class="list-unstyled">
          <li><a href="#" class="text-white text-decoration-none">Return Policy</a></li>
          <li><a href="#" class="text-white text-decoration-none">Privacy Policy</a></li>
          <li><a href="#" class="text-white text-decoration-none">Terms of Use</a></li>
          <li><a href="#" class="text-white text-decoration-none">Security</a></li>
        </ul>
      </div>
      <!-- Payment Partners / Social -->
      <div class="col-md-3 mb-3">
        <h5>Our Payment Partners</h5>
        <div class="d-flex flex-wrap mb-2">
          <img src="payment-visa.png" alt="Visa" class="me-2 mb-2" height="30" />
          <img src="payment-mastercard.png" alt="Mastercard" class="me-2 mb-2" height="30" />
          <img src="payment-paypal.png" alt="PayPal" class="me-2 mb-2" height="30" />
        </div>
        <h5>Follow us on</h5>
        <div>
          <a href="#" class="text-white me-3">Facebook</a>
          <a href="#" class="text-white me-3">Twitter</a>
          <a href="#" class="text-white">Instagram</a>
        </div>
      </div>
    </div>
    <hr class="bg-light" />
    <div class="text-center">
      <p class="mb-0">&copy; 2025 Pharma. All Rights Reserved.</p>
    </div>
  </div>
</footer>

<!-- JavaScript -->
<script>
  // Document Ready
  document.addEventListener('DOMContentLoaded', function() {
    // Load saved pincode if exists
    const savedPincode = localStorage.getItem('PharmaPincode');
    if (savedPincode) {
      document.querySelector('.current-pincode').textContent = savedPincode;
    }
    
    // Setup search functionality
    setupSearchFunctionality();
    
    // Setup delete order functionality
    setupDeleteOrderFunctionality();
  });
  
  // Function to update pincode
  function updatePincode() {
    const pincodeInput = document.querySelector('.pincode-input');
    const pincodeMessage = document.getElementById('pincodeMessage');
    
    if (pincodeInput.value.length === 6 && /^\d+$/.test(pincodeInput.value)) {
      // Save to localStorage
      localStorage.setItem('PharmaPincode', pincodeInput.value);
      
      // Update display
      document.querySelector('.current-pincode').textContent = pincodeInput.value;
      
      // Show success message
      pincodeMessage.innerHTML = '<div class="alert alert-success">Delivery available in your area!</div>';
      
      // Close the modal after 1.5 seconds
      setTimeout(() => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('pincodeModal'));
        modal.hide();
      }, 1500);
    } else {
      pincodeMessage.innerHTML = '<div class="alert alert-danger">Please enter a valid 6-digit pincode</div>';
    }
  }
  
  // Setup delete order functionality
  function setupDeleteOrderFunctionality() {
    // Get all delete order buttons
    const deleteOrderBtns = document.querySelectorAll('.delete-order-btn');
    
    // Add click event to each button
    deleteOrderBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        // Get order ID from data attribute
        const orderId = this.getAttribute('data-order-id');
        
        // Set order ID in hidden input
        document.getElementById('deleteOrderId').value = orderId;
      });
    });
  }
  
  // Setup search functionality
  function setupSearchFunctionality() {
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    const searchForm = document.getElementById('searchForm');
    
    // Debounce function
    function debounce(func, timeout = 300) {
      let timer;
      return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => { func.apply(this, args); }, timeout);
      };
    }
    
    // Process search with debounce
    const processSearch = debounce(async (searchTerm) => {
      if (searchTerm.length < 2) {
        searchResults.style.display = 'none';
        return;
      }
      
      try {
        const response = await fetch(`search.php?query=${encodeURIComponent(searchTerm)}`);
        const data = await response.json();
        
        if (data.error) {
          console.error(data.error);
          searchResults.innerHTML = '<div class="search-result-item">Error fetching results</div>';
          searchResults.style.display = 'block';
          return;
        }
        
        // Display results
        if (data.results && data.results.length > 0) {
          let resultsHTML = '';
          data.results.forEach(medicine => {
            resultsHTML += `
              <div class="search-result-item">
                <div>${medicine.name}</div>
                <div>₹${parseFloat(medicine.price).toFixed(2)}</div>
              </div>
            `;
          });
          searchResults.innerHTML = resultsHTML;
          searchResults.style.display = 'block';
        } else {
          searchResults.innerHTML = '<div class="search-result-item">No medicines found</div>';
          searchResults.style.display = 'block';
        }
      } catch (error) {
        console.error('Error fetching search results:', error);
        searchResults.innerHTML = '<div class="search-result-item">Error fetching results</div>';
        searchResults.style.display = 'block';
      }
    });
    
    // Search input event
    if (searchInput) {
      searchInput.addEventListener('input', function() {
        processSearch(this.value.trim());
      });
    }
    
    // Hide results when clicking outside
    document.addEventListener('click', function(e) {
      if (searchForm && !searchForm.contains(e.target)) {
        searchResults.style.display = 'none';
      }
    });
    
    // Search form submission
    if (searchForm) {
      searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const searchTerm = searchInput.value.trim();
        if (searchTerm.length > 0) {
          window.location.href = `medicine-list.php?search=${encodeURIComponent(searchTerm)}`;
        }
      });
    }
  }
</script>
</body>
</html>
