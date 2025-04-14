<?php
// Include database connection
require_once 'db_connect.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: index.php");
    exit;
}

// Initialize variables
$username = "";
$email = "";
$phone = "";
$error = "";
$success = "";

// Process signup form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["mobile"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $address = ""; // You can add an address field to your form if needed
    
    // Validate input
    if (empty($username)) {
        $error = "Username is required";
    } elseif (empty($email)) {
        $error = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif (empty($phone)) {
        $error = "Phone number is required";
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $error = "Phone number must be 10 digits";
    } elseif (empty($password)) {
        $error = "Password is required";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } elseif ($password != $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email already exists";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user into database
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if ($stmt->execute()) {
                // Also add to customer table for compatibility with existing system
                $stmt = $conn->prepare("INSERT INTO customer (username, email, phoneno, address) VALUES (?, ?, ?, ?)");
                $phone_int = (int)$phone;
                $stmt->bind_param("ssis", $username, $email, $phone_int, $address);
                $stmt->execute();
                
                $success = "Account created successfully! You can now login.";
                
                // Clear form data
                $username = $email = $phone = "";
            } else {
                $error = "Something went wrong. Please try again.";
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
    <title>Sign Up</title>
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

        <!-- Right Side (Sign-Up Form) -->
        <div style="margin-left: 40px; padding: 20px; border-radius: 20px; border: 3px solid #3366FF; width: 550px; text-align: left;">
            <div style="background: #3366FF; color: white; padding: 80px; border-radius: 20px; text-align: left;">
                <h2 style="margin: 0 0 20px 0; font-size: 26px; font-weight: bold; text-shadow: 1px 1px 2px rgba(0,0,0,0.2); text-align: center;">
                    Sign up
                </h2>
                
                <?php if (!empty($error)): ?>
                    <div style="color: #ffcccc; background-color: rgba(255, 0, 0, 0.2); padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div style="color: #ccffcc; background-color: rgba(0, 128, 0, 0.2); padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Input Fields -->
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <label for="username" style="display: block; font-size: 16px; margin-bottom: 5px;">Username</label>
                    <input id="username" name="username" type="text" placeholder="Enter username" style="width: 90%; padding: 14px; border: none; border-radius: 8px; font-size: 16px; margin-bottom: 15px;" value="<?php echo htmlspecialchars($username); ?>" required>
                    
                    <label for="mobile" style="display: block; font-size: 16px; margin-bottom: 5px;">Enter mobile number</label>
                    <input id="mobile" name="mobile" type="text" placeholder="Enter mobile number" style="width: 90%; padding: 14px; border: none; border-radius: 8px; font-size: 16px; margin-bottom: 15px;" value="<?php echo htmlspecialchars($phone); ?>" required>

                    <label for="email" style="display: block; font-size: 16px; margin-bottom: 5px;">Enter email address</label>
                    <input id="email" name="email" type="email" placeholder="Enter email address" style="width: 90%; padding: 14px; border: none; border-radius: 8px; font-size: 16px; margin-bottom: 15px;" value="<?php echo htmlspecialchars($email); ?>" required>

                    <label for="password" style="display: block; font-size: 16px; margin-bottom: 5px;">Enter password</label>
                    <input id="password" name="password" type="password" placeholder="Enter password" style="width: 90%; padding: 14px; border: none; border-radius: 8px; font-size: 16px; margin-bottom: 15px;" required>

                    <label for="confirm_password" style="display: block; font-size: 16px; margin-bottom: 5px;">Confirm password</label>
                    <input id="confirm_password" name="confirm_password" type="password" placeholder="Confirm password" style="width: 90%; padding: 14px; border: none; border-radius: 8px; font-size: 16px; margin-bottom: 25px;" required>

                    <!-- Signup Button -->
                    <button type="submit" style="width: 100%; padding: 14px; background: transparent; color: white; border: 2px solid white; border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer;">
                        SIGN UP
                    </button>
                </form>
                
                <p style="text-align: center; margin-top: 15px; font-size: 14px;">
                    Already have an account? <a href="login.php" style="color: white; font-weight: bold; text-decoration: none;">Login</a>
                </p>
            </div>
        </div>

    </div>

</body>
</html>