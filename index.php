<?php
/**
 * Homepage - E-Commerce Platform
 * Displays featured products, categories, and recommendations
 */

require_once __DIR__ . '/includes/init.php';

// Get featured products
$product = new Product();
$category = new Category();
$recommendation = new Recommendation();

$featuredProducts = $product->getFeatured(8);
$categories = $category->getParents();
$trendingProducts = $recommendation->getTrendingProducts(6);

// Log user activity if logged in
if (Session::isLoggedIn()) {
    $recommendation->logActivity(Session::getUserId(), null, 'view_homepage');
}

// Set page title
$page_title = 'Home';

// Include header
includeHeader($page_title);
?>

<div class="container">
    <!-- Hero Section -->
    <section class="hero-section" style="background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 4rem 2rem; border-radius: 12px; margin: 2rem 0; text-align: center;">
        <h1 style="font-size: 3rem; margin-bottom: 1rem;">Welcome to <?php echo APP_NAME; ?></h1>
        <p style="font-size: 1.2rem; margin-bottom: 2rem;">Discover amazing products from trusted vendors worldwide</p>
        <a href="/products.php" class="btn btn-lg" style="background: white; color: #007bff; border: none;">Shop Now</a>
    </section>

    <!-- Categories Section -->
    <section class="categories-section">
        <h2 class="text-center mb-4">Shop by Category</h2>
        <div class="categories-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
            <?php foreach ($categories as $cat): ?>
                <a href="/category.php?id=<?php echo $cat['id']; ?>" class="category-card" style="display: block; background: white; border-radius: 8px; padding: 1.5rem; text-align: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); transition: transform 0.3s ease;">
                    <div style="font-size: 2rem; margin-bottom: 1rem;">
                        <?php
                        // Simple category icons
                        $icons = [
                            'Electronics' => '📱',
                            'Clothing' => '👕',
                            'Home & Garden' => '🏠',
                            'Books' => '📚',
                            'Sports & Outdoors' => '⚽',
                            'Beauty & Health' => '💄',
                            'Toys & Games' => '🎮',
                            'Automotive' => '🚗'
                        ];
                        echo $icons[$cat['name']] ?? '🛍️';
                        ?>
                    </div>
                    <h3><?php echo htmlspecialchars($cat['name']); ?></h3>
                    <p style="color: #666; font-size: 0.9rem;"><?php echo htmlspecialchars($cat['description']); ?></p>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="featured-products-section">
        <h2 class="text-center mb-4">Featured Products</h2>
        <div class="products-grid">
            <?php foreach ($featuredProducts as $prod): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?php echo getProductImageUrl($prod['image_url'] ?? ''); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>">
                        <?php if ($prod['featured']): ?>
                            <div class="product-badge featured">Featured</div>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title">
                            <a href="/product.php?id=<?php echo $prod['id']; ?>">
                                <?php echo htmlspecialchars($prod['name']); ?>
                            </a>
                        </h3>
                        <p class="product-vendor">by <?php echo htmlspecialchars($prod['vendor_name'] ?? 'Unknown Vendor'); ?></p>
                        <p class="product-price"><?php echo formatPrice($prod['price']); ?></p>
                        <div class="product-actions">
                            <button class="btn add-to-cart" data-product-id="<?php echo $prod['id']; ?>">
                                Add to Cart
                            </button>
                            <?php if (Session::isLoggedIn()): ?>
                                <button class="btn btn-outline add-to-wishlist" data-product-id="<?php echo $prod['id']; ?>">
                                    💖
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Trending Products Section -->
    <?php if (!empty($trendingProducts)): ?>
    <section class="trending-products-section">
        <h2 class="text-center mb-4">Trending Now</h2>
        <div class="products-grid">
            <?php foreach ($trendingProducts as $prod): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="<?php echo getProductImageUrl($prod['image_url'] ?? ''); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>">
                        <div class="product-badge" style="background: #ff6b6b;">🔥 Trending</div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title">
                            <a href="/product.php?id=<?php echo $prod['id']; ?>">
                                <?php echo htmlspecialchars($prod['name']); ?>
                            </a>
                        </h3>
                        <p class="product-price"><?php echo formatPrice($prod['price']); ?></p>
                        <button class="btn add-to-cart" data-product-id="<?php echo $prod['id']; ?>">
                            Add to Cart
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Features Section -->
    <section class="features-section" style="background: white; border-radius: 12px; padding: 3rem 2rem; margin: 3rem 0; box-shadow: 0 2px 20px rgba(0,0,0,0.1);">
        <h2 class="text-center mb-4">Why Choose Us?</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
            <div class="feature-item text-center">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🚚</div>
                <h3>Fast Shipping</h3>
                <p>Free shipping on orders over $50. Get your products delivered quickly and safely.</p>
            </div>
            <div class="feature-item text-center">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🔒</div>
                <h3>Secure Payments</h3>
                <p>Your payment information is safe with our encrypted checkout process.</p>
            </div>
            <div class="feature-item text-center">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🏆</div>
                <h3>Quality Products</h3>
                <p>All products are carefully vetted by our quality assurance team.</p>
            </div>
            <div class="feature-item text-center">
                <div style="font-size: 3rem; margin-bottom: 1rem;">💬</div>
                <h3>24/7 Support</h3>
                <p>Our customer service team is here to help you anytime, anywhere.</p>
            </div>
        </div>
    </section>
</div>

<?php includeFooter(); ?>