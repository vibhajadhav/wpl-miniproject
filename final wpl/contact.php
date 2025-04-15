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
  <title>Contact Us - Pharma</title>
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
    
    /* Contact form specific styles */
    .contact-form-container {
      max-width: 800px;
      margin: 0 auto;
      padding: 30px;
      background-color: #fff;
      border-radius: 10px;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    
    .contact-info {
      background-color: #f8f9fa;
      padding: 20px;
      border-radius: 8px;
      margin-bottom: 30px;
    }
    
    .contact-info-item {
      display: flex;
      align-items: flex-start;
      margin-bottom: 15px;
    }
    
    .contact-info-item i {
      margin-right: 15px;
      font-size: 20px;
      color: #0d6efd;
    }
    
    .contact-form label {
      font-weight: 500;
    }
    
    .contact-form .form-control {
      padding: 12px;
      border-radius: 5px;
      margin-bottom: 20px;
    }
    
    .contact-form textarea {
      min-height: 150px;
    }
    
    .contact-form .btn-primary {
      padding: 12px 30px;
      font-weight: 500;
    }
  </style>
</head>
<body>
<!-- Updated Navigation Bar with Contact Us Dropdown -->
<nav class="navbar navbar-expand-lg bg-white shadow-sm">
  <div class="container">
    <a class="navbar-brand me-4" href="index.php">
      <img src="logo.png" alt="Logo" height="42" />
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
              <li><a class="dropdown-item active" href="contact.html">Send Message</a></li>
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
        <div class="input-group mb-3">
          <input type="text" class="form-control pincode-input" placeholder="Enter 6-digit pincode" maxlength="6" pattern="\d{6}" required>
          <button class="btn btn-primary pincode-submit" type="button" onclick="updatePincode()">Check & Update</button>
        </div>
        <div class="mt-3" id="pincodeMessage"></div>
      </div>
    </div>
  </div>
</div>

<!-- Contact Us Content -->
<div class="container my-5">
  <div class="contact-form-container">
    <h1 class="text-center mb-4">Contact Us</h1>
    <p class="text-center text-muted mb-5">Have questions or feedback? We'd love to hear from you!</p>
    
    <div class="contact-info">
      <div class="row">
        <div class="col-md-4">
          <div class="contact-info-item">
            <i class="bi bi-geo-alt"></i>
            <div>
              <h5>Our Address</h5>
              <p>123 Pharmacy Street, Medical District, City - 123456</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="contact-info-item">
            <i class="bi bi-telephone"></i>
            <div>
              <h5>Phone Number</h5>
              <p>+1 (234) 567-8900</p>
              <p>+1 (234) 567-8901</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="contact-info-item">
            <i class="bi bi-envelope"></i>
            <div>
              <h5>Email Address</h5>
              <p>support@pharma.com</p>
              <p>info@pharma.com</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="contact-form">
      <h3 class="mb-4">Send us a message</h3>
      <form action="contact.php" method="post">
        <div class="row">
          <div class="col-md-6">
            <div class="mb-3">
              <label for="name" class="form-label">Your Name</label>
              <input type="text" class="form-control" id="name" name="name" required>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-3">
              <label for="email" class="form-label">Your Email</label>
              <input type="email" class="form-control" id="email" name="email" required>
            </div>
          </div>
        </div>
        <div class="mb-3">
          <label for="message" class="form-label">Your Message</label>
          <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
        </div>
        <div class="text-center">
          <button type="submit" class="btn btn-primary">Send Message</button>
        </div>
      </form>
    </div>
  </div>
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
          <li><a href="contact.html" class="text-white text-decoration-none">Contact Us</a></li>
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

<!-- JavaScript for Pincode Functionality -->
<script>
// Search functionality
const searchInput = document.getElementById('searchInput');
const searchResults = document.getElementById('searchResults');
const searchForm = document.getElementById('searchForm');

function debounce(func, timeout = 300) {
  let timer;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => { func.apply(this, args); }, timeout);
  };
}

async function fetchSearchResults(query) {
  if (query.length < 2) {
    searchResults.style.display = 'none';
    return [];
  }
  
  try {
    const response = await fetch(`search.php?query=${encodeURIComponent(query)}`);
    const data = await response.json();
    return data.results || [];
  } catch (error) {
    console.error('Error fetching search results:', error);
    return [];
  }
}

function displayResults(results) {
  searchResults.innerHTML = '';
  if (results.length === 0) {
    searchResults.innerHTML = '<div class="search-result-item">No medicines found</div>';
    searchResults.style.display = 'block';
    return;
  }
  
  results.forEach(medicine => {
    const resultItem = document.createElement('div');
    resultItem.className = 'search-result-item';
    resultItem.textContent = `${medicine.name} - ₹${medicine.price}`;
    
    resultItem.addEventListener('click', function() {
      window.location.href = `medicine-list.php?search=${encodeURIComponent(medicine.name)}`;
    });
    
    searchResults.appendChild(resultItem);
  });
  searchResults.style.display = 'block';
}

const processSearch = debounce(async (searchTerm) => {
  const results = await fetchSearchResults(searchTerm);
  displayResults(results);
});

searchInput.addEventListener('input', function() {
  processSearch(this.value.trim());
});

document.addEventListener('click', function(e) {
  if (!searchForm.contains(e.target)) {
    searchResults.style.display = 'none';
  }
});

searchForm.addEventListener('submit', async function(e) {
  e.preventDefault();
  const searchTerm = searchInput.value.trim();
  if (searchTerm) {
    window.location.href = `medicine-list.php?search=${encodeURIComponent(searchTerm)}`;
  }
});

function updatePincode() {
  const pincodeInput = document.querySelector('.pincode-input');
  const pincodeValue = pincodeInput.value.trim();
  const pincodeMessage = document.getElementById('pincodeMessage');
  const currentPincode = document.querySelector('.current-pincode');
  
  if (pincodeValue.length === 6 && /^\d{6}$/.test(pincodeValue)) {
    // Display success message
    pincodeMessage.innerHTML = '<div class="alert alert-success">Pincode is available</div>';
    
    // Update the displayed pincode in the navbar
    currentPincode.textContent = pincodeValue;
    
    // Close the modal properly
    setTimeout(() => {
      const modalElement = document.getElementById('pincodeModal');
      const modalInstance = bootstrap.Modal.getInstance(modalElement);
      if (modalInstance) {
        modalInstance.hide();
        
        // Ensure backdrop is removed
        document.body.classList.remove('modal-open');
        const backdrops = document.getElementsByClassName('modal-backdrop');
        while(backdrops.length > 0) {
          backdrops[0].parentNode.removeChild(backdrops[0]);
        }
      }
    }, 1500);
  } else {
    // Display error message for invalid pincode
    pincodeMessage.innerHTML = '<div class="alert alert-danger">Please enter a valid 6-digit pincode</div>';
  }
}
</script>
</body>
</html>
