<?php
declare(strict_types=1);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
include('includes/connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $result = $conn->query("SELECT * FROM users WHERE email = '$email'");
    
    if($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if(password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['email'] = $user['email'];
            header("Location: index.php");
            exit();
        }
    }
    
    $_SESSION['notification'] = [
        'type' => 'error',
        'message' => 'Invalid email or password!'
    ];
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="assets/style.css">
    <title>Login</title>
    <link rel="stylesheet" href="assets/login.css"></head>
<body>
    <?php include('includes/navbar.php'); ?>
    
    <div class="auth-form">
        <h2>Welcome Back</h2>
        <form method="POST">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn-primary">Login</button>
            <p style="margin-top: 1rem;">New user? <a href="register.php">Create account</a></p>
        </form>
    </div>
</body>
</html>