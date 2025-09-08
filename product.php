<?php
/**
 * Single Product Page - FezaMarket E-Commerce Platform
 * Displays detailed product information with eBay-style layout
 */

require_once __DIR__ . '/includes/init.php';

// Get product ID from URL
$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$productId) {
    header('Location: /products.php');
    exit;
}

// Initialize models
$product = new Product();
$recommendation = new Recommendation();

// Get product details
$productData = $product->findWithVendor($productId);

if (!$productData) {
    header('Location: /products.php');
    exit;
}

// Get product images
$images = $product->getImages($productId);
$primaryImage = !empty($images) ? $images[0]['image_url'] : $productData['image_url'];

// Get product reviews
$reviews = $product->getReviews($productId, 5);
$rating = $product->getAverageRating($productId);

// Get related products
$relatedProducts = $product->findByCategory($productData['category_id'], 4);
$relatedProducts = array_filter($relatedProducts, function($p) use ($productId) {
    return $p['id'] != $productId;
});

// Get recommendations
$recommendedProducts = $recommendation->getRecommendations(Session::getUserId(), $productId, 'viewed_together', 6);

// Log user activity if logged in
if (Session::isLoggedIn()) {
    $recommendation->logActivity(Session::getUserId(), $productId, 'view_product');
}

// Set page title
$page_title = htmlspecialchars($productData['name']);

// Include header
includeHeader($page_title);
?>

<div class="container">
    <!-- Breadcrumb -->
    <nav class="breadcrumb" style="margin: 20px 0; font-size: 14px; color: #767676;">
        <a href="/" style="color: #0654ba;">FezaMarket</a> &gt; 
        <a href="/category.php?id=<?php echo $productData['category_id']; ?>" style="color: #0654ba;"><?php echo htmlspecialchars($productData['category_name']); ?></a> &gt; 
        <span><?php echo htmlspecialchars($productData['name']); ?></span>
    </nav>

    <div class="product-details-layout" style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px;">
        <!-- Product Images Section -->
        <div class="product-images">
            <div class="main-image" style="margin-bottom: 20px;">
                <img id="main-product-image" 
                     src="<?php echo getProductImageUrl($primaryImage); ?>" 
                     alt="<?php echo htmlspecialchars($productData['name']); ?>"
                     style="width: 100%; max-width: 500px; border: 1px solid #e1e1e1; border-radius: 8px;">
            </div>
            
            <?php if (!empty($images) && count($images) > 1): ?>
            <div class="image-thumbnails" style="display: flex; gap: 10px; flex-wrap: wrap;">
                <?php foreach ($images as $img): ?>
                    <img src="<?php echo getProductImageUrl($img['image_url']); ?>" 
                         alt="<?php echo htmlspecialchars($img['alt_text']); ?>"
                         onclick="changeMainImage('<?php echo getProductImageUrl($img['image_url']); ?>')"
                         style="width: 60px; height: 60px; object-fit: cover; border: 1px solid #e1e1e1; border-radius: 4px; cursor: pointer;">
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Product Information Section -->
        <div class="product-info">
            <h1 style="font-size: 24px; font-weight: 600; color: #1f2937; margin-bottom: 10px;">
                <?php echo htmlspecialchars($productData['name']); ?>
            </h1>

            <!-- Product Rating -->
            <?php if ($rating['review_count'] > 0): ?>
            <div class="product-rating" style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
                <div class="stars" style="color: #ffc107;">
                    <?php 
                    $avgRating = round($rating['avg_rating'], 1);
                    for ($i = 1; $i <= 5; $i++) {
                        echo $i <= $avgRating ? '★' : '☆';
                    }
                    ?>
                </div>
                <span style="color: #0654ba; font-size: 14px;"><?php echo $rating['review_count']; ?> review<?php echo $rating['review_count'] != 1 ? 's' : ''; ?></span>
            </div>
            <?php endif; ?>

            <!-- Price Section -->
            <div class="price-section" style="margin-bottom: 24px;">
                <div class="current-price" style="font-size: 28px; font-weight: bold; color: #dc2626;">
                    US $<?php echo number_format($productData['price'], 2); ?>
                </div>
                <?php if ($productData['compare_price'] && $productData['compare_price'] > $productData['price']): ?>
                <div class="compare-price" style="color: #767676; text-decoration: line-through; font-size: 16px;">
                    Was: US $<?php echo number_format($productData['compare_price'], 2); ?>
                </div>
                <div class="savings" style="color: #16a34a; font-size: 14px; font-weight: bold;">
                    You save: US $<?php echo number_format($productData['compare_price'] - $productData['price'], 2); ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Shipping Info -->
            <div class="shipping-info" style="background: #f7f7f7; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                <div style="font-size: 14px; margin-bottom: 8px;">
                    <strong>🚚 Shipping:</strong> FREE Standard Shipping
                </div>
                <div style="font-size: 14px; margin-bottom: 8px;">
                    <strong>📦 Delivery:</strong> Estimated between Mon, Dec 18 and Thu, Dec 21
                </div>
                <div style="font-size: 14px;">
                    <strong>🔄 Returns:</strong> 30-day return guarantee
                </div>
            </div>

            <!-- Quantity and Buy Section -->
            <div class="purchase-section" style="background: white; border: 1px solid #e1e1e1; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                <div class="quantity-section" style="margin-bottom: 16px;">
                    <label style="display: block; font-weight: bold; margin-bottom: 8px;">Quantity:</label>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <select id="quantity" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <?php for ($i = 1; $i <= min(10, $productData['stock_quantity']); $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                        <span style="color: #767676; font-size: 14px;">
                            (<?php echo $productData['stock_quantity']; ?> available)
                        </span>
                    </div>
                </div>

                <div class="action-buttons" style="display: flex; flex-direction: column; gap: 12px;">
                    <button id="add-to-cart-btn" 
                            class="btn" 
                            data-product-id="<?php echo $productId; ?>"
                            style="background: #0654ba; color: white; padding: 12px 24px; font-size: 16px; font-weight: bold; border-radius: 24px;">
                        Add to cart
                    </button>
                    
                    <button class="btn btn-outline" 
                            style="border: 1px solid #0654ba; color: #0654ba; background: white; padding: 12px 24px; font-size: 16px; border-radius: 24px;">
                        Buy It Now
                    </button>
                    
                    <?php if (Session::isLoggedIn()): ?>
                    <button class="btn btn-outline add-to-wishlist" 
                            data-product-id="<?php echo $productId; ?>"
                            style="border: 1px solid #767676; color: #767676; background: white; padding: 8px 16px; font-size: 14px; border-radius: 20px;">
                        ♡ Add to Watchlist
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Seller Information -->
            <div class="seller-info" style="background: #f7f7f7; padding: 16px; border-radius: 8px;">
                <h3 style="font-size: 16px; font-weight: bold; margin-bottom: 8px;">Seller information</h3>
                <div style="font-size: 14px; margin-bottom: 4px;">
                    <strong><?php echo htmlspecialchars($productData['vendor_name'] ?? 'FezaMarket'); ?></strong>
                </div>
                <div style="color: #767676; font-size: 14px; margin-bottom: 8px;">
                    (<?php echo rand(98, 100); ?>% positive feedback)
                </div>
                <div style="font-size: 14px;">
                    <a href="/seller/<?php echo $productData['vendor_id']; ?>" style="color: #0654ba;">Contact seller</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Description Tabs -->
    <div class="product-tabs" style="margin-bottom: 40px;">
        <div class="tab-navigation" style="border-bottom: 1px solid #e1e1e1; margin-bottom: 20px;">
            <button class="tab-btn active" data-tab="description" style="padding: 12px 24px; border: none; background: none; font-size: 16px; font-weight: bold; color: #0654ba; border-bottom: 2px solid #0654ba;">
                Description
            </button>
            <button class="tab-btn" data-tab="shipping" style="padding: 12px 24px; border: none; background: none; font-size: 16px; color: #767676;">
                Shipping and payments
            </button>
            <button class="tab-btn" data-tab="returns" style="padding: 12px 24px; border: none; background: none; font-size: 16px; color: #767676;">
                Return policy
            </button>
        </div>

        <div class="tab-content">
            <div id="description-tab" class="tab-pane active" style="display: block;">
                <div class="description-content" style="font-size: 14px; line-height: 1.6; color: #333;">
                    <?php echo nl2br(htmlspecialchars($productData['description'])); ?>
                    
                    <?php if (!empty($productData['specifications'])): ?>
                    <h4 style="margin-top: 24px; margin-bottom: 12px; font-weight: bold;">Specifications:</h4>
                    <div><?php echo nl2br(htmlspecialchars($productData['specifications'])); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div id="shipping-tab" class="tab-pane" style="display: none;">
                <div style="font-size: 14px; line-height: 1.6;">
                    <h4 style="margin-bottom: 12px;">Shipping Options</h4>
                    <ul style="margin-left: 20px;">
                        <li>Standard Shipping: FREE (5-7 business days)</li>
                        <li>Express Shipping: $9.99 (2-3 business days)</li>
                        <li>Overnight Shipping: $24.99 (1 business day)</li>
                    </ul>
                    
                    <h4 style="margin-top: 20px; margin-bottom: 12px;">Payment Methods</h4>
                    <p>We accept all major credit cards, PayPal, and bank transfers.</p>
                </div>
            </div>
            
            <div id="returns-tab" class="tab-pane" style="display: none;">
                <div style="font-size: 14px; line-height: 1.6;">
                    <h4 style="margin-bottom: 12px;">Return Policy</h4>
                    <p>30-day return guarantee. Item must be returned in original condition.</p>
                    <ul style="margin-left: 20px; margin-top: 12px;">
                        <li>Buyer pays return shipping</li>
                        <li>Refund within 3-5 business days after receipt</li>
                        <li>Original packaging required</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Reviews -->
    <?php if (!empty($reviews)): ?>
    <section class="reviews-section" style="margin-bottom: 40px;">
        <h2 style="font-size: 20px; font-weight: bold; margin-bottom: 20px;">Customer Reviews</h2>
        
        <?php foreach ($reviews as $review): ?>
        <div class="review-item" style="border-bottom: 1px solid #e1e1e1; padding: 20px 0;">
            <div class="review-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <div class="reviewer-info">
                    <strong><?php echo htmlspecialchars($review['first_name']); ?></strong>
                    <div class="review-rating" style="color: #ffc107; margin-top: 4px;">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php echo $i <= $review['rating'] ? '★' : '☆'; ?>
                        <?php endfor; ?>
                    </div>
                </div>
                <div class="review-date" style="color: #767676; font-size: 14px;">
                    <?php echo date('M j, Y', strtotime($review['created_at'])); ?>
                </div>
            </div>
            <div class="review-content" style="color: #333; font-size: 14px; line-height: 1.6;">
                <?php echo htmlspecialchars($review['comment']); ?>
            </div>
        </div>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
    <section class="related-products" style="margin-bottom: 40px;">
        <h2 style="font-size: 20px; font-weight: bold; margin-bottom: 20px;">Similar sponsored items</h2>
        
        <div class="products-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <?php foreach ($relatedProducts as $related): ?>
            <div class="product-card" onclick="window.location.href='/product.php?id=<?php echo $related['id']; ?>'" style="cursor: pointer; background: white; border: 1px solid #e1e1e1; border-radius: 8px; padding: 16px; transition: transform 0.3s ease;">
                <div class="product-image" style="text-align: center; margin-bottom: 12px;">
                    <img src="<?php echo getProductImageUrl($related['image_url'] ?? ''); ?>" 
                         alt="<?php echo htmlspecialchars($related['name']); ?>" 
                         style="width: 100%; max-width: 150px; height: 150px; object-fit: cover; border-radius: 4px;">
                </div>
                <div class="product-info">
                    <h4 style="font-size: 14px; margin-bottom: 8px; color: #1f2937;">
                        <?php echo htmlspecialchars(substr($related['name'], 0, 60)); ?><?php echo strlen($related['name']) > 60 ? '...' : ''; ?>
                    </h4>
                    <div class="price" style="font-size: 16px; font-weight: bold; color: #dc2626;">
                        $<?php echo number_format($related['price'], 2); ?>
                    </div>
                    <?php if (!empty($related['shipping_free'])): ?>
                    <div style="color: #16a34a; font-size: 12px; margin-top: 4px;">Free shipping</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<script>
// Change main product image
function changeMainImage(imageUrl) {
    document.getElementById('main-product-image').src = imageUrl;
}

// Tab functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetTab = this.dataset.tab;
            
            // Remove active class from all tabs and panes
            tabBtns.forEach(b => {
                b.classList.remove('active');
                b.style.color = '#767676';
                b.style.borderBottom = 'none';
            });
            tabPanes.forEach(p => {
                p.classList.remove('active');
                p.style.display = 'none';
            });
            
            // Add active class to clicked tab and corresponding pane
            this.classList.add('active');
            this.style.color = '#0654ba';
            this.style.borderBottom = '2px solid #0654ba';
            document.getElementById(targetTab + '-tab').style.display = 'block';
        });
    });
    
    // Add to cart functionality
    const addToCartBtn = document.getElementById('add-to-cart-btn');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const quantity = document.getElementById('quantity').value;
            
            fetch('/api/cart/add.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId,
                    quantity: parseInt(quantity)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Product added to cart successfully!');
                    // Update cart count if element exists
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount && data.cart_count) {
                        cartCount.textContent = data.cart_count;
                        cartCount.style.display = 'flex';
                    }
                } else {
                    alert('Error adding product to cart: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding product to cart');
            });
        });
    }
});
</script>

<?php includeFooter(); ?>