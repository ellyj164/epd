# FezaMarket Database Setup Guide

Complete MariaDB database setup and configuration for the FezaMarket E-Commerce Platform.

## Overview

This guide covers the complete MariaDB database setup, including schema creation, migration system, and configuration for the FezaMarket e-commerce application.

## Prerequisites

- MariaDB 10.3+ or MySQL 8.0+
- PHP 8.0+ with PDO MySQL extension
- Composer (for dependency management)
- Command line access

## Quick Setup

### 1. Database User Creation

Create a dedicated database user for the application:

```sql
-- Connect to MariaDB as root
mysql -u root -p

-- Create database
CREATE DATABASE IF NOT EXISTS ecommerce_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user with secure password
CREATE USER 'ecommerce_user'@'localhost' IDENTIFIED BY 'your_secure_password_here';

-- Grant necessary privileges
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER, INDEX, REFERENCES ON ecommerce_platform.* TO 'ecommerce_user'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;

-- Verify connection
mysql -u ecommerce_user -p ecommerce_platform
```

### 2. Environment Configuration

Copy and configure the environment file:

```bash
cp .env.example .env
```

Update `.env` with your database credentials:

```env
# Database Configuration
DB_HOST=localhost
DB_NAME=ecommerce_platform
DB_USER=ecommerce_user
DB_PASS=your_secure_password_here
DB_CHARSET=utf8mb4

# Application Settings
APP_NAME="FezaMarket"
APP_URL=http://localhost
APP_ENV=development
APP_DEBUG=true

# Security
SECRET_KEY=your-secret-key-change-this-in-production-minimum-32-chars
SESSION_TIMEOUT=3600
BCRYPT_COST=12

# Email Configuration (Optional - for development)
SMTP_HOST=smtp.example.com
SMTP_PORT=587
SMTP_USERNAME=your-email@example.com
SMTP_PASSWORD=your-email-password
FROM_EMAIL=no-reply@yoursite.com
FROM_NAME="FezaMarket"
```

### 3. Run Database Migrations

Execute the migration system to set up all tables:

```bash
# Run all migrations
php scripts/migrate.php

# Or run fresh installation with seed data
php scripts/migrate.php --fresh --seed

# Check migration status
php scripts/migrate.php --status
```

### 4. Verify Database Schema

Run the schema verification to ensure everything is set up correctly:

```bash
# Basic verification
php scripts/verify_schema.php

# Detailed verification with report
php scripts/verify_schema.php --verbose --report

# Apply automatic fixes for missing indexes
php scripts/verify_schema.php --fix
```

## Database Schema Overview

The database consists of the following main components:

### Core Tables

- **users** - User accounts (customers, vendors, admins)
- **categories** - Hierarchical product categories
- **vendors** - Seller/vendor information
- **products** - Product catalog with full e-commerce features
- **product_images** - Product image management

### E-commerce Features

- **cart** - Shopping cart functionality
- **orders** - Order management system
- **order_items** - Individual order line items
- **payment_methods** - Stored payment methods
- **transactions** - Payment transaction logs
- **coupons** - Discount codes and promotions
- **coupon_usage** - Coupon usage tracking

### Social Features

- **reviews** - Product reviews and ratings
- **review_helpfulness** - Review voting system
- **wishlists** - User product wishlists
- **user_follows** - User/vendor following system
- **product_views** - Product view tracking
- **search_queries** - Search analytics
- **product_recommendations** - AI/ML recommendations

### Live Shopping & Communication

- **live_streams** - Live shopping sessions
- **live_stream_products** - Products in live streams
- **live_chat_messages** - Real-time chat during streams
- **stream_viewers** - Stream viewer tracking
- **messages** - Private messaging system

### Notifications & Admin

- **notifications** - User notification system
- **notification_preferences** - User notification settings
- **push_subscriptions** - Web push notifications
- **security_logs** - Security event logging
- **login_attempts** - Rate limiting and security
- **email_tokens** - Email verification tokens
- **email_queue** - Email delivery system
- **system_settings** - Application configuration
- **admin_activity_logs** - Admin action logging
- **api_keys** - API access management
- **file_uploads** - File upload tracking

### System Tables

- **addresses** - User shipping/billing addresses
- **user_sessions** - Session management
- **migrations** - Database version tracking

## Migration System

### Available Commands

```bash
# Run pending migrations
php scripts/migrate.php

# Fresh installation (drops all tables)
php scripts/migrate.php --fresh

# Fresh installation with seed data
php scripts/migrate.php --fresh --seed

# Run only seed data
php scripts/migrate.php --seed

# Check migration status
php scripts/migrate.php --status

# Rollback last migration batch
php scripts/migrate.php --rollback

# Show help
php scripts/migrate.php --help
```

### Migration Files

Located in `/db/sql/` directory:

1. `01_initial_schema.sql` - Core tables (users, products, categories, vendors)
2. `02_shopping_orders.sql` - E-commerce functionality (cart, orders, payments)
3. `03_social_reviews.sql` - Social features (reviews, wishlists, recommendations)
4. `04_live_notifications.sql` - Live shopping and notifications
5. `05_security_admin.sql` - Security, admin, and system tables
6. `99_seed_data.sql` - Sample data for development

### Creating New Migrations

To add new database changes:

1. Create a new `.sql` file in `/db/sql/` with proper numbering
2. Use MariaDB-specific syntax only
3. Include proper indexes and foreign key constraints
4. Test the migration thoroughly
5. Run `php scripts/migrate.php` to apply

## Schema Verification

The verification system ensures database consistency with the codebase:

### Verification Features

- **Table Existence** - Ensures all referenced tables exist
- **Column Verification** - Checks all referenced columns exist
- **Index Analysis** - Suggests missing indexes for performance
- **Foreign Key Validation** - Verifies relationship integrity
- **Unused Element Detection** - Finds potentially unused tables
- **Performance Optimization** - Suggests improvements

### Running Verification

```bash
# Basic verification
php scripts/verify_schema.php

# Verbose output
php scripts/verify_schema.php --verbose

# Generate detailed report
php scripts/verify_schema.php --report

# Apply automatic fixes (indexes only)
php scripts/verify_schema.php --fix

# Full verification with fixes and report
php scripts/verify_schema.php --verbose --fix --report
```

## Performance Optimization

### Recommended Indexes

The schema includes comprehensive indexing for optimal performance:

- Primary keys on all tables
- Foreign key indexes for relationships
- Composite indexes for common query patterns
- Full-text search indexes for product search
- Temporal indexes for date-based queries

### Query Optimization Tips

1. **Use LIMIT** for paginated queries
2. **Avoid SELECT \*** in production code
3. **Use prepared statements** for security and performance
4. **Leverage indexes** for WHERE, ORDER BY, and JOIN clauses
5. **Consider query caching** for frequently accessed data

### Monitoring Performance

```sql
-- Check slow queries
SHOW FULL PROCESSLIST;

-- Analyze table usage
SHOW TABLE STATUS;

-- Index usage statistics
SELECT * FROM INFORMATION_SCHEMA.STATISTICS 
WHERE TABLE_SCHEMA = 'ecommerce_platform';

-- Query performance analysis
EXPLAIN SELECT * FROM products WHERE status = 'active';
```

## Backup and Maintenance

### Regular Backups

```bash
# Full database backup
mysqldump -u ecommerce_user -p ecommerce_platform > backup_$(date +%Y%m%d_%H%M%S).sql

# Schema-only backup
mysqldump -u ecommerce_user -p --no-data ecommerce_platform > schema_backup.sql

# Compressed backup
mysqldump -u ecommerce_user -p ecommerce_platform | gzip > backup_$(date +%Y%m%d_%H%M%S).sql.gz
```

### Restore Database

```bash
# Restore from backup
mysql -u ecommerce_user -p ecommerce_platform < backup_file.sql

# Restore compressed backup
gunzip < backup_file.sql.gz | mysql -u ecommerce_user -p ecommerce_platform
```

### Maintenance Tasks

```sql
-- Optimize tables
OPTIMIZE TABLE products, orders, users;

-- Analyze tables for better query planning
ANALYZE TABLE products, orders, users;

-- Check table integrity
CHECK TABLE products, orders, users;

-- Repair corrupted tables (if needed)
REPAIR TABLE table_name;
```

## Security Configuration

### Database Security Settings

```sql
-- Secure MariaDB installation
mysql_secure_installation

-- Remove anonymous users
DELETE FROM mysql.user WHERE User='';

-- Remove remote root access
DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');

-- Update root password
ALTER USER 'root'@'localhost' IDENTIFIED BY 'new_secure_password';

-- Flush privileges
FLUSH PRIVILEGES;
```

### Application Security

- **Password Hashing** - Uses bcrypt with configurable cost
- **SQL Injection Prevention** - Prepared statements throughout
- **Rate Limiting** - Login attempt tracking and blocking
- **Session Security** - Secure session management
- **Input Validation** - Comprehensive input sanitization
- **CSRF Protection** - Token-based request validation

## Troubleshooting

### Common Issues

#### Connection Errors

```bash
# Check MariaDB service status
systemctl status mariadb

# Restart MariaDB service
sudo systemctl restart mariadb

# Check error logs
sudo tail -f /var/log/mysql/error.log
```

#### Permission Issues

```sql
-- Grant missing permissions
GRANT ALL PRIVILEGES ON ecommerce_platform.* TO 'ecommerce_user'@'localhost';
FLUSH PRIVILEGES;

-- Check user permissions
SHOW GRANTS FOR 'ecommerce_user'@'localhost';
```

#### Migration Failures

```bash
# Check migration status
php scripts/migrate.php --status

# Rollback and retry
php scripts/migrate.php --rollback
php scripts/migrate.php

# Fresh installation if needed
php scripts/migrate.php --fresh --seed
```

#### Performance Issues

```sql
-- Check for slow queries
SHOW FULL PROCESSLIST;

-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;

-- Analyze problematic queries
EXPLAIN your_slow_query_here;
```

### Getting Help

1. Check the application logs in `/logs/` directory
2. Run schema verification for inconsistencies
3. Review MariaDB error logs
4. Check PHP error logs for application issues
5. Verify environment configuration in `.env`

## Production Deployment

### Additional Considerations

- **SSL/TLS** - Enable encrypted connections
- **Connection Pooling** - Configure appropriate pool sizes
- **Monitoring** - Set up database monitoring and alerting
- **Scaling** - Consider read replicas for high traffic
- **Backup Strategy** - Implement automated backup schedules
- **Security Updates** - Keep MariaDB updated regularly

### Configuration Tuning

```ini
# /etc/mysql/mariadb.conf.d/50-server.cnf

[mysqld]
# Connection settings
max_connections = 200
connect_timeout = 60
wait_timeout = 120
max_allowed_packet = 64M

# Performance tuning
innodb_buffer_pool_size = 2G
innodb_log_file_size = 256M
query_cache_type = 1
query_cache_size = 128M

# Security
bind-address = 127.0.0.1
local-infile = 0
```

---

**Note**: This database setup provides a production-ready foundation for the FezaMarket e-commerce platform. All schema elements are optimized for performance and scalability while maintaining data integrity through proper constraints and relationships.