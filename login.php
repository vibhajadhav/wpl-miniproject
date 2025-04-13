<?php
// Handle login logic
session_start();

$host = "localhost";     // your DB host
$user = "root";          // your DB username
$pass = "";              // your DB password
$dbname = "pharmacy";    // your DB name

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM customer WHERE email=? AND password=?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $_SESSION['user'] = $res->fetch_assoc(); // store user info
        header("Location: profile.php");         // redirect to profile
        exit();
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body style="margin: 0; font-family: Arial, sans-serif; background-color: #f8f9fa; display: flex; justify-content: center; align-items: center; height: 100vh;">
    <div style="display: flex; width: 900px;">
        <div style="width: 400px; background: #3366FF; padding: 40px; border-radius: 20px; color: white; text-align: center;">
            <img src="doctor.png" alt="Doctor" style="width: 90%; margin-top: 40px;">
        </div>
        <div style="margin-left: 50px; padding: 40px; border-radius: 20px; border: 2px solid #3366FF; width: 550px; text-align: left;">
            <form method="POST" action="login.php">
                <div style="background: #3366FF; color: white; padding: 80px; border-radius: 20px;">
                    <h2 style="text-align: center;">Login</h2>

                    <?php if ($error): ?>
                        <p style="color: yellow; text-align: center;"><?php echo $error; ?></p>
                    <?php endif; ?>

                    <label for="email">Enter email address</label>
                    <input id="email" name="email" type="email" required placeholder="Enter email address" style="width: 90%; padding: 14px; border: none; border-radius: 8px; margin-bottom: 15px;">

                    <label for="password">Enter password</label>
                    <input id="password" name="password" type="password" required placeholder="Enter password" style="width: 90%; padding: 14px; border: none; border-radius: 8px; margin-bottom: 25px;">

                    <button type="submit" style="width: 100%; padding: 14px; background: transparent; color: white; border: 2px solid white; border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer;">
                        LOGIN
                    </button>

                    <p style="text-align: center; margin-top: 15px;">
                        Don't have an account? <a href="signup.php" style="color: white; font-weight: bold;">Sign Up</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
