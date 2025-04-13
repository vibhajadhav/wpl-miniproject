<?php
$host = "localhost";
$user = "root";
$pass = ""; // Change if needed
$db = "pharmacy";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = isset($_GET['email']) ? $_GET['email'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $name = $_POST["username"];

    $update = $conn->prepare("UPDATE customer SET username = ? WHERE email = ?");
    $update->bind_param("ss", $name, $email);
    $update->execute();
}

// Fetch user data
$user = ["username" => "", "phoneno" => "", "email" => ""];
if ($email) {
    $stmt = $conn->prepare("SELECT * FROM customer WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Profile</title>
    <style>
        body {
            font-family: Arial;
            background: #f5f5f5;
            padding: 50px;
        }
        .form-container {
            background: white;
            max-width: 500px;
            margin: auto;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .form-container input[type="text"],
        .form-container input[type="email"] {
            width: 100%;
            padding: 14px;
            margin-top: 8px;
            margin-bottom: 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .form-container input[readonly] {
            background: #f1f1f1;
        }
        .form-container button {
            padding: 12px;
            width: 100%;
            background-color: #3366FF;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
        }
        .form-container h2 {
            margin-bottom: 30px;
            color: #333;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Edit Profile</h2>
    <form method="POST" action="">
        <label>Name*</label>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

        <label>Mobile Number*</label>
        <input type="text" value="<?= htmlspecialchars($user['phoneno']) ?>" readonly>

        <label>Email*</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>

        <button type="submit">SAVE</button>
    </form>
</div>

</body>
</html>
