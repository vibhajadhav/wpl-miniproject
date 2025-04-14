<?php
// Include database connection
require_once 'db_connect.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

// Initialize variables
$email = "";
$error = "";

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    
    // Validate input
    if (empty($email)) {
        $error = "Email is required";
    } elseif (empty($password)) {
        $error = "Password is required";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id, username, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Password is correct, start a new session
                session_start();
                
                // Store data in session variables
                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["email"] = $user["email"];
                
                // Redirect to home page
                header("Location: index.php");
                exit;
            } else {
                $error = "Invalid password";
            }
        } else {
            // Check customer table as fallback (for existing customers)
            $stmt = $conn->prepare("SELECT username, email, phoneno, address FROM customer WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                $customer = $result->fetch_assoc();
                
                // For demo purposes, allow login with any password for existing customers
                // In production, you should require password reset for these users
                session_start();
                
                // Store data in session variables
                $_SESSION["username"] = $customer["username"];
                $_SESSION["email"] = $customer["email"];
                
                // Redirect to home page
                header("Location: index.php");
                exit;
            } else {
                $error = "No account found with that email";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>

<body style="margin: 0; font-family: Arial, sans-serif; background-color: #f8f9fa; display: flex; justify-content: center; align-items: center; height: 100vh;">

    <!-- Navigation Links -->
    <div style="position: absolute; top: 20px; right: 20px;">
        <a href="index.php" style="margin-right: 15px; color: #3366FF; text-decoration: none;">Home</a>
        <a href="contact.html" style="color: #3366FF; text-decoration: none;">Contact Us</a>
    </div>

    <div style="display: flex; width: 900px;">

        <!-- Left Side (Doctor Banner) -->
        <div style="width: 400px; background: #3366FF; padding: 40px; border-radius: 20px; color: white; text-align: center;">
            <img src="doctor.png" alt="Doctor" style="width: 90%; margin-top: 40px;">
        </div>

        <!-- Right Side (Login Form) -->
        <div style="margin-left: 50px; padding: 40px; border-radius: 20px; border: 2px solid #3366FF; width: 550px; text-align: left;">
            <div style="background: #3366FF; color: white; padding: 80px; border-radius: 20px; text-align: left;">
                <h2 style="margin: 0 0 20px 0; font-size: 26px; font-weight: bold; text-shadow: 1px 1px 2px rgba(0,0,0,0.2); text-align: center;">
                    Login
                </h2>
                
                <?php if (!empty($error)): ?>
                    <div style="color: #ffcccc; background-color: rgba(255, 0, 0, 0.2); padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Input Fields -->
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <label for="email" style="display: block; font-size: 16px; margin-bottom: 5px;">Enter email address</label>
                    <input id="email" name="email" type="email" placeholder="Enter email address" style="width: 90%; padding: 14px; border: none; border-radius: 8px; font-size: 16px; margin-bottom: 15px;" value="<?php echo htmlspecialchars($email); ?>" required>

                    <label for="password" style="display: block; font-size: 16px; margin-bottom: 5px;">Enter password</label>
                    <input id="password" name="password" type="password" placeholder="Enter password" style="width: 90%; padding: 14px; border: none; border-radius: 8px; font-size: 16px; margin-bottom: 25px;" required>

                    <!-- Login Button -->
                    <button type="submit" style="width: 100%; padding: 14px; background: transparent; color: white; border: 2px solid white; border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer;">
                        LOGIN
                    </button>
                </form>

                <!-- Sign Up Link -->
                <p style="text-align: center; margin-top: 15px; font-size: 14px;">
                    Don't have an account? <a href="signup.php" style="color: white; font-weight: bold; text-decoration: none;">Sign Up</a>
                </p>
            </div>
        </div>

    </div>

</body>
</html>