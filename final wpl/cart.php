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
  <title>Dashboard</title>
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

    /* Ensure these styles are present */
#searchResults {
  position: absolute;
  top: 100%; 
  width: 100%;
  z-index: 1000;
  background: white;
  display: none; /* Initially hidden */
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
    
    /* Cart specific styles */
    .cart-item {
      border: 1px solid #eee;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 15px;
    }
    
    .quantity-control {
      display: flex;
      align-items: center;
    }
    
    .quantity-btn {
      width: 30px;
      height: 30px;
      border: 1px solid #ddd;
      background: #f8f9fa;
      border-radius: 4px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }
    
    .quantity-input {
      width: 50px;
      height: 30px;
      text-align: center;
      border: 1px solid #ddd;
      margin: 0 5px;
    }
    
    .empty-cart {
      text-align: center;
      padding: 40px 20px;
      border: 1px dashed #ddd;
      border-radius: 8px;
    }
    
    .empty-cart-icon {
      font-size: 48px;
      margin-bottom: 15px;
    }
    
    .summary-card {
      border: 1px solid #eee;
      border-radius: 8px;
      padding: 20px;
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
    
    .no-results {
      padding: 10px;
      text-align: center;
      color: #666;
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
          <input type="text" class="pincode-input" placeholder="Enter 6-digit pincode" maxlength="6" pattern="\\d{6}" required>
          <button class="pincode-submit" onclick="updatePincode()">Check & Update</button>
          <div class="mt-3" id="pincodeMessage"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Cart Content -->
  <div class="container my-5">
    <h2 class="mb-4">Your Shopping Cart</h2>
    
    <div class="row">
      <!-- Cart Items Column -->
      <div class="col-lg-8">
        <div id="cartItemsContainer">
          <!-- Cart items will be dynamically inserted here -->
          <div class="empty-cart">
            <div class="empty-cart-icon">🛒</div>
            <h3>Your cart is empty</h3>
            <p class="text-muted">Looks like you haven't added anything to your cart yet</p>
            <a href="index.php" class="btn btn-primary">Continue Shopping</a>
          </div>
        </div>
      </div>
      
      <!-- Order Summary Column -->
      <div class="col-lg-4">
        <div class="summary-card">
          <h4 class="mb-4">Order Summary</h4>
          
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
          
            <a href="checkout.php" id="checkoutBtn" class="btn btn-primary w-100">Proceed to Checkout</a>
          <p class="text-muted small mt-2">By placing your order, you agree to Pharma's <a href="#">Terms of Use</a></p>
        </div>
      </div>
    </div>
  </div>

  <!-- JavaScript -->
  <script>
    // Document Ready
    document.addEventListener('DOMContentLoaded', function() {
      // Load cart from localStorage
      let cart = JSON.parse(localStorage.getItem('PharmaCart')) || [];
      updateCartDisplay(cart);
      
      // Load saved pincode if exists
      const savedPincode = localStorage.getItem('PharmaPincode');
      if (savedPincode) {
        document.querySelector('.current-pincode').textContent = savedPincode;
      }
      
      // Setup search functionality
      setupSearchFunctionality();
      
      // Make sure the search results container is properly initialized
      const searchResults = document.getElementById('searchResults');
      if (searchResults) {
        searchResults.style.display = 'none';
      }
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
    
    // Function to update cart display
    function updateCartDisplay(cart) {
      const cartItemsContainer = document.getElementById('cartItemsContainer');
      const subtotalElement = document.getElementById('subtotal');
      const totalElement = document.getElementById('total');
      const discountElement = document.getElementById('discount');
      const checkoutBtn = document.getElementById('checkoutBtn');
      
      if (cart.length === 0) {
        cartItemsContainer.innerHTML = `
          <div class="empty-cart">
            <div class="empty-cart-icon">🛒</div>
            <h3>Your cart is empty</h3>
            <p class="text-muted">Looks like you haven't added anything to your cart yet</p>
            <a href="index.php" class="btn btn-primary">Continue Shopping</a>
          </div>
        `;
        subtotalElement.textContent = '₹0';
        totalElement.textContent = '₹49';
        discountElement.textContent = '-₹0';
        checkoutBtn.disabled = true;
        checkoutBtn.classList.add('disabled');
        checkoutBtn.href = 'javascript:void(0)';
        checkoutBtn.onclick = function(e) {
          e.preventDefault();
          alert('Your cart is empty. Please add items to your cart before checkout.');
        };
        return;
      }

      // If cart has items, ensure checkout button is enabled
      checkoutBtn.disabled = false;
      checkoutBtn.classList.remove('disabled');
      checkoutBtn.href = 'checkout.php';
      checkoutBtn.onclick = null;
      
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
      checkoutBtn.disabled = false;
      
      // Generate cart items HTML
      let cartItemsHTML = '';
      cart.forEach(item => {
        const originalPrice = item.originalPrice || (item.price * 1.2); // If originalPrice not set, estimate original price as 20% higher
        cartItemsHTML += `
          <div class="cart-item" data-id="${item.id}">
            <div class="row">
              <div class="col-md-3">
                <img src="${item.image || 'placeholder.jpg'}" class="img-fluid" alt="${item.name}">
              </div>
              <div class="col-md-6">
                <h5>${item.name}</h5>
                <p class="text-muted">${item.description || `${item.name} medicine`}</p>
                <div class="quantity-control mt-3">
                  <button class="quantity-btn minus-btn">-</button>
                  <input type="number" class="quantity-input" value="${item.quantity}" min="1">
                  <button class="quantity-btn plus-btn">+</button>
                </div>
              </div>
              <div class="col-md-3 text-end">
                <p class="fw-bold">₹${(item.price * item.quantity).toFixed(2)}</p>
                <p class="text-muted small"><del>₹${(originalPrice * item.quantity).toFixed(2)}</del></p>
                <button class="btn btn-sm btn-outline-danger remove-btn">Remove</button>
              </div>
            </div>
          </div>
        `;
      });
      
      cartItemsContainer.innerHTML = cartItemsHTML;
      
      // Add event listeners to quantity buttons
      document.querySelectorAll('.plus-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          const input = this.parentElement.querySelector('.quantity-input');
          input.value = parseInt(input.value) + 1;
          updateCartItem(this.closest('.cart-item').dataset.id, parseInt(input.value));
        });
      });
      
      document.querySelectorAll('.minus-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          const input = this.parentElement.querySelector('.quantity-input');
          if (parseInt(input.value) > 1) {
            input.value = parseInt(input.value) - 1;
            updateCartItem(this.closest('.cart-item').dataset.id, parseInt(input.value));
          }
        });
      });
      
      document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
          if (parseInt(this.value) < 1) this.value = 1;
          updateCartItem(this.closest('.cart-item').dataset.id, parseInt(this.value));
        });
      });
      
      document.querySelectorAll('.remove-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          removeCartItem(this.closest('.cart-item').dataset.id);
        });
      });
    }
    
    // Function to update cart item quantity
    function updateCartItem(id, quantity) {
      let cart = JSON.parse(localStorage.getItem('PharmaCart')) || [];
      const itemIndex = cart.findIndex(item => item.id === id);
      
      if (itemIndex !== -1) {
        cart[itemIndex].quantity = quantity;
        localStorage.setItem('PharmaCart', JSON.stringify(cart));
        updateCartDisplay(cart);
      }
    }
    
    // Function to remove item from cart
    function removeCartItem(id) {
      let cart = JSON.parse(localStorage.getItem('PharmaCart')) || [];
      cart = cart.filter(item => item.id !== id);
      localStorage.setItem('PharmaCart', JSON.stringify(cart));
      updateCartDisplay(cart);
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
      updateCartDisplay(cart);
      
      // Show success message
      alert(`${product.name} added to cart!`);
    }

    // Debounce function
    function debounce(func, timeout = 300) {
      let timer;
      return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => { func.apply(this, args); }, timeout);
      };
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
          searchResults.innerHTML = '<div class="search-result-item">Error fetching results</div>';
          searchResults.style.display = 'block';
          return;
        }
        
        // Display results
        if (data.results && data.results.length > 0) {
          let resultsHTML = '';
          data.results.forEach(medicine => {
            resultsHTML += `
              <div class="search-result-item" data-id="${medicine.id || medicine.m_id}" data-name="${medicine.name}" data-price="${medicine.price}">
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
          quantity: 1
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
            id: medicine.id || medicine.m_id || Date.now().toString(),
            name: medicine.name,
            price: parseFloat(medicine.price),
            quantity: 1
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
</script>
</body>
</html>
