<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Skincare - HealthPlus</title>
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
    .pincode-modal .modal-content {
      border-radius: 10px;
    }
    .pincode-input {
      padding: 10px;
      font-size: 16px;
      border: 1px solid #ddd;
      border-radius: 5px;
      width: 100%;
      margin-bottom: 15px;
    }
    .pincode-submit {
      background-color: #0d6efd;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      cursor: pointer;
      width: 100%;
    }
    .pincode-submit:hover {
      background-color: #0b5ed7;
    }
    .delivery-location {
      cursor: pointer;
    }
    .delivery-location:hover {
      text-decoration: underline;
    }
    .current-pincode {
      color: #0d6efd;
    }
    .product-card {
      margin-bottom: 30px;
      border: 1px solid #eee;
      border-radius: 10px;
      padding: 20px;
      transition: transform 0.3s;
    }
    .product-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .page-header {
      margin-bottom: 30px;
      padding-bottom: 15px;
      border-bottom: 1px solid #eee;
    }
  </style>
</head>
<body>
  <!-- Navigation Bar -->
  <nav class="navbar navbar-expand-lg bg-white shadow-sm">
    <div class="container">
      <a class="navbar-brand" href="index.php">
        <img src="logo.png" alt="Logo" height="40" />
      </a>
      <div class="delivery-location" data-bs-toggle="modal" data-bs-target="#pincodeModal">
        <div>Express delivery to</div>
        <b class="current-pincode d-block">Select Pincode</b>
      </div>
      <form class="d-flex search-bar" style="width: 400px;">
        <input
          class="form-control me-2"
          type="search"
          placeholder="Search for"
          aria-label="Search"
          style="flex-grow: 1;" 
        />
        <button class="btn btn-primary" style="width: 80px; padding: 6px 12px;">
          Search
        </button>
      </form>
      <div class="nav-icons">
        <a href="cart.php" class="cart">🛒 Cart</a>
        <a href="login.php">Login</a>
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

  <!-- Skincare Products Section -->
  <div class="container mt-5">
    <div class="page-header">
      <h1>Skincare Essentials</h1>
      <p class="text-muted">Nourish and protect your skin with our premium products</p>
    </div>

    <div class="row">
      <!-- Product 1 -->
      <div class="col-md-6">
        <div class="product-card">
          <div class="row">
            <div class="col-md-5">
              <img src="vitamin-c-serum.jpg" class="img-fluid" alt="Vitamin C Serum">
            </div>
            <div class="col-md-7">
              <h3>La Roche-Posay Vitamin C Serum - 30ml</h3>
              <p>
                <strong>₹2,499</strong> <span class="text-muted"><del>₹3,200</del></span>
                <span class="badge bg-danger">22% OFF</span>
              </p>
              <p class="text-muted">Brightening antioxidant serum</p>
              <ul>
                <li>10% Pure Vitamin C</li>
                <li>Reduces signs of aging</li>
                <li>Fragrance-free, non-comedogenic</li>
              </ul>
              <button class="btn btn-primary">Add To Cart</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Product 2 -->
      <div class="col-md-6">
        <div class="product-card">
          <div class="row">
            <div class="col-md-5">
              <img src="hyaluronic-acid.jpg" class="img-fluid" alt="Hyaluronic Acid">
            </div>
            <div class="col-md-7">
              <h3>The Ordinary Hyaluronic Acid 2% + B5 - 30ml</h3>
              <p>
                <strong>₹1,050</strong> <span class="text-muted"><del>₹1,400</del></span>
                <span class="badge bg-danger">25% OFF</span>
              </p>
              <p class="text-muted">Intense hydration serum</p>
              <ul>
                <li>Plumps and hydrates skin</li>
                <li>Lightweight, fast-absorbing</li>
                <li>Vegan and cruelty-free</li>
              </ul>
              <button class="btn btn-primary">Add To Cart</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>