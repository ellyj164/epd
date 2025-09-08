# MySQL/MariaDB Setup Instructions

This e-commerce platform now uses **MySQL/MariaDB** as the primary database. SQLite support has been removed.

## Prerequisites

1. **MySQL/MariaDB Server** - Install and start the database server
   ```bash
   # Ubuntu/Debian
   sudo apt update
   sudo apt install mysql-server
   sudo systemctl start mysql
   
   # CentOS/RHEL
   sudo yum install mariadb-server
   sudo systemctl start mariadb
   
   # macOS (Homebrew)
   brew install mysql
   brew services start mysql
   ```

2. **PHP MySQL Extension** - Ensure PDO MySQL is installed
   ```bash
   # Ubuntu/Debian
   sudo apt install php-mysql
   
   # CentOS/RHEL
   sudo yum install php-mysql
   
   # Check if extension is loaded
   php -m | grep mysql
   ```

## Database Configuration

1. **Update database credentials** in `config/config.php`:
   ```php
   define('DB_HOST', 'localhost');      // Your MySQL host
   define('DB_NAME', 'duns1');          // Database name
   define('DB_USER', 'duns1');          // MySQL username
   define('DB_PASS', '');               // MySQL password
   ```

2. **Create MySQL user and database** (if needed):
   ```sql
   -- Connect to MySQL as root
   mysql -u root -p
   
   -- Create database
   CREATE DATABASE duns1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   
   -- Create user (adjust password as needed)
   CREATE USER 'duns1'@'localhost' IDENTIFIED BY 'your_secure_password';
   
   -- Grant privileges
   GRANT ALL PRIVILEGES ON duns1.* TO 'duns1'@'localhost';
   FLUSH PRIVILEGES;
   ```

## Automated Setup

Run the setup script to create all tables and initial data:

```bash
php setup_database.php
```

This script will:
- ✅ Create the database if it doesn't exist
- ✅ Set up all required tables
- ✅ Insert default categories and settings
- ✅ Create the default admin user

## Default Admin Credentials

After setup, you can login with:
- **Email:** `admin@ecommerce.com`
- **Password:** `admin123`
- **Role:** `admin`

**⚠️ IMPORTANT:** Change the default admin password immediately after first login!

## User Registration & Login

The platform now supports:
- ✅ User registration with validation
- ✅ Secure password hashing (bcrypt)
- ✅ Email verification (configurable)
- ✅ Role-based access (customer, vendor, admin)
- ✅ Session management
- ✅ Activity tracking

## Database Features

### Tables Created:
- `users` - User accounts and profiles
- `user_addresses` - Shipping/billing addresses
- `vendors` - Vendor/seller information
- `categories` - Product categories (hierarchical)
- `products` - Product catalog
- `product_images` - Product image galleries
- `cart` - Shopping cart items
- `orders` & `order_items` - Order management
- `reviews` - Product reviews and ratings
- `wishlists` - User wishlists
- `coupons` - Discount codes
- `user_activities` - User behavior tracking
- `recommendation_logs` - AI recommendation tracking
- `settings` - System configuration

### Key Improvements:
- ✅ **Fixed CHECK constraints** - Activity types are validated
- ✅ **Input validation** - All user inputs are sanitized
- ✅ **Error handling** - Proper PDO exception handling
- ✅ **Performance** - Indexed columns for fast queries
- ✅ **Security** - Foreign key constraints and data integrity

## Troubleshooting

### Connection Issues:
```bash
# Test MySQL connection
mysql -u duns1 -p -h localhost

# Check PHP extensions
php -m | grep -E "(pdo|mysql)"

# Verify configuration
php -r "
echo 'DB_HOST: ' . DB_HOST . PHP_EOL;
echo 'DB_NAME: ' . DB_NAME . PHP_EOL;
echo 'DB_USER: ' . DB_USER . PHP_EOL;
"
```

### Permission Issues:
```sql
-- Grant additional privileges if needed
GRANT CREATE, ALTER, DROP, INSERT, UPDATE, DELETE, SELECT ON duns1.* TO 'duns1'@'localhost';
FLUSH PRIVILEGES;
```

### Reset Database:
```bash
# Drop and recreate database
mysql -u root -p -e "DROP DATABASE IF EXISTS duns1; CREATE DATABASE duns1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run setup again
php setup_database.php
```

## Next Steps

1. **Change admin password** via the admin panel
2. **Configure email settings** in `config/config.php` for notifications
3. **Set up payment gateways** (Stripe, PayPal) for production
4. **Configure SSL certificate** for secure connections
5. **Set up backups** for your MySQL database

## Support

The database is now configured for production use with MySQL/MariaDB. All user registration, login, and data storage functionality should work properly.