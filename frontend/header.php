<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CUSAT Store</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>

<header class="main-header">
    <div class="header-container">
        
        <a href="index.php" class="logo-link">
            <span class="logo-icon">⚓</span>
            <span class="logo-text-blue">CUSAT</span>
            <span class="logo-text-green">Store</span>
        </a>

        <nav class="nav-menu">
            <a href="index.php" class="nav-item">Products     </a>
            <a href="cart.php" class="nav-item">🛒Cart</a>
        </nav>

        <div class="header-actions">
            <a href="cart.php" class="cart-status-link">
                <span id="cart-badge" class="badge-count" style="display: none;">0</span>
            </a>

            <a href="login.php" class="nav-btn btn-login">Login</a>
            <a href="register.php" class="nav-btn btn-register">Register</a>
        </div>

    </div>
</header>