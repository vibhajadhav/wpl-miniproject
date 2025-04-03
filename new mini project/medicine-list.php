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
    
    .nav-icons a {
      text-decoration: none;
      color: #333;
      font-weight: 500;
    }
    
    .delivery-location {
      cursor: pointer;
    }
  </style>
</head>
<body>
<!-- Updated Navigation Bar -->
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
      <a href="login.php" class="d-flex align-items-center">
        <span style="font-size: 16px; margin-right: 5px;">👤</span> Login
      </a>
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

  <!-- Medicine Details Section -->
  <div class="container mt-5">
    <div class="row">
      <!-- Image Section -->
      <div class="col-md-5">
        <img id="medicineImage" src="pharma-coq10.jpg" class="img-fluid" alt="Medicine">
      </div>

      <!-- Details Section -->
      <div class="col-md-7">
        <h2 id="medicineName">Pharma Coenzyme Q10 (Coq10) 200mg With Piperine - 60 Capsules</h2>
        <p id="medicinePrice">
          <strong>₹822.03</strong> <span class="text-muted"><del>₹1749</del></span>
          <span class="badge bg-danger">53% OFF</span>
        </p>
        <p class="text-muted">Inclusive of all taxes</p>

        <button id="addToCartBtn" class="btn btn-primary">Add To Cart</button>
      </div>
    </div>
  </div>

  <script>
    // Current medicine data
    let currentMedicine = {
      id: '11',
      name: 'Pharma Coenzyme Q10 (Coq10) 200mg With Piperine - 60 Capsules',
      price: 822.03,
      originalPrice: 1749,
      image: 'pharma-coq10.jpg',
      description: 'Coenzyme Q10 supplement with piperine for better absorption'
    };

    // DOM elements
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    const searchForm = document.getElementById('searchForm');
    const medicineImage = document.getElementById('medicineImage');
    const medicineName = document.getElementById('medicineName');
    const medicinePrice = document.getElementById('medicinePrice');
    const addToCartBtn = document.getElementById('addToCartBtn');

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
      
      // Show success message
      alert(`${product.name} added to cart!`);
      
      // Redirect to cart page
      window.location.href = 'cart.php';
    }

    // Add to cart button click handler
    addToCartBtn.addEventListener('click', function() {
      addToCart(currentMedicine);
    });

    // Debounce function to limit API calls
    function debounce(func, timeout = 300) {
      let timer;
      return (...args) => {
        clearTimeout(timer);
        timer = setTimeout(() => { func.apply(this, args); }, timeout);
      };
    }

    // Fetch search results from PHP backend
    async function fetchSearchResults(query) {
      if (query.length < 2) {
        searchResults.style.display = 'none';
        return [];
      }
      
      try {
        const response = await fetch(`search.php?query=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        if (data.error) {
          console.error(data.error);
          return [];
        }
        
        return data.results;
      } catch (error) {
        console.error('Error fetching search results:', error);
        return [];
      }
    }

    // Display search results
    function displayResults(results) {
      if (results.length === 0) {
        searchResults.innerHTML = '<div class="search-result-item">No medicines found</div>';
        searchResults.style.display = 'block';
        return;
      }
      
      searchResults.innerHTML = '';
      results.forEach((medicine, index) => {
        const resultItem = document.createElement('div');
        resultItem.className = 'search-result-item';
        resultItem.innerHTML = `
          <div>${medicine.name}</div>
          <div>₹${parseFloat(medicine.price).toFixed(2)}</div>
        `;
        
        // Add data attributes for cart functionality
        resultItem.dataset.id = index; // Using index as ID since we don't have m_id in the results
        resultItem.dataset.name = medicine.name;
        resultItem.dataset.price = medicine.price;
        
        resultItem.addEventListener('click', function() {
          // Update the current medicine
          currentMedicine = {
            id: this.dataset.id,
            name: this.dataset.name,
            price: parseFloat(this.dataset.price),
            originalPrice: parseFloat(this.dataset.price) * 1.2, // Estimate original price as 20% higher
            image: 'placeholder.jpg',
            description: `${this.dataset.name} medicine`
          };
          
          // Update the page with the selected medicine
          medicineName.textContent = currentMedicine.name;
          medicinePrice.innerHTML = `
            <strong>₹${currentMedicine.price.toFixed(2)}</strong> 
            <span class="text-muted"><del>₹${currentMedicine.originalPrice.toFixed(2)}</del></span>
            <span class="badge bg-danger">20% OFF</span>
          `;
          
          // Hide results and clear search
          searchResults.style.display = 'none';
          searchInput.value = '';
        });
        
        searchResults.appendChild(resultItem);
      });
      
      searchResults.style.display = 'block';
    }

    // Search input handler with debounce
    const processSearch = debounce(async (searchTerm) => {
      const results = await fetchSearchResults(searchTerm);
      displayResults(results);
    });

    searchInput.addEventListener('input', function() {
      const searchTerm = this.value.trim();
      processSearch(searchTerm);
    });

    // Hide results when clicking outside
    document.addEventListener('click', function(e) {
      if (!searchForm.contains(e.target)) {
        searchResults.style.display = 'none';
      }
    });

    // Form submission
    searchForm.addEventListener('submit', async function(e) {
      e.preventDefault();
      const searchTerm = searchInput.value.trim();
      
      if (searchTerm.length === 0) return;
      
      const results = await fetchSearchResults(searchTerm);
      
      if (results.length === 1) {
        // If only one result, automatically select it
        const medicine = results[0];
        currentMedicine = {
          id: '1', // Using a placeholder ID
          name: medicine.name,
          price: parseFloat(medicine.price),
          originalPrice: parseFloat(medicine.price) * 1.2, // Estimate original price as 20% higher
          image: 'placeholder.jpg',
          description: `${medicine.name} medicine`
        };
        
        medicineName.textContent = currentMedicine.name;
        medicinePrice.innerHTML = `
          <strong>₹${currentMedicine.price.toFixed(2)}</strong> 
          <span class="text-muted"><del>₹${currentMedicine.originalPrice.toFixed(2)}</del></span>
          <span class="badge bg-danger">20% OFF</span>
        `;
        searchResults.style.display = 'none';
        searchInput.value = '';
      } else {
        // Show all matching results
        displayResults(results);
      }
    });

    // Pincode function
    function updatePincode() {
      const pincodeInput = document.querySelector('.pincode-input');
      const pincodeMessage = document.getElementById('pincodeMessage');
      
      if (pincodeInput.value.length === 6 && /^\d+$/.test(pincodeInput.value)) {
        // Save to localStorage
        localStorage.setItem('PharmaPincode', pincodeInput.value);
        
        // Update display
        document.querySelector('.current-pincode').textContent = pincodeInput.value;
        
        pincodeMessage.innerHTML = '<div class="alert alert-success">Delivery available in your area!</div>';
        
        // Close modal after 1.5 seconds
        setTimeout(() => {
          const modal = bootstrap.Modal.getInstance(document.getElementById('pincodeModal'));
          modal.hide();
        }, 1500);
      } else {
        pincodeMessage.innerHTML = '<div class="alert alert-danger">Please enter a valid 6-digit pincode</div>';
      }
    }

    // Load saved pincode if exists
    document.addEventListener('DOMContentLoaded', function() {
      const savedPincode = localStorage.getItem('PharmaPincode');
      if (savedPincode) {
        document.querySelector('.current-pincode').textContent = savedPincode;
      }
    });
  </script>
</body>
</html>

