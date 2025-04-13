<?php
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Database connection
    $conn = new mysqli("localhost", "root", "", "pharmacy");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Get input values
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phoneno = $_POST['phoneno'];
    $address = $_POST['address'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // Validate passwords
    if ($password !== $confirm) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        // Insert into customer table
        $stmt = $conn->prepare("INSERT INTO customer (username, email, phoneno, address, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $email, $phoneno, $address, $password);

        if ($stmt->execute()) {
            echo "<script>alert('Sign up successful!'); window.location.href='login.html';</script>";
        } else {
            echo "<script>alert('Error: Could not sign up. Please check your details.');</script>";
        }

        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
</head>
<body style="margin: 0; font-family: Arial, sans-serif; background-color: #f8f9fa; display: flex; justify-content: center; align-items: center; height: 100vh;">

    <div style="display: flex; width: 900px;">

        <!-- Left Side (Doctor Image) -->
        <div style="width: 400px; background: #3366FF; padding: 40px; border-radius: 20px; color: white; text-align: center;">
            <img src="doctor.png" alt="Doctor" style="width: 90%; margin-top: 40px;">
        </div>

        <!-- Right Side (Form) -->
        <div style="margin-left: 40px; padding: 20px; border-radius: 20px; border: 3px solid #3366FF; width: 550px; text-align: left;">
            <form method="POST" action="" style="background: #3366FF; color: white; padding: 80px; border-radius: 20px;">
                <h2 style="text-align: center;">Sign up</h2>

                <label>Enter username</label>
                <input name="username" type="text" required style="width: 90%; padding: 12px; margin-bottom: 10px; border-radius: 6px; border: none;">

                <label>Enter email address</label>
                <input name="email" type="email" required style="width: 90%; padding: 12px; margin-bottom: 10px; border-radius: 6px; border: none;">

                <label>Enter mobile number</label>
                <input name="phoneno" type="text" required style="width: 90%; padding: 12px; margin-bottom: 10px; border-radius: 6px; border: none;">

                <label>Enter address</label>
                <input name="address" type="text" required style="width: 90%; padding: 12px; margin-bottom: 10px; border-radius: 6px; border: none;">

                <label>Enter password</label>
                <input name="password" type="password" required style="width: 90%; padding: 12px; margin-bottom: 10px; border-radius: 6px; border: none;">

                <label>Confirm password</label>
                <input name="confirm_password" type="password" required style="width: 90%; padding: 12px; margin-bottom: 20px; border-radius: 6px; border: none;">

                <button type="submit" style="width: 100%; padding: 14px; background: transparent; color: white; border: 2px solid white; border-radius: 8px; font-size: 18px; font-weight: bold;">
                    SIGN UP
                </button>

                <p style="text-align: center; margin-top: 15px;">
                    Already have an account? <a href="login.html" style="color: white;">Login</a>
                </p>
            </form>
        </div>

    </div>

</body>
</html>
