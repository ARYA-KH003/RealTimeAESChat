<?php
include('DBconnection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm-password']);
    
    if ($password !== $confirm_password) {
        echo "Passwords do not match!";
        exit();
    }

    $sql = "SELECT id FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    if (mysqli_num_rows($result) > 0) {
        echo "This email is already registered. Please use a different email or log in.";
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (email, password) VALUES ('$email', '$hashed_password')";

    if (mysqli_query($conn, $sql)) {
        echo "Registration successful!";
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }

    mysqli_close($conn);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AryaTel - Register</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <div class="login-container">
        <h2>Register for AryaTel</h2>
        <form action="register.php" method="post">
            <div class="input-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="input-group">
                <label for="confirm-password">Confirm Password</label>
                <input type="password" id="confirm-password" name="confirm-password" required>
            </div>
            <button type="submit" class="login-button">Register</button>
        </form>
        <p class="register-text">Already have an account? <a href="login.php">Login</a></p>
    </div>
</body>
</html>