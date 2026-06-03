<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Handle Logout
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

// Helper to check active page
function is_active($page) {
    $current_script = basename($_SERVER['SCRIPT_NAME']);
    return ($current_script == $page) ? 'active' : '';
}

// User details if logged in
$is_logged_in = isset($_SESSION['user']);
$user_name = $is_logged_in ? $_SESSION['user']['name'] : '';
$user_id = $is_logged_in ? $_SESSION['user']['id'] : '';
$is_admin = $is_logged_in && isset($_SESSION['user']['is_admin']) && $_SESSION['user']['is_admin'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CUSAT Store | Cochin University Merchandise & Books</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Floating Toast Notifications Container -->
    <div id="toast-container" class="toast-container"></div>

    <header>
        <div class="container nav-container">
            <!-- Brand Logo -->
            <a href="index.php" class="logo">
                <!-- Anchor representing marine heritage of CUSAT -->
                <svg viewBox="0 0 24 24">
                    <path d="M12 2c.72 0 1.39.38 1.76 1 .23.38.31.83.24 1.25.68.2 1.29.6 1.76 1.13.78.89.96 2.15.54 3.23l2.84 2.84c.36-.18.77-.28 1.21-.28 1.46 0 2.65 1.19 2.65 2.65 0 1.05-.62 1.96-1.5 2.39v.79c0 1.95-1.58 3.53-3.53 3.53H14v1.2c2.28.46 4 2.48 4 4.8v1h-2v-1c0-1.66-1.34-3-3-3s-3 1.34-3 3v1H7v-1c0-2.32 1.72-4.34 4-4.8v-1.2H7.53C5.58 23 4 21.42 4 19.47v-.79C3.12 18.25 2.5 17.34 2.5 16.29c0-1.46 1.19-2.65 2.65-2.65.44 0 .85.1 1.21.28l2.84-2.84c-.42-1.08-.24-2.34.54-3.23.47-.53 1.08-.93 1.76-1.13-.07-.42.01-.87.24-1.25C10.61 2.38 11.28 2 12 2zm0 6c-.55 0-1 .45-1 1s.45 1 1 1 1-.45 1-1-.45-1-1-1zm0 5c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5z"/>
                </svg>
                CUSAT <span>Store</span>
            </a>

            <!-- Navigation Links -->
            <nav class="nav-links">
                <a href="index.php" class="nav-link <?php echo is_active('index.php'); ?>">Products</a>
                <a href="cart.php" class="nav-link <?php echo is_active('cart.php'); ?>">Cart</a>
                <?php if ($is_logged_in): ?>
                    <a href="orders.php" class="nav-link <?php echo is_active('orders.php'); ?>">My Orders</a>
                    <?php if ($is_admin): ?>
                        <a href="admin.php" class="nav-link <?php echo is_active('admin.php'); ?>" style="color: var(--cusat-gold); font-weight: 700;">Merchant Panel</a>
                    <?php endif; ?>
                <?php endif; ?>
            </nav>

            <!-- Actions (Cart & Auth) -->
            <div class="nav-actions">
                <a href="cart.php" class="cart-icon-btn" title="View Cart">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                    <span id="cart-badge" class="cart-badge" style="display: none;">0</span>
                </a>

                <?php if ($is_logged_in): ?>
                    <div class="user-profile-menu">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                        </div>
                        <span class="user-name"><?php echo htmlspecialchars($user_name); ?></span>
                        <a href="index.php?logout=1" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px; margin-left: 8px;">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-secondary">Login</a>
                    <a href="register.php" class="btn btn-primary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
