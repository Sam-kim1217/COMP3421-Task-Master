<?php
declare(strict_types=1);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
include('includes/connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        // Check if email exists
        $check = $conn->query("SELECT email FROM users WHERE email = '$email'");
        if($check->num_rows > 0) {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Email already exists!'
            ];
            header("Location: register.php");
            exit();
        }

        $sql = "INSERT INTO users (username, email, password) 
                VALUES ('$username', '$email', '$password')";
                
        if($conn->query($sql)) {
            $_SESSION['notification'] = [
                'type' => 'success',
                'message' => 'Registration successful! Please login.'
            ];
            header("Location: login.php");
            exit();
        }
    } catch(Exception $e) {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => 'Error: ' . $e->getMessage()
        ];
        header("Location: register.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/login.css">
</head>
<body>
    <?php include('includes/navbar.php'); ?>
    
    <div class="auth-form">
        <h2>Create Account</h2>
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn-primary">Register</button>
            <p style="margin-top: 1rem;">Already have an account? <a href="login.php">Login here</a></p>
        </form>
    </div>
</body>
</html>