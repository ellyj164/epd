<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link rel="stylesheet" href="/css/styles.css">
    <script src="/js/scripts.js" defer></script>
    <link rel="icon" type="image/x-icon" href="/images/favicon.ico">
</head>
<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="/" class="logo"><?php echo APP_NAME; ?></a>
                
                <div class="search-container">
                    <form class="search-form">
                        <input type="text" id="search" class="search-input" placeholder="Search products..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
                        <button type="submit" class="search-btn">Search</button>
                    </form>
                </div>
                
                <ul class="nav-menu">
                    <li><a href="/">Home</a></li>
                    <li><a href="/products.php">Products</a></li>
                    <li><a href="/categories.php">Categories</a></li>
                    <?php if (Session::isLoggedIn()): ?>
                        <?php if (Session::getUserRole() === 'vendor'): ?>
                            <li><a href="/vendor/">Vendor Dashboard</a></li>
                        <?php endif; ?>
                        <?php if (Session::getUserRole() === 'admin'): ?>
                            <li><a href="/admin/">Admin</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>
                
                <div class="nav-actions">
                    <?php if (Session::isLoggedIn()): ?>
                        <a href="/wishlist.php" class="nav-link" data-tooltip="Wishlist">💖</a>
                        <a href="/cart.php" class="cart-icon">
                            🛒
                            <span class="cart-count" style="display: <?php echo $cart_count > 0 ? 'flex' : 'none'; ?>">
                                <?php echo $cart_count; ?>
                            </span>
                        </a>
                        <div class="user-menu">
                            <span>Hello, <?php echo htmlspecialchars($current_user['first_name']); ?></span>
                            <div class="dropdown">
                                <a href="/account.php">My Account</a>
                                <a href="/orders.php">Orders</a>
                                <a href="/logout.php">Logout</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="/login.php" class="btn btn-outline">Login</a>
                        <a href="/register.php" class="btn">Register</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>
    
    <main class="main-content"><?php