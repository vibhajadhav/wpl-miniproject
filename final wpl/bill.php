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

// Database connection details - matching your search.php file
$db_host = 'localhost';
$db_name = 'pharmacy';
$db_user = 'root';
$db_pass = '';

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Function to get company details (for invoice header)
    function getCompanyDetails() {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM company LIMIT 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    $company = getCompanyDetails();
    
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
  <title>Order Confirmation</title>
  <link rel="stylesheet" href="styles.css" />
  <link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
  />
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
    defer
  ></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

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
    
    .invoice-container {
      background-color: white;
      padding: 30px;
      border: 1px solid #ddd;
      border-radius: 8px;
      margin-top: 20px;
    }
    
    .invoice-header {
      display: flex;
      justify-content: space-between;
      margin-bottom: 30px;
    }
    
    .invoice-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }
    
    .invoice-table th, .invoice-table td {
      padding: 10px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    
    .invoice-table th {
      background-color: #f8f9fa;
    }
    
    .invoice-total {
      text-align: right;
      margin-top: 20px;
    }
    
    .order-success {
      background-color: #d4edda;
      color: #155724;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      text-align: center;
    }
    
    @media print {
      .no-print {
        display: none;
      }
      body {
        padding: 0;
        margin: 0;
      }
      .invoice-container {
        border: none;
        padding: 0;
      }
    }
  </style>
</head>
<body>
<!-- Updated Navigation Bar with Dropdown -->
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand me-4" href="index.php">
                <img src="logo.png" alt="Logo" height="42">
            </a>
            
            <!-- Navbar toggler button for mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Collapsible navbar content -->
            <div class="collapse navbar-collapse" id="navbarContent">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center w-100">
                    <div class="delivery-location me-lg-4" data-bs-toggle="modal" data-bs-target="#pincodeModal">
                        <div style="font-size: 13px;">Express delivery to</div>
                        <b class="current-pincode d-block">Select Pincode</b>
                    </div>
                    
                    <!-- Search Bar with adjusted proportions -->
                    <div class="search-container mx-lg-4">
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
                    
                    <div class="nav-icons ms-lg-auto">
                        <a href="cart.php" class="cart d-flex align-items-center">
                            <span style="font-size: 18px; margin-right: 5px;">🛒</span> Cart
                        </a>
                        
                        <!-- Contact Us Dropdown -->
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center dropdown-toggle" id="contactDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <span style="font-size: 16px; margin-right: 5px;">📞</span> Contact Us
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="contactDropdown">
                                <li><a class="dropdown-item" href="contact.html">Send Message</a></li>
                                <li><a class="dropdown-item" href="tel:+1234567890">Call Us</a></li>
                                <li><a class="dropdown-item" href="mailto:support@pharma.com">Email Us</a></li>
                            </ul>
                        </div>
                        
                        <!-- User Account Dropdown -->
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

<!-- Bill Content -->
<div class="container my-5">
  <div id="orderSuccessMessage" class="order-success mb-4">
    <h4>Order Placed Successfully!</h4>
    <p>Your order has been placed successfully. Order ID: <strong id="successOrderId"></strong></p>
  </div>
  
  <div class="d-flex justify-content-between mb-3 no-print">
    <h3>Order Confirmation</h3>
    <div>
      <button id="printInvoiceBtn" class="btn btn-outline-primary me-2">
        <i class="bi bi-printer"></i> Print Invoice
      </button>
      <button id="downloadPdfBtn" class="btn btn-primary">
        <i class="bi bi-file-earmark-pdf"></i> Download PDF
      </button>
    </div>
  </div>
  
  <div id="invoiceContainer" class="invoice-container">
    <div class="invoice-header">
      <div>
        <h4>INVOICE</h4>
        <p>Order #: <strong id="invoiceOrderId"></strong></p>
        <p>Date: <span id="invoiceDate"><?php echo date('d M Y'); ?></span></p>
      </div>
      <div>
        <h5><?php echo $company['company']; ?></h5>
        <p><?php echo $company['c_address']; ?></p>
        <p>GST: <?php echo $company['gstno']; ?></p>
      </div>
    </div>
    
    <div class="row mb-4">
      <div class="col-md-6">
        <h6>Bill To:</h6>
        <p id="invoiceBillingName"></p>
        <p id="invoiceBillingAddress"></p>
        <p id="invoiceBillingPhone"></p>
      </div>
      <div class="col-md-6">
        <h6>Ship To:</h6>
        <p id="invoiceShippingName"></p>
        <p id="invoiceShippingAddress"></p>
        <p id="invoiceShippingPhone"></p>
      </div>
    </div>
    
    <table class="invoice-table">
      <thead>
        <tr>
          <th>Item</th>
          <th>Quantity</th>
          <th>Unit Price</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody id="invoiceItems">
        <!-- Invoice items will be inserted here -->
      </tbody>
    </table>
    
    <div class="invoice-total">
      <div class="row">
        <div class="col-md-6"></div>
        <div class="col-md-6">
          <div class="d-flex justify-content-between mb-2">
            <span>Subtotal:</span>
            <span id="invoiceSubtotal">₹0</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span>Delivery:</span>
            <span id="invoiceDelivery">₹49</span>
          </div>
          <div class="d-flex justify-content-between mb-2">
            <span>Discount:</span>
            <span id="invoiceDiscount" class="text-danger">-₹0</span>
          </div>
          <div class="d-flex justify-content-between fw-bold">
            <span>Total:</span>
            <span id="invoiceTotal">₹49</span>
          </div>
        </div>
      </div>
    </div>
    
    <div class="mt-5 text-center">
      <p>Thank you for your order!</p>
    </div>
  </div>
  
  <div class="d-flex justify-content-center mt-4 no-print">
    <a href="index.php" class="btn btn-success btn-lg">
      Continue Shopping
    </a>
  </div>
</div>

<!-- JavaScript -->
<script>
  // Document Ready
  document.addEventListener('DOMContentLoaded', function() {
    // Get order data from sessionStorage
    const orderData = JSON.parse(sessionStorage.getItem('orderData'));
    
    if (!orderData) {
      // If no order data, redirect to cart page
      window.location.href = 'cart.php';
      return;
    }
    
    // Generate invoice
    generateInvoice(orderData);
    
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
  
  // Function to setup event listeners
  function setupEventListeners() {
    // Print Invoice Button
    const printInvoiceBtn = document.getElementById('printInvoiceBtn');
    if (printInvoiceBtn) {
      printInvoiceBtn.addEventListener('click', function() {
        window.print();
      });
    }
    
    // Download PDF Button
    const downloadPdfBtn = document.getElementById('downloadPdfBtn');
    if (downloadPdfBtn) {
      downloadPdfBtn.addEventListener('click', function() {
        generatePDF();
      });
    }
  }
  
  // Function to generate invoice
  function generateInvoice(orderData) {
    // Update order ID in success message and invoice
    document.getElementById('successOrderId').textContent = orderData.orderId;
    document.getElementById('invoiceOrderId').textContent = orderData.orderId;
    
    // Update billing and shipping information
    document.getElementById('invoiceBillingName').textContent = orderData.customerName;
    document.getElementById('invoiceBillingAddress').textContent = orderData.customerAddress;
    document.getElementById('invoiceBillingPhone').textContent = orderData.customerPhone;
    
    document.getElementById('invoiceShippingName').textContent = orderData.customerName;
    document.getElementById('invoiceShippingAddress').textContent = orderData.customerAddress;
    document.getElementById('invoiceShippingPhone').textContent = orderData.customerPhone;
    
    // Get invoice elements
    const invoiceItems = document.getElementById('invoiceItems');
    const invoiceSubtotal = document.getElementById('invoiceSubtotal');
    const invoiceDiscount = document.getElementById('invoiceDiscount');
    const invoiceTotal = document.getElementById('invoiceTotal');
    
    // Calculate totals
    const subtotal = orderData.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const discount = orderData.cart.reduce((sum, item) => {
      const originalPrice = item.originalPrice || (item.price * 1.2);
      return sum + (originalPrice - item.price) * item.quantity;
    }, 0);
    const total = subtotal + 49; // Adding delivery charge
    
    // Update invoice summary
    invoiceSubtotal.textContent = `₹${subtotal.toFixed(2)}`;
    invoiceDiscount.textContent = `-₹${discount.toFixed(2)}`;
    invoiceTotal.textContent = `₹${total.toFixed(2)}`;
    
    // Generate invoice items HTML
    let invoiceItemsHTML = '';
    orderData.cart.forEach(item => {
      invoiceItemsHTML += `
        <tr>
          <td>${item.name}</td>
          <td>${item.quantity}</td>
          <td>₹${item.price.toFixed(2)}</td>
          <td>₹${(item.price * item.quantity).toFixed(2)}</td>
        </tr>
      `;
    });
    
    invoiceItems.innerHTML = invoiceItemsHTML;
  }
  
  // Function to generate PDF
  function generatePDF() {
    // Initialize jsPDF
    const { jsPDF } = window.jspdf;
    
    // Get the invoice container
    const invoiceContainer = document.getElementById('invoiceContainer');
    
    // Create a new PDF document
    const doc = new jsPDF('p', 'pt', 'a4');
    
    // Use html2canvas to capture the invoice as an image
    html2canvas(invoiceContainer).then(canvas => {
      const imgData = canvas.toDataURL('image/png');
      const imgWidth = doc.internal.pageSize.getWidth();
      const imgHeight = (canvas.height * imgWidth) / canvas.width;
      
      doc.addImage(imgData, 'PNG', 0, 0, imgWidth, imgHeight);
      doc.save('pharmacy-invoice.pdf');
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
