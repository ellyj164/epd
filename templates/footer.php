    </main>
    
    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Shop</h3>
                    <ul>
                        <li><a href="/products.php">All Products</a></li>
                        <li><a href="/categories.php">Categories</a></li>
                        <li><a href="/deals.php">Special Deals</a></li>
                        <li><a href="/new-arrivals.php">New Arrivals</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Account</h3>
                    <ul>
                        <?php if (Session::isLoggedIn()): ?>
                            <li><a href="/account.php">My Account</a></li>
                            <li><a href="/orders.php">Order History</a></li>
                            <li><a href="/wishlist.php">Wishlist</a></li>
                            <li><a href="/logout.php">Logout</a></li>
                        <?php else: ?>
                            <li><a href="/login.php">Login</a></li>
                            <li><a href="/register.php">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Customer Service</h3>
                    <ul>
                        <li><a href="/contact.php">Contact Us</a></li>
                        <li><a href="/help.php">Help Center</a></li>
                        <li><a href="/shipping.php">Shipping Info</a></li>
                        <li><a href="/returns.php">Returns</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Sell With Us</h3>
                    <ul>
                        <li><a href="/vendor/register.php">Become a Vendor</a></li>
                        <li><a href="/vendor/">Vendor Dashboard</a></li>
                        <li><a href="/seller-guidelines.php">Seller Guidelines</a></li>
                        <li><a href="/fees.php">Fees & Pricing</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>About</h3>
                    <ul>
                        <li><a href="/about.php">About Us</a></li>
                        <li><a href="/careers.php">Careers</a></li>
                        <li><a href="/press.php">Press</a></li>
                        <li><a href="/investors.php">Investors</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Connect</h3>
                    <ul>
                        <li><a href="#" target="_blank">Facebook</a></li>
                        <li><a href="#" target="_blank">Twitter</a></li>
                        <li><a href="#" target="_blank">Instagram</a></li>
                        <li><a href="#" target="_blank">LinkedIn</a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.</p>
                <p>
                    <a href="/privacy.php">Privacy Policy</a> | 
                    <a href="/terms.php">Terms of Service</a> | 
                    <a href="/cookies.php">Cookie Policy</a>
                </p>
            </div>
        </div>
    </footer>
    
    <!-- Include additional JavaScript if needed -->
    <script>
        // Initialize tooltips and other interactive elements
        document.addEventListener('DOMContentLoaded', function() {
            // Add any page-specific JavaScript here
        });
    </script>
</body>
</html>