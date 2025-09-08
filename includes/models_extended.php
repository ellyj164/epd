<?php
/**
 * Additional Models
 * E-Commerce Platform
 */

/**
 * Order Model
 */
class Order extends BaseModel {
    protected $table = 'orders';
    
    public function createOrder($userId, $orderData) {
        try {
            $this->db->beginTransaction();
            
            // Generate order number
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            
            // Insert order
            $stmt = $this->db->prepare("
                INSERT INTO {$this->table} 
                (user_id, order_number, subtotal, tax_amount, shipping_amount, discount_amount, total_amount, 
                 shipping_address, billing_address, payment_method) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $userId,
                $orderNumber,
                $orderData['subtotal'],
                $orderData['tax_amount'],
                $orderData['shipping_amount'],
                $orderData['discount_amount'] ?? 0,
                $orderData['total_amount'],
                $orderData['shipping_address'],
                $orderData['billing_address'],
                $orderData['payment_method']
            ]);
            
            $orderId = $this->db->lastInsertId();
            
            // Add order items
            $cart = new Cart();
            $cartItems = $cart->getCartItems($userId);
            
            foreach ($cartItems as $item) {
                $this->addOrderItem($orderId, $item);
                
                // Decrease product stock
                $product = new Product();
                $product->decreaseStock($item['product_id'], $item['quantity']);
            }
            
            // Clear cart
            $cart->clearCart($userId);
            
            $this->db->commit();
            return $orderId;
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
    
    private function addOrderItem($orderId, $item) {
        $stmt = $this->db->prepare("
            INSERT INTO order_items 
            (order_id, product_id, vendor_id, quantity, unit_price, total_price, product_name, product_sku) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $product = new Product();
        $productData = $product->find($item['product_id']);
        
        return $stmt->execute([
            $orderId,
            $item['product_id'],
            $productData['vendor_id'],
            $item['quantity'],
            $item['price'],
            $item['quantity'] * $item['price'],
            $item['name'],
            $item['sku']
        ]);
    }
    
    public function getUserOrders($userId, $limit = ORDERS_PER_PAGE, $offset = 0) {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT {$limit} OFFSET {$offset}
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function getOrderItems($orderId) {
        $stmt = $this->db->prepare("
            SELECT oi.*, p.name as current_product_name 
            FROM order_items oi 
            LEFT JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }
    
    public function updateStatus($orderId, $status) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET status = ?, updated_at = datetime('now') WHERE id = ?");
        return $stmt->execute([$status, $orderId]);
    }
    
    public function updatePaymentStatus($orderId, $status, $transactionId = null) {
        $sql = "UPDATE {$this->table} SET payment_status = ?, updated_at = datetime('now')";
        $params = [$status];
        
        if ($transactionId) {
            $sql .= ", payment_transaction_id = ?";
            $params[] = $transactionId;
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $orderId;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function getVendorOrders($vendorId, $limit = null, $offset = 0) {
        $sql = "
            SELECT DISTINCT o.*, u.first_name, u.last_name, u.email 
            FROM {$this->table} o 
            JOIN order_items oi ON o.id = oi.order_id 
            JOIN users u ON o.user_id = u.id 
            WHERE oi.vendor_id = ? 
            ORDER BY o.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$vendorId]);
        return $stmt->fetchAll();
    }
    
    public function getOrderStats($vendorId = null) {
        $whereClause = $vendorId ? "WHERE oi.vendor_id = ?" : "";
        $params = $vendorId ? [$vendorId] : [];
        
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(DISTINCT o.id) as total_orders,
                SUM(oi.total_price) as total_revenue,
                AVG(oi.total_price) as average_order_value,
                COUNT(CASE WHEN o.status = 'pending' THEN 1 END) as pending_orders,
                COUNT(CASE WHEN o.status = 'processing' THEN 1 END) as processing_orders,
                COUNT(CASE WHEN o.status = 'shipped' THEN 1 END) as shipped_orders,
                COUNT(CASE WHEN o.status = 'delivered' THEN 1 END) as delivered_orders
            FROM orders o 
            JOIN order_items oi ON o.id = oi.order_id 
            {$whereClause}
        ");
        
        $stmt->execute($params);
        return $stmt->fetch();
    }
}

/**
 * Vendor Model
 */
class Vendor extends BaseModel {
    protected $table = 'vendors';
    
    public function findByUserId($userId) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
    
    public function createVendorApplication($userId, $vendorData) {
        $vendorData['user_id'] = $userId;
        $vendorData['status'] = 'pending';
        return $this->create($vendorData);
    }
    
    public function getApproved($limit = null, $offset = 0) {
        $sql = "
            SELECT v.*, u.username, u.email, u.first_name, u.last_name 
            FROM {$this->table} v 
            JOIN users u ON v.user_id = u.id 
            WHERE v.status = 'approved'
            ORDER BY v.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getPending() {
        $stmt = $this->db->prepare("
            SELECT v.*, u.username, u.email, u.first_name, u.last_name 
            FROM {$this->table} v 
            JOIN users u ON v.user_id = u.id 
            WHERE v.status = 'pending'
            ORDER BY v.created_at ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function approve($vendorId) {
        return $this->update($vendorId, ['status' => 'approved']);
    }
    
    public function suspend($vendorId) {
        return $this->update($vendorId, ['status' => 'suspended']);
    }
    
    public function getVendorStats($vendorId) {
        $product = new Product();
        $order = new Order();
        
        $productCount = $product->count("vendor_id = {$vendorId}");
        $orderStats = $order->getOrderStats($vendorId);
        
        return [
            'product_count' => $productCount,
            'total_orders' => $orderStats['total_orders'] ?? 0,
            'total_revenue' => $orderStats['total_revenue'] ?? 0,
            'average_order_value' => $orderStats['average_order_value'] ?? 0
        ];
    }
}

/**
 * Review Model
 */
class Review extends BaseModel {
    protected $table = 'reviews';
    
    public function addReview($userId, $productId, $rating, $title, $comment, $orderItemId = null) {
        $data = [
            'user_id' => $userId,
            'product_id' => $productId,
            'rating' => $rating,
            'title' => $title,
            'comment' => $comment,
            'order_item_id' => $orderItemId,
            'status' => 'pending'
        ];
        
        return $this->create($data);
    }
    
    public function getUserReviews($userId, $limit = null, $offset = 0) {
        $sql = "
            SELECT r.*, p.name as product_name 
            FROM {$this->table} r 
            JOIN products p ON r.product_id = p.id 
            WHERE r.user_id = ? 
            ORDER BY r.created_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function approve($reviewId) {
        return $this->update($reviewId, ['status' => 'approved']);
    }
    
    public function reject($reviewId) {
        return $this->update($reviewId, ['status' => 'rejected']);
    }
    
    public function getPending() {
        $stmt = $this->db->prepare("
            SELECT r.*, p.name as product_name, u.first_name, u.last_name 
            FROM {$this->table} r 
            JOIN products p ON r.product_id = p.id 
            JOIN users u ON r.user_id = u.id 
            WHERE r.status = 'pending' 
            ORDER BY r.created_at ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

/**
 * Wishlist Model
 */
class Wishlist extends BaseModel {
    protected $table = 'wishlists';
    
    public function addToWishlist($userId, $productId) {
        try {
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (user_id, product_id) VALUES (?, ?)");
            return $stmt->execute([$userId, $productId]);
        } catch (PDOException $e) {
            // Handle duplicate entry
            return false;
        }
    }
    
    public function removeFromWishlist($userId, $productId) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE user_id = ? AND product_id = ?");
        return $stmt->execute([$userId, $productId]);
    }
    
    public function getUserWishlist($userId) {
        $stmt = $this->db->prepare("
            SELECT w.*, p.name, p.price, p.status, pi.image_url 
            FROM {$this->table} w 
            JOIN products p ON w.product_id = p.id 
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
            WHERE w.user_id = ? 
            ORDER BY w.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
    
    public function isInWishlist($userId, $productId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        return $stmt->fetchColumn() > 0;
    }
}

/**
 * AI Recommendations Model
 */
class Recommendation extends BaseModel {
    protected $table = 'recommendation_logs';
    
    public function logActivity($userId, $productId, $activityType, $metadata = []) {
        $stmt = $this->db->prepare("
            INSERT INTO user_activities 
            (user_id, activity_type, product_id, metadata, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $userId,
            $activityType,
            $productId,
            json_encode($metadata),
            getClientIP(),
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }
    
    public function getViewedTogether($productId, $limit = 6) {
        $stmt = $this->db->prepare("
            SELECT p.*, COUNT(*) as view_count, pi.image_url
            FROM user_activities ua1 
            JOIN user_activities ua2 ON ua1.user_id = ua2.user_id 
                AND ua1.product_id != ua2.product_id 
                AND ua1.activity_type = 'view_product' 
                AND ua2.activity_type = 'view_product'
            JOIN products p ON ua2.product_id = p.id 
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
            WHERE ua1.product_id = ? AND p.status = 'active'
            GROUP BY p.id 
            ORDER BY view_count DESC, p.created_at DESC 
            LIMIT {$limit}
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }
    
    public function getPurchasedTogether($productId, $limit = 6) {
        $stmt = $this->db->prepare("
            SELECT p.*, COUNT(*) as purchase_count, pi.image_url
            FROM order_items oi1 
            JOIN order_items oi2 ON oi1.order_id = oi2.order_id 
                AND oi1.product_id != oi2.product_id 
            JOIN products p ON oi2.product_id = p.id 
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
            WHERE oi1.product_id = ? AND p.status = 'active'
            GROUP BY p.id 
            ORDER BY purchase_count DESC, p.created_at DESC 
            LIMIT {$limit}
        ");
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }
    
    public function getTrendingProducts($limit = 8) {
        $stmt = $this->db->prepare("
            SELECT p.*, COUNT(ua.id) as activity_count, pi.image_url
            FROM products p 
            LEFT JOIN user_activities ua ON p.id = ua.product_id 
                AND ua.created_at >= datetime('now', '-7 days')
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
            WHERE p.status = 'active'
            GROUP BY p.id 
            ORDER BY activity_count DESC, p.created_at DESC 
            LIMIT {$limit}
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getPersonalizedRecommendations($userId, $limit = 8) {
        // Get user's purchase history and preferences
        $stmt = $this->db->prepare("
            SELECT p.*, 
                   COUNT(DISTINCT ua.id) as user_interest_score,
                   AVG(r.rating) as avg_rating,
                   pi.image_url
            FROM products p 
            LEFT JOIN user_activities ua ON p.id = ua.product_id 
                AND ua.user_id = ?
            LEFT JOIN reviews r ON p.id = r.product_id AND r.status = 'approved'
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = 1
            WHERE p.status = 'active'
                AND p.id NOT IN (
                    SELECT oi.product_id 
                    FROM order_items oi 
                    JOIN orders o ON oi.order_id = o.id 
                    WHERE o.user_id = ?
                )
            GROUP BY p.id 
            ORDER BY user_interest_score DESC, avg_rating DESC, p.featured DESC 
            LIMIT {$limit}
        ");
        $stmt->execute([$userId, $userId]);
        return $stmt->fetchAll();
    }
    
    public function logRecommendationClick($userId, $productId, $recommendationType) {
        return $this->create([
            'user_id' => $userId,
            'product_id' => $productId,
            'recommendation_type' => $recommendationType,
            'clicked' => 1
        ]);
    }
}

/**
 * Settings Model
 */
class Settings extends BaseModel {
    protected $table = 'settings';
    
    public function getSetting($key, $default = null) {
        $stmt = $this->db->prepare("SELECT setting_value FROM {$this->table} WHERE setting_key = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetchColumn();
        return $result !== false ? $result : $default;
    }
    
    public function setSetting($key, $value, $description = '') {
        // Use SQLite UPSERT syntax
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (setting_key, setting_value, description) 
            VALUES (?, ?, ?) 
            ON CONFLICT(setting_key) DO UPDATE SET 
                setting_value = excluded.setting_value, 
                updated_at = datetime('now')
        ");
        return $stmt->execute([$key, $value, $description]);
    }
    
    public function getAllSettings() {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY setting_key");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    }
}
?>