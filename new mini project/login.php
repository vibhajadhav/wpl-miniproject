<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>

<body style="margin: 0; font-family: Arial, sans-serif; background-color: #f8f9fa; display: flex; justify-content: center; align-items: center; height: 100vh;">

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
                
                <!-- Input Fields -->
                <label for="email" style="display: block; font-size: 16px; margin-bottom: 5px;">Enter email address</label>
                <input id="email" type="email" placeholder="Enter email address" style="width: 90%; padding: 14px; border: none; border-radius: 8px; font-size: 16px; margin-bottom: 15px;">

                <label for="password" style="display: block; font-size: 16px; margin-bottom: 5px;">Enter password</label>
                <input id="password" type="password" placeholder="Enter password" style="width: 90%; padding: 14px; border: none; border-radius: 8px; font-size: 16px; margin-bottom: 25px;">

                <!-- Login Button -->
                <button style="width: 100%; padding: 14px; background: transparent; color: white; border: 2px solid white; border-radius: 8px; font-size: 18px; font-weight: bold; cursor: pointer;">
                    LOGIN
                </button>

                <!-- Sign Up Link -->
                <p style="text-align: center; margin-top: 15px; font-size: 14px;">
                    Don't have an account? <a href="signup.html" style="color: white; font-weight: bold; text-decoration: none;">Sign Up</a>
                </p>
            </div>
        </div>

    </div>

</body>
</html>
