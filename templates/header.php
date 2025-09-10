<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title . ' - FezaMarket'); ?></title>
    
    <!-- Modern CSS Framework -->
    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/css/styles.css">
    
    <!-- Preload critical assets -->
    <link rel="preload" href="/assets/js/ui.js" as="script">
    
    <!-- Favicon and meta -->
    <link rel="icon" type="image/x-icon" href="/images/favicon.ico">
    <meta name="description" content="<?php echo htmlspecialchars($meta_description ?? 'FezaMarket - Buy & Sell Everything Online'); ?>">
    
    <style>
        /* User dropdown styles */
        .account-dropdown {
            position: relative;
        }
        
        .user-menu-toggle {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .user-avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
        }
        
        .user-dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            min-width: 200px;
            z-index: 1000;
            margin-top: 0.5rem;
        }
        
        .dropdown-item {
            display: block;
            padding: 0.75rem 1rem;
            color: #374151;
            text-decoration: none;
            border-bottom: 1px solid #f3f4f6;
            transition: background-color 0.2s;
        }
        
        .dropdown-item:hover {
            background-color: #f9fafb;
        }
        
        .dropdown-item:last-child {
            border-bottom: none;
        }
        
        .dropdown-divider {
            height: 1px;
            background-color: #e5e7eb;
            margin: 0.25rem 0;
        }
    </style>
    
    <!-- Modern JavaScript -->
    <script src="/assets/js/ui.js" defer></script>
    <script src="/js/fezamarket.js" defer></script>
    <script>
        // User dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const userMenuToggle = document.querySelector('.user-menu-toggle');
            const userDropdown = document.querySelector('.user-dropdown-menu');
            
            if (userMenuToggle && userDropdown) {
                userMenuToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const isVisible = userDropdown.style.display === 'block';
                    userDropdown.style.display = isVisible ? 'none' : 'block';
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!userMenuToggle.contains(e.target) && !userDropdown.contains(e.target)) {
                        userDropdown.style.display = 'none';
                    }
                });
            }
        });
    </script>
</head>
<body>
    <header class="fezamarket-header">
        <!-- Top Navigation Bar -->
        <div class="top-nav">
            <div class="container">
                <div class="top-nav-content">
                    <div class="top-nav-left">
                        <span class="greeting">Hi! 
                            <?php if (Session::isLoggedIn()): ?>
                                <?php echo htmlspecialchars($current_user['first_name'] ?? 'User'); ?>!
                            <?php else: ?>
                                <a href="/login.php" class="auth-link">Sign in</a> or <a href="/register.php" class="auth-link">register</a>
                            <?php endif; ?>
                        </span>
                        <a href="/deals.php" class="top-nav-link">Daily Deals</a>
                        <a href="/brands.php" class="top-nav-link">Brand Outlet</a>
                        <a href="/gift-cards.php" class="top-nav-link">Gift Cards</a>
                        <a href="/help.php" class="top-nav-link">Help & Contact</a>
                    </div>
                    <div class="top-nav-right">
                        <div class="ship-to">Ship to</div>
                        <a href="/sell.php" class="top-nav-link">Sell</a>
                        <?php if (Session::isLoggedIn()): ?>
                            <div class="watchlist-dropdown">
                                <a href="/wishlist.php" class="top-nav-link">Watchlist <span class="dropdown-arrow">▼</span></a>
                            </div>
                            <div class="account-dropdown">
                                <a href="#" class="top-nav-link user-menu-toggle">
                                    <img src="<?php echo getUserAvatar($current_user, 24); ?>" alt="Avatar" class="user-avatar">
                                    <?php echo htmlspecialchars($current_user['first_name']); ?> <span class="dropdown-arrow">▼</span>
                                </a>
                                <div class="user-dropdown-menu" style="display: none;">
                                    <a href="/account.php" class="dropdown-item">Dashboard</a>
                                    <?php if (hasRole('vendor')): ?>
                                        <a href="/seller-center.php" class="dropdown-item">Seller Center</a>
                                    <?php else: ?>
                                        <a href="<?php echo sellerUrl('register'); ?>" class="dropdown-item">Start Selling</a>
                                    <?php endif; ?>
                                    <?php if (hasRole('admin')): ?>
                                        <a href="/admin/" class="dropdown-item">Admin Panel</a>
                                    <?php endif; ?>
                                    <div class="dropdown-divider"></div>
                                    <a href="/account.php?tab=orders" class="dropdown-item">Orders</a>
                                    <a href="/account.php?tab=security" class="dropdown-item">Security</a>
                                    <div class="dropdown-divider"></div>
                                    <a href="/logout.php" class="dropdown-item">Sign Out</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="/wishlist.php" class="top-nav-link">Watchlist</a>
                            <a href="/account.php" class="top-nav-link" onclick="setIntendedUrl(); return false;">My FezaMarket</a>
                        <?php endif; ?>
                        <a href="/notifications.php" class="notification-icon">🔔</a>
                        <a href="/cart.php" class="cart-icon-top">🛒
                            <?php if (isset($cart_count) && $cart_count > 0): ?>
                                <span class="cart-count"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Header -->
        <div class="main-header">
            <div class="container">
                <div class="main-header-content">
                    <!-- Logo -->
                    <div class="logo-section">
                        <a href="/" class="fezamarket-logo">
                            <span class="logo-f">f</span><span class="logo-e">e</span><span class="logo-z">z</span><span class="logo-a">a</span><span class="logo-market">Market</span>
                        </a>
                    </div>

                    <!-- Search Section -->
                    <div class="search-section">
                        <div class="search-form-container">
                            <form class="search-form" action="/search.php" method="GET">
                                <div class="search-input-group">
                                    <select class="category-select" name="category" id="category-select">
                                        <option value="">All Categories</option>
                                        <?php
                                        $category = new Category();
                                        $categories = $category->getParents();
                                        foreach ($categories as $cat):
                                        ?>
                                            <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="text" 
                                           name="q" 
                                           id="search-input" 
                                           class="search-input" 
                                           placeholder="Search for anything" 
                                           value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>"
                                           autocomplete="off">
                                    <button type="submit" class="search-button">Search</button>
                                </div>
                                <div class="search-suggestions" id="search-suggestions" style="display: none;"></div>
                            </form>
                            <a href="/search/advanced.php" class="advanced-search">Advanced</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Navigation -->
        <div class="category-nav">
            <div class="container">
                <nav class="category-nav-content">
                    <a href="/live.php" class="category-nav-item">FezaMarket Live</a>
                    <a href="/saved.php" class="category-nav-item">Saved</a>
                    <a href="/category.php?name=electronics" class="category-nav-item">Electronics</a>
                    <a href="/category.php?name=motors" class="category-nav-item">Motors</a>
                    <a href="/category.php?name=fashion" class="category-nav-item">Fashion</a>
                    <a href="/category.php?name=collectibles" class="category-nav-item">Collectibles and Art</a>
                    <a href="/category.php?name=sports" class="category-nav-item">Sports</a>
                    <a href="/category.php?name=health-beauty" class="category-nav-item">Health & Beauty</a>
                    <a href="/category.php?name=industrial" class="category-nav-item">Industrial equipment</a>
                    <a href="/category.php?name=home-garden" class="category-nav-item">Home & Garden</a>
                    <a href="/deals.php" class="category-nav-item">Deals</a>
                    <a href="/sell.php" class="category-nav-item">Sell</a>
                </nav>
            </div>
        </div>
    </header>
    
    <main class="main-content"><?php