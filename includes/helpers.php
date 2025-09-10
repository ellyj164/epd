<?php
/**
 * URL Helpers and Routing Functions
 * E-Commerce Platform
 */

/**
 * Generate clean URLs for the application
 */
function url($path = '') {
    $baseUrl = rtrim(APP_URL, '/');
    $path = ltrim($path, '/');
    return $baseUrl . '/' . $path;
}

/**
 * Generate URL for seller routes
 */
function sellerUrl($path = '') {
    return url('seller/' . ltrim($path, '/'));
}

/**
 * Generate URL for account routes
 */
function accountUrl($path = '') {
    return url('account/' . ltrim($path, '/'));
}

/**
 * Generate URL for admin routes
 */
function adminUrl($path = '') {
    return url('admin/' . ltrim($path, '/'));
}

/**
 * Check if current page matches given path
 */
function isCurrentPage($path) {
    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $currentPath = rtrim($currentPath, '/');
    $path = '/' . ltrim($path, '/');
    $path = rtrim($path, '/');
    
    return $currentPath === $path || $currentPath === $path . '.php';
}

/**
 * Generate navigation class for active states
 */
function navClass($path, $baseClass = '', $activeClass = 'active') {
    $classes = [$baseClass];
    
    if (isCurrentPage($path)) {
        $classes[] = $activeClass;
    }
    
    return implode(' ', array_filter($classes));
}

/**
 * Store intended URL for post-login redirect
 */
function setIntendedUrl($url = null) {
    if ($url === null) {
        $url = $_SERVER['REQUEST_URI'] ?? '/';
    }
    Session::set('intended_url', $url);
}

/**
 * Get and clear intended URL
 */
function getIntendedUrl($default = '/') {
    $url = Session::get('intended_url', $default);
    Session::remove('intended_url');
    return $url;
}

/**
 * 404 error handler
 */
function show404($message = 'Page Not Found') {
    if (!headers_sent()) {
        http_response_code(404);
    }
    
    $page_title = '404 - Page Not Found';
    includeHeader($page_title);
    ?>
    <div class="container">
        <div class="error-page">
            <div class="error-content">
                <h1 class="error-code">404</h1>
                <h2 class="error-title">Page Not Found</h2>
                <p class="error-message"><?php echo htmlspecialchars($message); ?></p>
                <div class="error-actions">
                    <a href="/" class="btn btn-primary">Go Home</a>
                    <a href="javascript:history.back()" class="btn btn-outline">Go Back</a>
                </div>
            </div>
        </div>
    </div>
    <?php
    includeFooter();
    exit;
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    return Session::getUserRole() === $role || Session::getUserRole() === 'admin';
}

/**
 * Check if user is authenticated
 */
function isAuthenticated() {
    return Session::isLoggedIn();
}

/**
 * Get user avatar URL or generate initials
 */
function getUserAvatar($user, $size = 40) {
    if (!empty($user['avatar'])) {
        return url('uploads/avatars/' . $user['avatar']);
    }
    
    // Generate initials-based avatar
    $initials = strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1));
    $colors = ['#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd', '#8c564b'];
    $colorIndex = crc32($user['email']) % count($colors);
    $color = $colors[$colorIndex];
    
    return "data:image/svg+xml," . urlencode("
        <svg xmlns='http://www.w3.org/2000/svg' width='$size' height='$size' viewBox='0 0 100 100'>
            <rect width='100' height='100' fill='$color'/>
            <text x='50' y='50' font-family='Arial' font-size='40' fill='white' text-anchor='middle' dominant-baseline='central'>$initials</text>
        </svg>
    ");
}

/**
 * Format date for display (only if formatDate doesn't exist)
 */
if (!function_exists('formatDate')) {
    function formatDate($date, $format = 'M j, Y') {
        if (empty($date)) return '';
        
        try {
            $dt = new DateTime($date);
            return $dt->format($format);
        } catch (Exception $e) {
            return $date;
        }
    }
}

/**
 * Format currency for display
 */
function formatCurrency($amount, $currency = 'USD') {
    return '$' . number_format($amount, 2);
}
?>