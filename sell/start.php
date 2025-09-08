<?php
/**
 * Start Selling - Getting Started Guide
 * E-Commerce Platform
 */

require_once __DIR__ . '/../includes/init.php';

$page_title = 'Start Selling - Getting Started';
includeHeader($page_title);
?>

<div class="container">
    <!-- Header -->
    <div class="start-selling-header">
        <h1>Start Selling on FezaMarket</h1>
        <p>Your step-by-step guide to becoming a successful seller</p>
    </div>

    <!-- Getting Started Steps -->
    <section class="getting-started">
        <h2>Getting Started in 3 Easy Steps</h2>
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <h3>Create Your Account</h3>
                <p>Sign up for free and provide some basic information about yourself and your business.</p>
                <div class="step-actions">
                    <?php if (!Session::isLoggedIn()): ?>
                        <a href="/register.php?seller=1" class="btn">Create Account</a>
                    <?php else: ?>
                        <span class="step-completed">✅ Completed</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="step-card">
                <div class="step-number">2</div>
                <h3>Set Up Your Store</h3>
                <p>Add your business information, payment details, and shipping preferences.</p>
                <div class="step-actions">
                    <?php if (Session::isLoggedIn()): ?>
                        <a href="/vendor/register.php" class="btn">Set Up Store</a>
                    <?php else: ?>
                        <button class="btn" disabled>Sign Up First</button>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="step-card">
                <div class="step-number">3</div>
                <h3>List Your Products</h3>
                <p>Upload photos, write descriptions, and start selling to millions of buyers.</p>
                <div class="step-actions">
                    <a href="/sell/how-to.php" class="btn btn-outline">Learn How</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Selling Requirements -->
    <section class="requirements-section">
        <h2>What You Need to Start Selling</h2>
        <div class="requirements-grid">
            <div class="requirement-card">
                <div class="req-icon">📋</div>
                <h3>Basic Information</h3>
                <ul>
                    <li>Valid email address</li>
                    <li>Phone number</li>
                    <li>Business name (or personal name)</li>
                    <li>Business address</li>
                </ul>
            </div>
            
            <div class="requirement-card">
                <div class="req-icon">🏦</div>
                <h3>Financial Information</h3>
                <ul>
                    <li>Bank account for payments</li>
                    <li>Tax identification number</li>
                    <li>Business license (if applicable)</li>
                    <li>Credit card for account verification</li>
                </ul>
            </div>
            
            <div class="requirement-card">
                <div class="req-icon">📦</div>
                <h3>Products to Sell</h3>
                <ul>
                    <li>High-quality product photos</li>
                    <li>Detailed product descriptions</li>
                    <li>Competitive pricing</li>
                    <li>Inventory to fulfill orders</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Success Tips -->
    <section class="success-tips">
        <h2>Tips for Selling Success</h2>
        <div class="tips-container">
            <div class="tip-category">
                <h3>📸 Great Photos</h3>
                <div class="tip-list">
                    <div class="tip-item">
                        <strong>Use natural lighting:</strong> Take photos in bright, natural light for the best results.
                    </div>
                    <div class="tip-item">
                        <strong>Multiple angles:</strong> Show your product from different perspectives.
                    </div>
                    <div class="tip-item">
                        <strong>Clean backgrounds:</strong> Use plain, uncluttered backgrounds that don't distract.
                    </div>
                </div>
            </div>
            
            <div class="tip-category">
                <h3>📝 Compelling Titles</h3>
                <div class="tip-list">
                    <div class="tip-item">
                        <strong>Be specific:</strong> Include brand, model, size, color, and key features.
                    </div>
                    <div class="tip-item">
                        <strong>Use keywords:</strong> Think about what buyers might search for.
                    </div>
                    <div class="tip-item">
                        <strong>Stay accurate:</strong> Don't exaggerate or mislead in your titles.
                    </div>
                </div>
            </div>
            
            <div class="tip-category">
                <h3>💰 Competitive Pricing</h3>
                <div class="tip-list">
                    <div class="tip-item">
                        <strong>Research competitors:</strong> Check what similar items are selling for.
                    </div>
                    <div class="tip-item">
                        <strong>Factor in fees:</strong> Remember to account for FezaMarket fees and shipping.
                    </div>
                    <div class="tip-item">
                        <strong>Price to sell:</strong> Sometimes lower prices lead to faster sales and better rankings.
                    </div>
                </div>
            </div>
            
            <div class="tip-category">
                <h3>🚚 Fast Shipping</h3>
                <div class="tip-list">
                    <div class="tip-item">
                        <strong>Ship quickly:</strong> Aim to ship within 1 business day when possible.
                    </div>
                    <div class="tip-item">
                        <strong>Provide tracking:</strong> Always include tracking information for buyers.
                    </div>
                    <div class="tip-item">
                        <strong>Package securely:</strong> Ensure items arrive in perfect condition.
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Fee Structure -->
    <section class="fee-structure">
        <h2>Understanding Seller Fees</h2>
        <div class="fees-comparison">
            <div class="fee-plan">
                <h3>Individual Seller</h3>
                <div class="fee-price">Free</div>
                <div class="fee-details">
                    <ul>
                        <li>No monthly subscription</li>
                        <li>3.5% final value fee</li>
                        <li>Up to 50 listings per month</li>
                        <li>Basic seller tools</li>
                    </ul>
                </div>
            </div>
            
            <div class="fee-plan featured">
                <h3>Business Seller</h3>
                <div class="fee-price">$29.99<span>/month</span></div>
                <div class="fee-details">
                    <ul>
                        <li>Monthly subscription fee</li>
                        <li>2.5% final value fee</li>
                        <li>Unlimited listings</li>
                        <li>Advanced seller tools</li>
                        <li>Bulk listing features</li>
                        <li>Priority support</li>
                    </ul>
                </div>
            </div>
        </div>
        <p class="fee-note">
            <strong>Note:</strong> Final value fees are calculated on the total amount paid by the buyer, including shipping and taxes.
        </p>
    </section>

    <!-- Resources -->
    <section class="resources-section">
        <h2>Helpful Resources</h2>
        <div class="resources-grid">
            <a href="/sell/how-to.php" class="resource-card">
                <div class="resource-icon">📚</div>
                <h3>How to Sell Guide</h3>
                <p>Detailed instructions for listing and selling items</p>
            </a>
            
            <a href="/help/selling.php" class="resource-card">
                <div class="resource-icon">❓</div>
                <h3>Seller Help Center</h3>
                <p>Answers to common selling questions and issues</p>
            </a>
            
            <a href="/sell/business.php" class="resource-card">
                <div class="resource-icon">🏢</div>
                <h3>Business Sellers</h3>
                <p>Information for businesses and high-volume sellers</p>
            </a>
            
            <a href="/contact.php" class="resource-card">
                <div class="resource-icon">💬</div>
                <h3>Contact Support</h3>
                <p>Get help from our seller support team</p>
            </a>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="final-cta">
        <div class="cta-content">
            <h2>Ready to Start Your Selling Journey?</h2>
            <p>Join thousands of successful sellers earning money on FezaMarket</p>
            <div class="cta-buttons">
                <?php if (Session::isLoggedIn()): ?>
                    <a href="/vendor/register.php" class="btn btn-large">Set Up My Store</a>
                    <a href="/sell/how-to.php" class="btn btn-outline">Learn More First</a>
                <?php else: ?>
                    <a href="/register.php?seller=1" class="btn btn-large">Get Started Now</a>
                    <a href="/login.php" class="btn btn-outline">I Have an Account</a>
                <?php endif; ?>
            </div>
        </div>
    </section>
</div>

<style>
.start-selling-header {
    text-align: center;
    margin-bottom: 50px;
    padding: 40px 0;
}

.start-selling-header h1 {
    font-size: 36px;
    color: #1f2937;
    margin-bottom: 10px;
}

.start-selling-header p {
    font-size: 18px;
    color: #6b7280;
}

.getting-started h2 {
    text-align: center;
    color: #1f2937;
    margin-bottom: 40px;
}

.steps-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-bottom: 60px;
}

.step-card {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    text-align: center;
    position: relative;
}

.step-number {
    width: 60px;
    height: 60px;
    background: #0654ba;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: bold;
    margin: 0 auto 20px auto;
}

.step-card h3 {
    color: #1f2937;
    margin-bottom: 15px;
}

.step-card p {
    color: #6b7280;
    margin-bottom: 25px;
    line-height: 1.6;
}

.step-completed {
    color: #10b981;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.requirements-section {
    margin-bottom: 60px;
}

.requirements-section h2 {
    text-align: center;
    color: #1f2937;
    margin-bottom: 40px;
}

.requirements-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
}

.requirement-card {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.req-icon {
    font-size: 48px;
    text-align: center;
    margin-bottom: 20px;
}

.requirement-card h3 {
    color: #1f2937;
    text-align: center;
    margin-bottom: 20px;
}

.requirement-card ul {
    list-style: none;
    padding: 0;
}

.requirement-card li {
    padding: 8px 0;
    color: #374151;
    border-bottom: 1px solid #f3f4f6;
    position: relative;
    padding-left: 20px;
}

.requirement-card li:before {
    content: "✓";
    position: absolute;
    left: 0;
    color: #10b981;
    font-weight: bold;
}

.success-tips {
    margin-bottom: 60px;
    background: #f9fafb;
    padding: 40px;
    border-radius: 12px;
}

.success-tips h2 {
    text-align: center;
    color: #1f2937;
    margin-bottom: 40px;
}

.tips-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
}

.tip-category h3 {
    color: #1f2937;
    margin-bottom: 20px;
    font-size: 18px;
}

.tip-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.tip-item {
    background: white;
    padding: 15px;
    border-radius: 6px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.tip-item strong {
    color: #1f2937;
}

.fee-structure {
    margin-bottom: 60px;
}

.fee-structure h2 {
    text-align: center;
    color: #1f2937;
    margin-bottom: 40px;
}

.fees-comparison {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    max-width: 800px;
    margin: 0 auto;
}

.fee-plan {
    background: white;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    text-align: center;
    position: relative;
}

.fee-plan.featured {
    border: 2px solid #0654ba;
    transform: scale(1.05);
}

.fee-plan.featured:before {
    content: "Most Popular";
    position: absolute;
    top: -10px;
    left: 50%;
    transform: translateX(-50%);
    background: #0654ba;
    color: white;
    padding: 5px 15px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
}

.fee-plan h3 {
    color: #1f2937;
    margin-bottom: 15px;
}

.fee-price {
    font-size: 36px;
    font-weight: bold;
    color: #0654ba;
    margin-bottom: 20px;
}

.fee-price span {
    font-size: 16px;
    color: #6b7280;
}

.fee-details ul {
    list-style: none;
    padding: 0;
    text-align: left;
}

.fee-details li {
    padding: 8px 0;
    color: #374151;
    border-bottom: 1px solid #f3f4f6;
    position: relative;
    padding-left: 20px;
}

.fee-details li:before {
    content: "✓";
    position: absolute;
    left: 0;
    color: #10b981;
    font-weight: bold;
}

.fee-note {
    text-align: center;
    color: #6b7280;
    margin-top: 30px;
    font-size: 14px;
}

.resources-section {
    margin-bottom: 60px;
}

.resources-section h2 {
    text-align: center;
    color: #1f2937;
    margin-bottom: 40px;
}

.resources-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
}

.resource-card {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    text-decoration: none;
    color: inherit;
    text-align: center;
    transition: transform 0.3s ease;
}

.resource-card:hover {
    transform: translateY(-3px);
}

.resource-icon {
    font-size: 36px;
    margin-bottom: 15px;
}

.resource-card h3 {
    color: #1f2937;
    margin-bottom: 10px;
}

.resource-card p {
    color: #6b7280;
    font-size: 14px;
}

.final-cta {
    background: linear-gradient(135deg, #0654ba, #1e40af);
    color: white;
    padding: 50px;
    border-radius: 12px;
    text-align: center;
}

.cta-content h2 {
    margin-bottom: 15px;
}

.cta-content p {
    margin-bottom: 30px;
    opacity: 0.9;
}

.cta-buttons {
    display: flex;
    gap: 20px;
    justify-content: center;
}

@media (max-width: 768px) {
    .steps-grid {
        grid-template-columns: 1fr;
    }
    
    .fee-plan.featured {
        transform: none;
    }
    
    .cta-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .tips-container {
        grid-template-columns: 1fr;
    }
}
</style>

<?php includeFooter(); ?>