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
    
    /* Menu bar styling */
    .menu-bar {
      background-color: #f8f9fa;
      padding: 0.5rem 0;
      border-bottom: 1px solid #e9ecef;
    }
    
    .menu-bar ul {
      display: flex;
      list-style: none;
      margin: 0;
      padding: 0;
      justify-content: center;
      flex-wrap: wrap;
    }
    
    .menu-bar li {
      margin: 0 1rem;
    }
    
    .menu-bar a {
      color: #333;
      text-decoration: none;
      font-weight: 500;
      padding: 0.5rem 0;
      display: block;
    }
    
    .menu-bar a:hover {
      color: #0d6efd;
    }
    
    /* Responsive adjustments */
    @media (max-width: 991.98px) {
      .search-container {
        margin: 1rem 0;
        max-width: 100%;
      }
      
      .navbar-brand {
        margin-right: 0;
      }
      
      .delivery-location {
        margin-right: 0 !important;
        margin-bottom: 0.5rem;
      }
      
      .nav-icons {
        margin-top: 1rem;
        width: 100%;
        justify-content: space-around;
      }
      
      .menu-bar ul {
        flex-direction: column;
        align-items: center;
      }
      
      .menu-bar li {
        margin: 0.25rem 0;
      }
    }
  </style>
</head>
<body>
<!-- Updated Navigation Bar with proper responsive structure -->
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
              value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
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
    </div>
  </div>
</nav>

<!-- Menu Links - Now also responsive -->
<div class="menu-bar">
  <div class="container">
    <ul class="d-flex flex-column flex-md-row justify-content-center">
      <li><a href="medicine-list.php">Medicine</a></li>
      <li><a href="#">Recommended</a></li>
      <li><a href="#">Doctor Consult</a></li>
      <li><a href="#">Healthcare</a></li>
      <li><a href="#">Health Blogs</a></li>
      <li><a href="#">Offers</a></li>
      <li><a href="#">About us</a></li>
    </ul>
  </div>
</div>

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

  <!-- Banner Section (Carousel) -->
  <div id="bannerCarousel" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
      <div class="carousel-item active">
        <img
          src="banner1.jpg"
          class="d-block w-100"
          alt="Moisturising Sunscreen"
        />
      </div>
      <div class="carousel-item">
        <img src="banner2.jpg" class="d-block w-100" alt="Second Slide" />
      </div>
    </div>
    <button
      class="carousel-control-prev"
      type="button"
      data-bs-target="#bannerCarousel"
      data-bs-slide="prev"
    >
      <span class="carousel-control-prev-icon"></span>
    </button>
    <button
      class="carousel-control-next"
      type="button"
      data-bs-target="#bannerCarousel"
      data-bs-slide="next"
    >
      <span class="carousel-control-next-icon"></span>
    </button>
  </div>

  <!-- Categories Section -->
  <section class="categories">
    <h2 class="section-title">CATEGORIES</h2>
    <div class="category-grid">
      <!-- Category 1: Pharmacy (Now Links to Medicine List) -->
      <a href="medicine-list.php" class="category-item">
        <img src="pharmacy.jpg" alt="Pharmacy" />
        <h3 class="category-title">PHARMACY</h3>
        <p>General Medicine</p>
      </a>
      <!-- Category 2 -->
      <a href="medicine-list.php" class="category-item">
        <img src="vitamins.jpg" alt="Vitamins" />
        <h3 class="category-title">Vitamins</h3>
      </a>
      <!-- Category 3 -->
      <a href="medicine-list.php" class="category-item">
        <img src="skincare.jpg" alt="Skincare" />
        <h3 class="category-title">Skincare</h3>
      </a>
      <!-- Category 4 -->
      <a href="medicine-list.php" class="category-item">
        <img src="sports-nutrition.jpg" alt="Sports Nutrition" />
        <h3 class="category-title">Sports & Nutrition</h3>
      </a>
      <!-- Category 5 -->
      <a href="medicine-list.php" class="category-item">
        <img src="eldercare.jpg" alt="Eldercare" />
        <h3 class="category-title">Eldercare</h3>
      </a>
      <!-- Category 6 -->
      <a href="medicine-list.php" class="category-item">
        <img src="personal-care.jpg" alt="Personal Care" />
        <h3 class="category-title">Personal Care</h3>
      </a>
      <!-- Category 7 -->
      <a href="medicine-list.php" class="category-item">
        <img src="ayurvedic.jpg" alt="Ayurvedic" />
        <h3 class="category-title">Ayurvedic</h3>
      </a>
      <!-- Category 8 -->
      <a href="medicine-list.php" class="category-item">
        <img src="for-women.jpg" alt="For Women" />
        <h3 class="category-title">For Women</h3>
      </a>
      <!-- Category 9 -->
      <a href="medicine-list.php" class="category-item">
        <img src="healthy-food.jpg" alt="Healthy Food" />
        <h3 class="category-title">Healthy Food Packs</h3>
      </a>
    </div>
  </section>

  <!-- "Making Lives Better" Section -->
  <section class="py-5">
    <div class="container">
      <!-- Logo + Title Row -->
      <div class="row justify-content-center">
        <div class="col-md-8 text-center">
          <img
            src="logo.png"
            alt="Pharma 2025 Logo"
            class="img-fluid"
            style="max-width: 200px;"
          />
          <h2 class="mt-4 mb-3 fw-bold">Making Lives Better</h2>
        </div>
      </div>

      <!-- Main Text Content Row -->
      <div class="row justify-content-center mt-4">
        <div class="col-md-10">
          <!-- Delivering Health Across India -->
          <h3 class="fw-semibold">Delivering Health Across India!</h3>
          <p>
            Pharma is online retailing healthcare in just a click, spanning over
            1200 cities and 14000+ pin codes nationwide. From bustling metros like
            Mumbai, Delhi, Bengaluru, and Kolkata to towns across the country, we
            bring medicines and wellness products straight to your door!
          </p>

          <!-- Making Healthcare Effortless -->
          <h3 class="fw-semibold mt-4">Making Healthcare Effortless!</h3>
          <p>
            Pharma is a name that you can trust for choosing your own licensed
            retail chemists, allowing you to order medicines at the best possible
            rates. With a focus on convenience and reliability, ordering your
            healthcare essentials has never been simpler than now.
          </p>
          <p><strong>Now, that is healthcare – Fast and easy:</strong></p>
          <ul>
            <li>
              <strong>Vast Product Selection</strong> – Browse through 1 lakh+ medicines, OTC products, and healthcare essentials
            </li>
            <li>
              <strong>Book Lab Tests from Home</strong> – Schedule blood tests, health checkups, and diagnostics with home sample collection from accredited labs
            </li>
            <li>
              <strong>Secure Payment Options</strong> – Pay securely online with discounts or opt for cash delivery for flexibility
            </li>
            <li>
              <strong>New! Pharma's Subscription Model</strong> – receive automated reminders and timely deliveries, ensuring you always have your essential medications on hand
            </li>
          </ul>

          <!-- Trusted Medical Information -->
          <h3 class="fw-semibold mt-4">Trusted Medical Information at Your Fingertips</h3>
          <p>
            Stay informed with authentic and expert-reviewed health insights. From medicine guides to wellness advice, we provide well-researched content to support your healthcare journey.
          </p>
          <p>
            Pharma is dedicated to transforming healthcare by making it easier, more affordable, and within reach for everyone. Order with confidence and experience hassle-free medical care today!
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- Trending Near You Section (clickable cards) -->
  <section class="py-5 bg-light">
    <div class="container">
      <h2 class="mb-4">Trending Near You</h2>
      <div class="row row-cols-2 row-cols-md-4 g-4">
        <!-- Product 1 -->
        <div class="col">
          <a href="product1-details.php" class="text-decoration-none text-dark">
            <div class="card text-center">
              <img src="tren1.jpg" class="card-img-top" alt="Trending Product 1" />
              <div class="card-body">
                <h5 class="card-title">ShelCal 500</h5>
                <p class="card-text">500 mg</p>
              </div>
            </div>
          </a>
        </div>
        <!-- Product 2 -->
        <div class="col">
          <a href="product2-details.php" class="text-decoration-none text-dark">
            <div class="card text-center">
              <img src="tren2.jpg" class="card-img-top" alt="Trending Product 2" />
              <div class="card-body">
                <h5 class="card-title">Absorb</h5>
                <p class="card-text">500 mg</p>
              </div>
            </div>
          </a>
        </div>
        <!-- Product 3 -->
        <div class="col">
          <a href="product3-details.php" class="text-decoration-none text-dark">
            <div class="card text-center">
              <img src="trendi3.jpg" class="card-img-top" alt="Trending Product 3" />
              <div class="card-body">
                <h5 class="card-title">Live Easy</h5>
                <p class="card-text">500 mg</p>
              </div>
            </div>
          </a>
        </div>
        <!-- Product 4 -->
        <div class="col">
          <a href="product4-details.php" class="text-decoration-none text-dark">
            <div class="card text-center">
              <img src="trendi4.jpg" class="card-img-top" alt="Trending Product 4" />
              <div class="card-body">
                <h5 class="card-title">Evion 400</h5>
                <p class="card-text">400 mg</p>
              </div>
            </div>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- Featured Brands Section (clickable images) -->
  <section class="py-5">
    <div class="container">
      <h2 class="mb-4">Featured Brands</h2>
      <div class="row row-cols-2 row-cols-md-4 g-3 text-center justify-content-center">
        <div class="col" style="max-width: 180px;">
          <a href="brand1-details.php">
            <img src="br1.png" alt="Brand 1" class="img-fluid px-2" style="max-height: 110px; object-fit: contain;" />
          </a>
        </div>
        <div class="col" style="max-width: 180px;">
          <a href="brand2-details.php">
            <img src="br2.png" alt="Brand 2" class="img-fluid px-2" style="max-height: 110px; object-fit: contain;" />
          </a>
        </div>
        <div class="col" style="max-width: 180px;">
          <a href="brand3-details.php">
            <img src="bran3.png" alt="Brand 3" class="img-fluid px-2" style="max-height: 110px; object-fit: contain;" />
          </a>
        </div>
        <div class="col" style="max-width: 180px;">
          <a href="brand4-details.php">
            <img src="br4.png" alt="Brand 4" class="img-fluid px-2" style="max-height: 110px; object-fit: contain;" />
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- Often Ordered Section (clickable cards) -->
  <section class="py-5 bg-light">
    <div class="container">
      <h2 class="mb-4">Often Ordered</h2>
      <div class="row row-cols-2 row-cols-md-4 g-4">
        <!-- Example product 1 -->
        <div class="col">
          <a href="often1-details.php" class="text-decoration-none text-dark">
            <div class="card text-center">
              <img src="ofte1.jpg" class="card-img-top" alt="Often Ordered 1" />
              <div class="card-body">
                <h5 class="card-title">Melaglow</h5>
                <p class="card-text">20g</p>
              </div>
            </div>
          </a>
        </div>
        <!-- Example product 2 -->
        <div class="col">
          <a href="often2-details.php" class="text-decoration-none text-dark">
            <div class="card text-center">
              <img src="ofte2.jpg" class="card-img-top" alt="Often Ordered 2" />
              <div class="card-body">
                <h5 class="card-title">Derma Roller</h5>
                <p class="card-text">100 g</p>
              </div>
            </div>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- User Testimonials Carousel -->
  <section class="py-5 bg-white">
    <div class="container">
      <h2 class="mb-4 text-center">What Our Customers Say</h2>
      <div id="testimonialCarousel" class="carousel slide testimonial-carousel" data-bs-ride="carousel">
        <div class="carousel-inner">
          <!-- Testimonial 1 -->
          <div class="carousel-item active">
            <div class="row justify-content-center">
              <div class="col-md-8">
                <div class="testimonial-card text-center">
                  <p class="testimonial-text">"I love using Pharma 2025! The delivery is fast and the products are genuine. I've been using this service for my monthly medicines and it's been a lifesaver."</p>
                  <div class="testimonial-author">John Doe</div>
                  <div class="testimonial-location">Mumbai</div>
                </div>
              </div>
            </div>
          </div>
          <!-- Testimonial 2 -->
          <div class="carousel-item">
            <div class="row justify-content-center">
              <div class="col-md-8">
                <div class="testimonial-card text-center">
                  <p class="testimonial-text">"Excellent service and a wide range of products at great prices. Their customer support is very responsive and helpful when I had questions about my order."</p>
                  <div class="testimonial-author">Jane Smith</div>
                  <div class="testimonial-location">Delhi</div>
                </div>
              </div>
            </div>
          </div>
          <!-- Testimonial 3 -->
          <div class="carousel-item">
            <div class="row justify-content-center">
              <div class="col-md-8">
                <div class="testimonial-card text-center">
                  <p class="testimonial-text">"Using Pharma 2025 has been a game-changer for my family's healthcare needs. The subscription service ensures we never run out of essential medications."</p>
                  <div class="testimonial-author">Alex Kumar</div>
                  <div class="testimonial-location">Kolkata</div>
                </div>
              </div>
            </div>
          </div>
          <!-- Testimonial 4 -->
          <div class="carousel-item">
            <div class="row justify-content-center">
              <div class="col-md-8">
                <div class="testimonial-card text-center">
                  <p class="testimonial-text">"The convenience of ordering medicines online and getting them delivered to my doorstep is unparalleled. Pharma 2025 has made managing my chronic condition much easier."</p>
                  <div class="testimonial-author">Priya Sharma</div>
                  <div class="testimonial-location">Bangalore</div>
                </div>
              </div>
            </div>
          </div>
          <!-- Testimonial 5 -->
          <div class="carousel-item">
            <div class="row justify-content-center">
              <div class="col-md-8">
                <div class="testimonial-card text-center">
                  <p class="testimonial-text">"I was skeptical at first about ordering medicines online, but Pharma 2025 has proven to be reliable and trustworthy. The quality of products is excellent."</p>
                  <div class="testimonial-author">Rahul Patel</div>
                  <div class="testimonial-location">Hyderabad</div>
                </div>
              </div>
            </div>
          </div>
          <!-- Testimonial 6 -->
          <div class="carousel-item">
            <div class="row justify-content-center">
              <div class="col-md-8">
                <div class="testimonial-card text-center">
                  <p class="testimonial-text">"Their express delivery option saved me when I needed emergency medication late at night. Truly a service that cares about its customers' wellbeing."</p>
                  <div class="testimonial-author">Ananya Gupta</div>
                  <div class="testimonial-location">Chennai</div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Carousel Controls -->
        <button class="carousel-control-prev" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Next</span>
        </button>
        <!-- Indicators -->
        <div class="carousel-indicators position-relative mt-3">
          <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>  
          <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
          <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
          <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="3" aria-label="Slide 4"></button>
          <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="4" aria-label="Slide 5"></button>
          <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide-to="5" aria-label="Slide 6"></button>
        </div>
      </div>
    </div>
  </section>

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

</script>
<script>
        // Load saved pincode if exists
        document.addEventListener('DOMContentLoaded', function() {
            const savedPincode = localStorage.getItem('PharmaPincode');
            if (savedPincode) {
                document.querySelector('.current-pincode').textContent = savedPincode;
            }
        });

        // Function to update pincode
        function updatePincode() {
            const pincodeInput = document.querySelector('.pincode-input');
            const pincodeValue = pincodeInput.value.trim();
            const pincodeMessage = document.getElementById('pincodeMessage');
            const currentPincode = document.querySelector('.current-pincode');
            
            if (pincodeValue.length === 6 && /^\d{6}$/.test(pincodeValue)) {
                // Save to localStorage
                localStorage.setItem('PharmaPincode', pincodeValue);
                
                // Update display
                currentPincode.textContent = pincodeValue;
                
                // Show success message
                pincodeMessage.innerHTML = '<div class="alert alert-success">Delivery available in your area!</div>';
                
                // Close the modal after 1.5 seconds
                setTimeout(() => {
                    const modalElement = document.getElementById('pincodeModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalElement);
                    if (modalInstance) {
                        modalInstance.hide();
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
