<?php
// Include database connection
require_once 'db_connect.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['username']);

// Redirect to login if not logged in
if (!$isLoggedIn) {
    header("Location: login.php");
    exit;
}

// Get user data
$username = $_SESSION['username'];
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';

// Get additional user data from database
$userData = null;
if (!empty($email)) {
    // Try to get from users table first
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $userData = $result->fetch_assoc();
    } else {
        // Try to get from customer table
        $stmt = $conn->prepare("SELECT * FROM customer WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $userData = $result->fetch_assoc();
        }
    }
}

// Initialize variables
$error = "";
$success = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $newUsername = trim($_POST["username"]);
    $phone = trim($_POST["phone"]);
    $address = trim($_POST["address"]);
    
    // Validate input
    if (empty($newUsername)) {
        $error = "Username is required";
    } elseif (!empty($phone) && !preg_match("/^[0-9]{10}$/", $phone)) {
        $error = "Phone number must be 10 digits";
    } else {
        // Update user in database
        $updated = false;
        
        // Try to update in users table first
        if (isset($_SESSION['user_id'])) {
            $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
            $stmt->bind_param("si", $newUsername, $_SESSION['user_id']);
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $updated = true;
            }
        }
        
        // Update in customer table
        if (!empty($email)) {
            $stmt = $conn->prepare("UPDATE customer SET username = ?, phoneno = ?, address = ? WHERE email = ?");
            $phone_int = (int)$phone;
            $stmt->bind_param("siss", $newUsername, $phone_int, $address, $email);
            if ($stmt->execute() && $stmt->affected_rows > 0) {
                $updated = true;
            }
        }
        
        if ($updated) {
            $success = "Profile updated successfully!";
            
            // Update session data
            $_SESSION['username'] = $newUsername;
            
            // Refresh user data
            $username = $newUsername;
            
            // Reload page to show updated data
            header("Location: profile.php?updated=1");
            exit;
        } else {
            $error = "No changes were made or an error occurred.";
        }
    }
}

// Check for update success message
if (isset($_GET['updated']) && $_GET['updated'] == '1') {
    $success = "Profile updated successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Pharma</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
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
        
        /* Profile specific styles */
        .profile-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #3366FF;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 20px;
        }
    </style>
</head>
<body>
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

    <!-- Profile Content -->
    <div class="container">
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($username, 0, 1)); ?>
                </div>
                <h2><?php echo htmlspecialchars($username); ?></h2>
                <p class="text-muted"><?php echo htmlspecialchars($email); ?></p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($email); ?>" readonly>
                    <div class="form-text">Email cannot be changed.</div>
                </div>
                
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo isset($userData['phoneno']) ? htmlspecialchars($userData['phoneno']) : ''; ?>">
                </div>
                
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo isset($userData['address']) ? htmlspecialchars($userData['address']) : ''; ?></textarea>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                </div>
            </form>
            
            <div class="mt-4 text-center">
                <a href="index.php" class="btn btn-outline-secondary">Back to Home</a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
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