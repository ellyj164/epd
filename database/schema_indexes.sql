-- Additional Performance Indexes for MariaDB
-- To be run after schema.sql for optimal performance

-- Composite index for trending products query performance
-- This supports the query: o.placed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND o.status IN ('paid','shipped','delivered')
ALTER TABLE orders ADD INDEX idx_placed_status (placed_at, status);

-- Composite index for order items with quantity lookups
-- This supports: oi.product_id = p.id and qty calculations
ALTER TABLE order_items ADD INDEX idx_product_qty (product_id, qty);

-- Index for product images primary lookup
-- This supports: pi.product_id = p.id AND pi.is_primary = 1
ALTER TABLE product_images ADD INDEX idx_product_primary (product_id, is_primary);

-- Additional helpful indexes based on common queries
ALTER TABLE products ADD INDEX idx_status_updated (status, updated_at);
ALTER TABLE user_activities ADD INDEX idx_product_created (product_id, created_at);

-- Optimize session and token queries
ALTER TABLE user_sessions ADD INDEX idx_user_expires (user_id, expires_at);
ALTER TABLE email_verification_tokens ADD INDEX idx_token_expires (token, expires_at, used);
ALTER TABLE password_reset_tokens ADD INDEX idx_token_expires (token, expires_at, used);