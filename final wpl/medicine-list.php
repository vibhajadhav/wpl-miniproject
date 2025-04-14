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
  <title>Medicine List - Pharma</title>
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
    
    .medicine-card {
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      margin-bottom: 20px;
    }
    
    .medicine-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .medicine-price {
      font-size: 1.2rem;
      font-weight: bold;
      color: #0d6efd;
    }
    
    .medicine-original-price {
      text-decoration: line-through;
      color: #6c757d;
      font-size: 0.9rem;
    }
    
    .medicine-discount {
      background-color: #dc3545;
      color: white;
      padding: 2px 8px;
      border-radius: 4px;
      font-size: 0.8rem;
      margin-left: 5px;
    }
    
    .filters {
      background-color: #f8f9fa;
      padding: 15px;
      border-radius: 5px;
      margin-bottom: 20px;
    }
    
    .page-title {
      margin-bottom: 30px;
      border-bottom: 2px solid #f0f0f0;
      padding-bottom: 10px;
    }
    
    .no-results {
      text-align: center;
      padding: 50px 0;
      color: #6c757d;
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
          value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
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
          <li><a class="dropdown-item" href="contact.html">Send Message</a></li>
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
        <input type="text" class="pincode-input form-control" placeholder="Enter 6-digit pincode" maxlength="6" pattern="\d{6}" required>
        <button class="pincode-submit btn btn-primary mt-3" onclick="updatePincode()">Check & Update</button>
        <div class="mt-3" id="pincodeMessage"></div>
      </div>
    </div>
  </div>
</div>

<!-- Medicine List Section -->
<div class="container mt-4">
  <h1 class="page-title">
    <?php 
    if (isset($_GET['search']) && !empty($_GET['search'])) {
      echo 'Search Results for: ' . htmlspecialchars($_GET['search']);
    } else {
      echo 'All Medicines';
    }
    ?>
  </h1>
  
  <div class="row">
    <!-- Filters Column -->
    <div class="col-md-3">
      <div class="filters">
        <h4>Filters</h4>
        <div class="mb-3">
          <label for="sortBy" class="form-label">Sort By</label>
          <select class="form-select" id="sortBy">
            <option value="name-asc">Name (A-Z)</option>
            <option value="name-desc">Name (Z-A)</option>
            <option value="price-asc">Price (Low to High)</option>
            <option value="price-desc">Price (High to Low)</option>
          </select>
        </div>
        
        <div class="mb-3">
          <label class="form-label">Price Range</label>
          <div class="d-flex">
            <input type="number" class="form-control me-2" id="minPrice" placeholder="Min">
            <input type="number" class="form-control" id="maxPrice" placeholder="Max">
          </div>
          <button class="btn btn-sm btn-outline-primary mt-2" id="applyPriceFilter">Apply</button>
        </div>
      </div>
    </div>
    
    <!-- Medicines Column -->
    <div class="col-md-9">
      <div class="row" id="medicinesList">
        <?php
        // Database connection
        $db_host = 'localhost';
        $db_name = 'pharmacy';
        $db_user = 'root';
        $db_pass = '';
        
        try {
          $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
          $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          
          // Prepare query based on search parameter
          if (isset($_GET['search']) && !empty($_GET['search'])) {
            $stmt = $pdo->prepare("SELECT m_id, m_name, price FROM medicine WHERE m_name LIKE :search");
            $stmt->execute(['search' => '%' . $_GET['search'] . '%']);
          } else {
            $stmt = $pdo->query("SELECT m_id, m_name, price FROM medicine");
          }
          
          $medicines = $stmt->fetchAll(PDO::FETCH_ASSOC);
          
          if (count($medicines) > 0) {
            foreach ($medicines as $medicine) {
              // Calculate discount (20% off for example)
              $originalPrice = round($medicine['price'] * 1.25, 2);
              $discount = round(($originalPrice - $medicine['price']) / $originalPrice * 100);
              
              echo '<div class="col-md-4 medicine-item" 
                        data-id="' . $medicine['m_id'] . '" 
                        data-name="' . htmlspecialchars($medicine['m_name']) . '" 
                        data-price="' . $medicine['price'] . '">';
              echo '<div class="card medicine-card">';
              echo '<img src="placeholder.jpg" class="card-img-top" alt="' . htmlspecialchars($medicine['m_name']) . '">';
              echo '<div class="card-body">';
              echo '<h5 class="card-title">' . htmlspecialchars($medicine['m_name']) . '</h5>';
              echo '<div class="d-flex align-items-center mb-3">';
              echo '<span class="medicine-price">₹' . number_format($medicine['price'], 2) . '</span>';
              echo '<span class="medicine-original-price ms-2">₹' . number_format($originalPrice, 2) . '</span>';
              echo '<span class="medicine-discount">' . $discount . '% OFF</span>';
              echo '</div>';
              echo '<button class="btn btn-primary add-to-cart-btn" 
      data-id="' . $medicine['m_id'] . '" 
      data-name="' . htmlspecialchars($medicine['m_name']) . '" 
      data-price="' . $medicine['price'] . '">Add to Cart</button>';
              echo '</div></div></div>';
            }
          } else {
            echo '<div class="col-12 no-results">';
            echo '<h3>No medicines found</h3>';
            echo '<p>Try a different search term or browse our categories</p>';
            echo '</div>';
          }
        } catch (PDOException $e) {
          echo '<div class="col-12 no-results">';
          echo '<h3>Database Error</h3>';
          echo '<p>We\'re experiencing technical difficulties. Please try again later.</p>';
          echo '</div>';
          // Log the error (in a production environment)
          // error_log('Database error: ' . $e->getMessage());
        }
        ?>
      </div>
    </div>
  </div>
</div>

<footer class="bg-primary text-white py-4 mt-5">
  <div class="container">
    <div class="row">
      <div class="col-md-3 mb-3">
        <h5>Pharma</h5>
        <p>Your trusted online pharmacy.</p>
      </div>
      <div class="col-md-3 mb-3">
        <h5>Quick Links</h5>
        <ul class="list-unstyled">
          <li><a href="#" class="text-white text-decoration-none">Medicines</a></li>
          <li><a href="#" class="text-white text-decoration-none">Healthcare Products</a></li>
          <li><a href="#" class="text-white text-decoration-none">Offers</a></li>
          <li><a href="#" class="text-white text-decoration-none">New Arrivals</a></li>
        </ul>
      </div>
      <div class="col-md-3 mb-3">
        <h5>Company</h5>
        <ul class="list-unstyled">
          <li><a href="#" class="text-white text-decoration-none">About Us</a></li>
          <li><a href="#" class="text-white text-decoration-none">Careers</a></li>
          <li><a href="contact.html" class="text-white text-decoration-none">Contact Us</a></li>
          <li><a href="#" class="text-white text-decoration-none">FAQ</a></li>
        </ul>
      </div>
      <div class="col-md-3 mb-3">
        <h5>Follow Us</h5>
        <ul class="list-unstyled">
          <li><a href="#" class="text-white text-decoration-none">Facebook</a></li>
          <li><a href="#" class="text-white text-decoration-none">Twitter</a></li>
          <li><a href="#" class="text-white text-decoration-none">Instagram</a></li>
        </ul>
      </div>
    </div>
    <hr style="border-top: 1px solid rgba(255, 255, 255, 0.3);">
    <div class="row">
      <div class="col-md-6">
        <p>&copy; 2023 Pharma. All rights reserved.</p>
      </div>
      <div class="col-md-6 text-md-end">
        <a href="#" class="text-white text-decoration-none me-3">Terms of Service</a>
        <a href="#" class="text-white text-decoration-none">Privacy Policy</a>
      </div>
    </div>
  </div>
</footer>

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
    resultItem.innerHTML = `
      <div>${medicine.name}</div>
      <div>₹${parseFloat(medicine.price).toFixed(2)}</div>
    `;
    
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

searchForm.addEventListener('submit', function(e) {
  e.preventDefault();
  const searchTerm = searchInput.value.trim();
  if (searchTerm) {
    window.location.href = `medicine-list.php?search=${encodeURIComponent(searchTerm)}`;
  }
});

// Pincode functionality
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

// Load saved pincode if exists
document.addEventListener('DOMContentLoaded', function() {
  const savedPincode = localStorage.getItem('PharmaPincode');
  if (savedPincode) {
    document.querySelector('.current-pincode').textContent = savedPincode;
  }
  
  // Add to cart functionality
  const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
  addToCartButtons.forEach(button => {
    button.addEventListener('click', function() {
      const id = this.getAttribute('data-id');
      const name = this.getAttribute('data-name');
      const price = parseFloat(this.getAttribute('data-price'));
      
      const medicine = {
        id: id,
        name: name,
        price: price,
        quantity: 1
      };
      
      // Add to cart
      let cart = JSON.parse(localStorage.getItem('PharmaCart')) || [];
      
      // Check if product already in cart
      const existingItemIndex = cart.findIndex(item => item.id === medicine.id);
      
      if (existingItemIndex !== -1) {
        cart[existingItemIndex].quantity += 1;
      } else {
        cart.push(medicine);
      }
      
      localStorage.setItem('PharmaCart', JSON.stringify(cart));
      
      // Show success message
      alert(`${medicine.name} added to cart!`);
    });
  });
  
  // Sorting functionality
  document.getElementById('sortBy').addEventListener('change', function() {
    sortMedicines(this.value);
  });
  
  // Price filter
  document.getElementById('applyPriceFilter').addEventListener('click', function() {
    const minPrice = parseFloat(document.getElementById('minPrice').value) || 0;
    const maxPrice = parseFloat(document.getElementById('maxPrice').value) || Infinity;
    
    filterByPrice(minPrice, maxPrice);
  });
});

// Add to cart function
function addToCart(medicine) {
  let cart = JSON.parse(localStorage.getItem('PharmaCart')) || [];
  
  // Check if product already in cart
  const existingItemIndex = cart.findIndex(item => item.id === medicine.id);
  
  if (existingItemIndex !== -1) {
    cart[existingItemIndex].quantity += 1;
  } else {
    cart.push(medicine);
  }
  
  localStorage.setItem('PharmaCart', JSON.stringify(cart));
  
  // Show success message
  alert(`${medicine.name} added to cart!`);
}

// Sort medicines function
function sortMedicines(sortOption) {
  const medicinesList = document.getElementById('medicinesList');
  const medicines = Array.from(medicinesList.querySelectorAll('.medicine-item'));
  
  medicines.sort((a, b) => {
    if (sortOption === 'name-asc') {
      return a.dataset.name.localeCompare(b.dataset.name);
    } else if (sortOption === 'name-desc') {
      return b.dataset.name.localeCompare(a.dataset.name);
    } else if (sortOption === 'price-asc') {
      return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
    } else if (sortOption === 'price-desc') {
      return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
    }
    return 0;
  });
  
  // Clear and re-append sorted items
  medicinesList.innerHTML = '';
  medicines.forEach(medicine => {
    medicinesList.appendChild(medicine);
  });
}

// Filter by price function
function filterByPrice(min, max) {
  const medicines = document.querySelectorAll('.medicine-item');
  
  medicines.forEach(medicine => {
    const price = parseFloat(medicine.dataset.price);
    if (price >= min && price <= max) {
      medicine.style.display = '';
    } else {
      medicine.style.display = 'none';
    }
  });
}
</script>
</body>
</html>
