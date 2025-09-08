<?php
/**
 * Homepage - FezaMarket E-Commerce Platform
 * Displays featured products, categories, and recommendations with dynamic banners
 */

require_once __DIR__ . '/includes/init.php';

// Get featured products
$product = new Product();
$category = new Category();
$recommendation = new Recommendation();

$featuredProducts = $product->getFeatured(12);
$categories = $category->getParents();
$trendingProducts = $recommendation->getTrendingProducts(8);
$newArrivals = $product->getLatest(6);

// Get random products for banners
$bannerProducts = $product->getRandomProducts(20);

// Log user activity if logged in
if (Session::isLoggedIn()) {
    $recommendation->logActivity(Session::getUserId(), null, 'view_homepage');
}

// Set page title
$page_title = 'FezaMarket - Buy & Sell Everything';

// Include header
includeHeader($page_title);
?>

<div class="container">
    <!-- Hero Banners Section -->
    <section class="hero-banners">
        <!-- Main Banner -->
        <div class="banner-main" onclick="window.location.href='/deals.php'" style="cursor: pointer;">
            <div class="banner-bg" style="background-image: url('<?php echo !empty($bannerProducts[0]['image_url']) ? getProductImageUrl($bannerProducts[0]['image_url']) : '/images/banners/trending-banner.jpg'; ?>');"></div>
            <div class="banner-overlay"></div>
            <div class="banner-content">
                <h2 class="banner-title">New & trending<br>editors' picks</h2>
                <p class="banner-subtitle">Berry shades & more</p>
                <a href="/deals.php" class="banner-btn">Shop now</a>
                <div style="margin-top: 12px;">
                    <span style="background: #0654ba; color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: bold;">Who knew?</span>
                </div>
            </div>
        </div>

        <!-- Side Banner 1 - New Arrivals -->
        <div class="banner-side" onclick="window.location.href='/category.php?name=electronics'" style="cursor: pointer; background: linear-gradient(135deg, #2d5aa0, #1e3a8a);">
            <div class="banner-bg" style="background-image: url('<?php echo !empty($bannerProducts[1]['image_url']) ? getProductImageUrl($bannerProducts[1]['image_url']) : '/images/banners/electronics-banner.jpg'; ?>');"></div>
            <div class="banner-overlay"></div>
            <div class="banner-content">
                <h3 class="banner-title" style="font-size: 18px;">Hot new arrivals</h3>
                <a href="/category.php?name=electronics" class="banner-btn">Shop now</a>
                <div style="margin-top: 8px; font-size: 14px; font-weight: bold;">StockX</div>
            </div>
        </div>

        <!-- Side Banner 2 - Kids Food -->
        <div class="banner-side" onclick="window.location.href='/category.php?name=home-garden'" style="cursor: pointer; background: linear-gradient(135deg, #fbbf24, #f59e0b);">
            <div class="banner-bg" style="background-image: url('<?php echo !empty($bannerProducts[2]['image_url']) ? getProductImageUrl($bannerProducts[2]['image_url']) : '/images/banners/food-banner.jpg'; ?>');"></div>
            <div class="banner-overlay"></div>
            <div class="banner-content">
                <h3 class="banner-title" style="font-size: 18px;">Kids' food faves<br>in as fast as an hour*</h3>
                <a href="/category.php?name=home-garden" class="banner-btn">Shop now</a>
            </div>
        </div>
    </section>

    <!-- Secondary Banners -->
    <section class="secondary-banners" style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 20px; margin: 20px 0;">
        <!-- New Decor -->
        <div class="banner-card" onclick="window.location.href='/category.php?name=home-garden'" style="cursor: pointer; background: linear-gradient(135deg, #fef3c7, #fbbf24); border-radius: 8px; padding: 20px; min-height: 200px; position: relative;">
            <div style="position: absolute; top: 20px; left: 20px;">
                <h3 style="color: #1f2937; font-size: 18px; font-weight: bold; margin-bottom: 8px;">New decor from<br>Mainstays</h3>
                <a href="/category.php?name=home-garden" style="color: #0654ba; font-size: 14px; text-decoration: underline;">Shop now</a>
                <div style="margin-top: 40px; color: #1f2937; font-size: 14px;">From <span style="font-size: 24px; font-weight: bold;">$7.33</span></div>
            </div>
        </div>

        <!-- Scoop Banner -->
        <div class="banner-card" onclick="window.location.href='/category.php?name=fashion'" style="cursor: pointer; background: linear-gradient(135deg, #1f2937, #374151); border-radius: 8px; padding: 20px; min-height: 200px; position: relative; color: white;">
            <div style="position: absolute; top: 20px; left: 20px;">
                <h3 style="font-size: 18px; font-weight: bold; margin-bottom: 8px;">New Scoop, only<br>at Walmart</h3>
                <a href="/category.php?name=fashion" style="color: white; font-size: 14px; text-decoration: underline;">Shop now</a>
            </div>
        </div>

        <!-- Flash Deals -->
        <div class="banner-card" onclick="window.location.href='/deals.php'" style="cursor: pointer; background: linear-gradient(135deg, #84cc16, #65a30d); border-radius: 8px; padding: 20px; min-height: 200px; position: relative; color: white;">
            <div style="position: absolute; top: 20px; left: 20px;">
                <h3 style="font-size: 18px; font-weight: bold; margin-bottom: 8px;">Up to 55% off</h3>
                <a href="/deals.php" style="color: white; font-size: 14px; text-decoration: underline;">Shop now</a>
                <div style="margin-top: 40px; background: #1f2937; color: #fbbf24; padding: 8px 12px; border-radius: 4px; font-weight: bold; display: inline-block;">Flash<br>Deals</div>
            </div>
        </div>

        <!-- Home Catalog -->
        <div class="banner-card" onclick="window.location.href='/category.php?name=home-garden'" style="cursor: pointer; background: linear-gradient(135deg, #fbbf24, #f59e0b); border-radius: 8px; padding: 20px; min-height: 200px; position: relative;">
            <div style="position: absolute; top: 20px; left: 20px;">
                <h3 style="color: #1f2937; font-size: 18px; font-weight: bold; margin-bottom: 8px;">Introducing our<br>fall home catalog</h3>
                <a href="/category.php?name=home-garden" style="color: #0654ba; font-size: 14px; text-decoration: underline;">Shop now</a>
            </div>
        </div>
    </section>

    <!-- Tech Banner -->
    <section class="full-width-banner" style="margin: 30px 0;">
        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
            <div class="banner-card" onclick="window.location.href='/category.php?name=electronics'" style="cursor: pointer; background: linear-gradient(135deg, #fbbf24, #f59e0b); border-radius: 8px; padding: 20px; min-height: 150px;">
                <h3 style="color: #1f2937; font-size: 20px; font-weight: bold; margin-bottom: 8px;">Get top tech in as<br>fast as an hour*</h3>
                <a href="/category.php?name=electronics" style="color: #0654ba; font-size: 14px; text-decoration: underline;">Shop all</a>
            </div>
            <div class="banner-card" onclick="window.location.href='/membership.php'" style="cursor: pointer; background: linear-gradient(135deg, #0654ba, #1e40af); border-radius: 8px; padding: 20px; min-height: 150px; color: white; position: relative;">
                <h3 style="font-size: 24px; font-weight: bold; margin-bottom: 8px;">Members enjoy free grocery<br>delivery</h3>
                <div style="background: #fbbf24; color: #1f2937; padding: 8px 16px; border-radius: 20px; font-weight: bold; display: inline-block; margin-top: 15px;">Start a free trial</div>
                <div style="position: absolute; bottom: 10px; right: 20px; font-size: 12px;">$35 min. T&C apply. One free trial per member.</div>
            </div>
        </div>
    </section>

    <!-- Personalized Recommendations -->
    <section class="personalized-section" style="background: linear-gradient(135deg, #f3f4f6, #e5e7eb); border-radius: 12px; padding: 20px; margin: 30px 0; text-align: center;">
        <div style="margin-bottom: 20px;">
            <span style="font-size: 30px;">🛍️</span>
        </div>
        <h3 style="font-size: 18px; margin-bottom: 8px;">Sign in for personalized recommendations and more!</h3>
        <?php if (!Session::isLoggedIn()): ?>
            <a href="/login.php" class="btn" style="margin-top: 10px;">Sign in or create an account</a>
        <?php else: ?>
            <p style="color: #16a34a; font-weight: bold;">Welcome back, <?php echo htmlspecialchars($current_user['first_name']); ?>!</p>
        <?php endif; ?>
    </section>

    <!-- Featured Products Section -->
    <section class="featured-products-section">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="color: #1f2937; font-size: 20px; font-weight: bold;">Spritz & stay</h2>
            <a href="/products.php" style="color: #0654ba; font-size: 14px;">View all</a>
        </div>
        <div class="products-carousel" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <?php foreach (array_slice($featuredProducts, 0, 6) as $prod): ?>
                <div class="product-card-mini" style="background: white; border-radius: 8px; padding: 15px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.3s ease;">
                    <div class="product-image" style="text-align: center; margin-bottom: 10px;">
                        <img src="<?php echo getProductImageUrl($prod['image_url'] ?? ''); ?>" 
                             alt="<?php echo htmlspecialchars($prod['name']); ?>" 
                             style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px;">
                        <div style="position: absolute; top: 5px; right: 5px; color: #ccc; font-size: 18px;">🤍</div>
                    </div>
                    <div class="product-info">
                        <div style="color: #dc2626; font-weight: bold; font-size: 14px;">Now $<?php echo number_format($prod['price'], 2); ?></div>
                        <div style="color: #6b7280; text-decoration: line-through; font-size: 12px;">$<?php echo number_format($prod['price'] * 1.3, 2); ?></div>
                        <h4 style="font-size: 14px; margin: 8px 0; color: #1f2937;"><?php echo htmlspecialchars(substr($prod['name'], 0, 50)); ?><?php echo strlen($prod['name']) > 50 ? '...' : ''; ?></h4>
                        <div style="display: flex; gap: 5px; margin-top: 10px;">
                            <button style="background: transparent; border: 1px solid #d1d5db; border-radius: 4px; padding: 4px 8px; font-size: 12px; cursor: pointer;">⊕ Add</button>
                            <button style="background: transparent; border: 1px solid #d1d5db; border-radius: 4px; padding: 4px 8px; font-size: 12px; cursor: pointer;">Options</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Styles Section -->
    <section class="styles-section" style="margin: 40px 0;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2 style="color: #1f2937; font-size: 20px; font-weight: bold;">Styles for all your plans</h2>
            <a href="/category.php?name=fashion" style="color: #0654ba; font-size: 14px;">View all</a>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
            <?php foreach (array_slice($trendingProducts, 0, 4) as $prod): ?>
                <div class="style-card" onclick="window.location.href='/product.php?id=<?php echo $prod['id']; ?>'" style="cursor: pointer; position: relative; border-radius: 8px; overflow: hidden; aspect-ratio: 3/4;">
                    <img src="<?php echo getProductImageUrl($prod['image_url'] ?? ''); ?>" 
                         alt="<?php echo htmlspecialchars($prod['name']); ?>" 
                         style="width: 100%; height: 100%; object-fit: cover;">
                    <div style="position: absolute; top: 10px; right: 10px; color: white; font-size: 20px;">🤍</div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Pretty Garden Banner -->
    <section class="garden-banner" style="background: linear-gradient(135deg, #fef3c7, #fcd34d); border-radius: 12px; padding: 30px; margin: 30px 0; display: grid; grid-template-columns: 1fr 1fr; gap: 30px; align-items: center;">
        <div>
            <p style="color: #0654ba; font-size: 14px; margin-bottom: 5px;">Dresses to sweaters</p>
            <h2 style="color: #1f2937; font-size: 32px; font-weight: bold; margin-bottom: 15px;">Just in from<br>PrettyGarden</h2>
            <a href="/brands/prettygarden.php" style="background: white; color: #1f2937; padding: 12px 24px; border-radius: 6px; font-weight: bold; text-decoration: none; display: inline-block;">Shop now</a>
        </div>
        <div style="text-align: center;">
            <?php if (!empty($bannerProducts[3]['image_url'])): ?>
                <img src="<?php echo getProductImageUrl($bannerProducts[3]['image_url']); ?>" 
                     alt="PrettyGarden Fashion" 
                     style="max-width: 200px; border-radius: 8px;">
            <?php endif; ?>
        </div>
    </section>

    <!-- New Arrivals Grid -->
    <?php if (!empty($newArrivals)): ?>
    <section class="new-arrivals-section">
        <h2 class="text-center mb-4" style="color: #1f2937; font-size: 24px; font-weight: bold;">New Arrivals</h2>
        <div class="products-grid">
            <?php foreach ($newArrivals as $prod): ?>
                <div class="product-card" onclick="window.location.href='/product.php?id=<?php echo $prod['id']; ?>'" style="cursor: pointer;">
                    <div class="product-image">
                        <img src="<?php echo getProductImageUrl($prod['image_url'] ?? ''); ?>" alt="<?php echo htmlspecialchars($prod['name']); ?>">
                        <div class="product-badge" style="background: #16a34a;">🆕 New</div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title">
                            <a href="/product.php?id=<?php echo $prod['id']; ?>">
                                <?php echo htmlspecialchars($prod['name']); ?>
                            </a>
                        </h3>
                        <p class="product-vendor">by <?php echo htmlspecialchars($prod['vendor_name'] ?? 'FezaMarket'); ?></p>
                        <p class="product-price"><?php echo formatPrice($prod['price']); ?></p>
                        <div class="product-actions">
                            <button class="btn add-to-cart" data-product-id="<?php echo $prod['id']; ?>">
                                Add to Cart
                            </button>
                            <?php if (Session::isLoggedIn()): ?>
                                <button class="btn btn-outline add-to-wishlist" data-product-id="<?php echo $prod['id']; ?>">
                                    🤍
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<?php includeFooter(); ?>