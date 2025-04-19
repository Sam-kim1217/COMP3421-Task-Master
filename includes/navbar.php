<?php 
declare(strict_types=1);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<nav class="navbar">
    <div class="container">
        <a href="index.php" class="logo">Task Master</a>
        <div class="nav-links">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="index.php">Tasks</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<?php if(isset($_SESSION['notification'])): ?>
    <div class="notification <?= $_SESSION['notification']['type'] ?>">
        <div class="container">
            <?= $_SESSION['notification']['message'] ?>
        </div>
    </div>
    <?php unset($_SESSION['notification']); ?>
<?php endif; ?>