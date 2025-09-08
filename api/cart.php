<?php
/**
 * Cart API Endpoint
 * E-Commerce Platform
 */

require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!Session::isLoggedIn()) {
    errorResponse('Please login to manage your cart', 401);
}

$userId = Session::getUserId();
$cart = new Cart();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? 'list';
        
        switch ($action) {
            case 'count':
                $count = $cart->getCartCount($userId);
                successResponse(['count' => $count]);
                break;
                
            case 'totals':
                $total = $cart->getCartTotal($userId);
                successResponse(['total' => formatPrice($total)]);
                break;
                
            case 'list':
            default:
                $items = $cart->getCartItems($userId);
                $total = $cart->getCartTotal($userId);
                successResponse([
                    'items' => $items,
                    'total' => $total,
                    'count' => array_sum(array_column($items, 'quantity'))
                ]);
                break;
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';
        
        switch ($action) {
            case 'add':
                $productId = (int)($input['product_id'] ?? 0);
                $quantity = (int)($input['quantity'] ?? 1);
                
                if ($productId <= 0 || $quantity <= 0) {
                    errorResponse('Invalid product or quantity');
                }
                
                // Check if product exists and is available
                $product = new Product();
                $productData = $product->find($productId);
                
                if (!$productData || $productData['status'] !== 'active') {
                    errorResponse('Product not available');
                }
                
                if ($productData['stock_quantity'] < $quantity) {
                    errorResponse('Insufficient stock available');
                }
                
                $result = $cart->addItem($userId, $productId, $quantity);
                
                if ($result) {
                    // Log activity
                    $recommendation = new Recommendation();
                    $recommendation->logActivity($userId, $productId, 'add_to_cart');
                    
                    successResponse(['message' => 'Item added to cart']);
                } else {
                    errorResponse('Failed to add item to cart');
                }
                break;
                
            case 'update':
                $productId = (int)($input['product_id'] ?? 0);
                $quantity = (int)($input['quantity'] ?? 1);
                
                if ($productId <= 0) {
                    errorResponse('Invalid product');
                }
                
                $result = $cart->updateQuantity($userId, $productId, $quantity);
                
                if ($result) {
                    successResponse(['message' => 'Cart updated']);
                } else {
                    errorResponse('Failed to update cart');
                }
                break;
                
            case 'remove':
                $productId = (int)($input['product_id'] ?? 0);
                
                if ($productId <= 0) {
                    errorResponse('Invalid product');
                }
                
                $result = $cart->removeItem($userId, $productId);
                
                if ($result) {
                    successResponse(['message' => 'Item removed from cart']);
                } else {
                    errorResponse('Failed to remove item');
                }
                break;
                
            case 'clear':
                $result = $cart->clearCart($userId);
                
                if ($result) {
                    successResponse(['message' => 'Cart cleared']);
                } else {
                    errorResponse('Failed to clear cart');
                }
                break;
                
            default:
                errorResponse('Invalid action');
                break;
        }
    } else {
        errorResponse('Method not allowed', 405);
    }
} catch (Exception $e) {
    Logger::error('Cart API error: ' . $e->getMessage());
    errorResponse('An error occurred', 500);
}
?>