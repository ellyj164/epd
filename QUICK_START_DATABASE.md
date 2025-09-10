# FezaMarket Database Quick Start Guide

This guide provides the essential steps to set up the complete MariaDB database for the FezaMarket e-commerce platform.

## 🚀 Quick Setup (5 Minutes)

### 1. Prerequisites
```bash
# Ensure you have MariaDB/MySQL running
sudo systemctl status mariadb

# Install PHP dependencies if needed
composer install
```

### 2. Database Setup
```sql
-- Connect to MariaDB as root
mysql -u root -p

-- Create database and user
CREATE DATABASE IF NOT EXISTS ecommerce_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'ecommerce_user'@'localhost' IDENTIFIED BY 'SecurePassword123!';
GRANT ALL PRIVILEGES ON ecommerce_platform.* TO 'ecommerce_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Configure Environment
```bash
# Copy environment file
cp .env.example .env

# Edit database credentials
nano .env
```

Update these lines in `.env`:
```env
DB_HOST=localhost
DB_NAME=ecommerce_platform
DB_USER=ecommerce_user
DB_PASS=SecurePassword123!
```

### 4. Run Complete Setup
```bash
# Fresh installation with all tables and sample data
php scripts/migrate.php --fresh --seed
```

### 5. Verify Installation
```bash
# Check everything is working
php scripts/verify_schema.php --verbose
```

## 📊 What Gets Created

### Database Tables (38 total)
- **Core**: users, categories, vendors, products, addresses
- **Shopping**: cart, orders, order_items, payments, transactions
- **Social**: reviews, wishlists, recommendations, user_follows
- **Live Shopping**: live_streams, chat_messages, stream_viewers
- **Security**: sessions, security_logs, email_tokens, notifications
- **Admin**: system_settings, admin_logs, api_keys, file_uploads

### Sample Data Includes
- Admin user (admin@fezalogistics.com / password123)
- Test customers and vendors
- 8 sample products (electronics)
- Product categories and reviews
- System settings and notifications

## 🛠️ Available Commands

### Migration Commands
```bash
# Run pending migrations only
php scripts/migrate.php

# Fresh install (drops all tables)
php scripts/migrate.php --fresh

# Fresh install with sample data
php scripts/migrate.php --fresh --seed

# Check migration status
php scripts/migrate.php --status

# Rollback last batch
php scripts/migrate.php --rollback
```

### Verification Commands
```bash
# Basic schema verification
php scripts/verify_schema.php

# Detailed verification with report
php scripts/verify_schema.php --verbose --report

# Auto-fix missing indexes
php scripts/verify_schema.php --fix
```

## 🔍 Testing Your Setup

### 1. Database Connection Test
```php
<?php
require_once 'includes/db.php';
try {
    $pdo = db();
    echo "✅ Database connection successful!\n";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "✅ Found {$result['count']} users in database\n";
} catch (Exception $e) {
    echo "❌ Connection failed: " . $e->getMessage() . "\n";
}
?>
```

### 2. Web Interface Test
Access your application in a web browser:
- Homepage should display sample products
- Login with: admin@fezalogistics.com / password123
- All features should work without SQL errors

### 3. Feature Verification
- ✅ Product browsing and search
- ✅ User registration and login  
- ✅ Shopping cart functionality
- ✅ Order processing
- ✅ Product reviews and ratings
- ✅ Wishlist management
- ✅ Live shopping features
- ✅ Admin panel access
- ✅ Vendor management
- ✅ Notification system

## 🔧 Troubleshooting

### Connection Issues
```bash
# Check MariaDB is running
sudo systemctl status mariadb

# Check user permissions
mysql -u ecommerce_user -p -e "SHOW GRANTS FOR CURRENT_USER();"
```

### Migration Failures
```bash
# Check what failed
php scripts/migrate.php --status

# Start fresh if needed
php scripts/migrate.php --fresh --seed
```

### Performance Issues
```bash
# Check for missing indexes
php scripts/verify_schema.php --verbose

# Apply recommended fixes
php scripts/verify_schema.php --fix
```

## 📋 Database Schema Overview

### Key Features Supported
- **Multi-vendor marketplace** with vendor approval workflow
- **Complete e-commerce** with cart, orders, payments, coupons
- **Social features** including reviews, ratings, wishlists, follows  
- **Live shopping** with real-time streams and chat
- **Advanced security** with session management and audit logs
- **Admin tools** for user, product, and system management
- **Notification system** with email and push notifications
- **SEO optimization** with meta fields and URL slugs
- **Analytics tracking** for views, searches, and recommendations
- **File management** system for uploads and media

### Performance Optimizations
- Strategic indexing on all search/filter columns
- Foreign key constraints for data integrity
- Full-text search indexes for product search
- Optimized queries with proper LIMIT/OFFSET
- Efficient session and cache management

### Security Features
- Bcrypt password hashing with configurable cost
- Session security with IP and user agent tracking
- Rate limiting for login attempts
- Security event logging and monitoring
- CSRF protection tokens
- Input validation and sanitization
- Email verification system
- Two-factor authentication support

## 🚀 Production Deployment

For production deployment, also configure:

1. **SSL/TLS encryption** for database connections
2. **Regular automated backups** 
3. **Monitoring and alerting** for database health
4. **Connection pooling** for high traffic
5. **Read replicas** for scaling reads
6. **Security hardening** per MariaDB best practices

## 📚 Additional Resources

- **Full Documentation**: `README_DATABASE.md`
- **Migration System**: `scripts/migrate.php --help`
- **Schema Verification**: `scripts/verify_schema.php --help`
- **Configuration Guide**: Environment variables in `.env`
- **Troubleshooting**: Database logs in `/logs/` directory

---

**🎉 You're all set!** Your FezaMarket database is now ready with complete e-commerce functionality, live shopping features, and robust security. The system includes comprehensive sample data for immediate testing and development.