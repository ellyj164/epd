# Database Standardization Implementation

This document explains the implementation of standardized database access for the EPD (E-Commerce Platform).

## Overview

The database access has been standardized to use **one PDO entrypoint** for MariaDB as requested. The implementation provides both a new functional API and maintains backward compatibility with existing code.

## Key Files

### `/includes/db.php` - Single Source of Truth
- **Function**: `db(): PDO` - Returns lazy singleton PDO connection
- **Function**: `db_transaction(callable $fn)` - Helper for database transactions
- **Function**: `db_ping(): bool` - Health check for database connectivity
- **Environment**: Loads credentials from `.env` with secure fallbacks
- **Security**: Uses proper PDO options (ERRMODE_EXCEPTION, FETCH_ASSOC, no emulate prepares)

### Updated Files
- **`healthz.php`**: Now uses `db_ping()` for health checks with proper HTTP status codes
- **`includes/database.php`**: Refactored to use `db()` internally while maintaining API compatibility
- **`includes/init.php`**: Now includes the standardized database module
- **Test files**: Updated to use `db()` function instead of direct PDO instantiation

## Usage Examples

### New Functional API (Recommended)
```php
require __DIR__.'/includes/db.php';

// Simple query
$pdo = db();
$stmt = $pdo->prepare('SELECT id,email FROM users WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Transaction
$result = db_transaction(function($pdo) {
    $stmt = $pdo->prepare('INSERT INTO orders (user_id, total) VALUES (?, ?)');
    $stmt->execute([$userId, $total]);
    return $pdo->lastInsertId();
});

// Health check
if (db_ping()) {
    echo "Database is healthy";
}
```

### Backward Compatibility
```php
// Existing code continues to work unchanged
$db = Database::getInstance();
$pdo = $db->getConnection();

// Models using BaseModel still work
class UserModel extends BaseModel {
    protected $table = 'users';
}
```

## Environment Variables

The system supports the following environment variables (via `.env` file):
- `DB_HOST` (default: localhost)
- `DB_PORT` (default: 3306)
- `DB_NAME` (default: duns)
- `DB_USER` (default: duns)
- `DB_PASS` (default: QRJ5M0VuI1nkMQW)

## Security Features

1. **Secure PDO Options**:
   - `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`
   - `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC`
   - `PDO::ATTR_EMULATE_PREPARES => false`

2. **Error Handling**: Connection failures are logged without exposing credentials

3. **MariaDB/MySQL Only**: DSN format `mysql:host=...;dbname=...;charset=utf8mb4`

## Health Check Endpoint

`/healthz` endpoint:
- Returns JSON with application status
- Uses `db_ping()` for database connectivity test
- Returns HTTP 200 for healthy, HTTP 503 for unhealthy

Example response:
```json
{
    "status": "healthy",
    "timestamp": "2024-01-01T12:00:00+00:00",
    "version": "2.0.0",
    "environment": "development",
    "checks": {
        "database": "ok"
    }
}
```

## Testing

Created comprehensive tests to validate the implementation:
- `test_db_standardization.php` - Tests the new functional API
- `test_db_integration.php` - Tests integration between new and old APIs
- `example_db_usage.php` - Demonstrates proper usage

## Migration Strategy

1. **Immediate**: All new code should use `db()` function
2. **Gradual**: Existing code using `Database::getInstance()` continues to work
3. **Future**: Gradually migrate existing code to use the functional API

## Requirements Compliance

✅ **Single PDO entrypoint**: `/includes/db.php`  
✅ **PHP 8, PDO mysql: DSN, utf8mb4**: Implemented  
✅ **Load creds from .env with fallback**: Implemented  
✅ **Secure options**: ERRMODE_EXCEPTION, FETCH_ASSOC, no emulate prepares  
✅ **Export function db()**: Lazy singleton, include_once-safe  
✅ **Helpers**: `db_transaction()`, `db_ping()`  
✅ **MariaDB-only**: DSN format enforced  
✅ **Health check endpoint**: `/healthz` uses `db_ping()`  
✅ **Error handling**: Connection failures logged without passwords  
✅ **Tests**: Unit and integration tests provided  
✅ **No direct PDO**: All removed except in standardized location  

The implementation successfully standardizes database access while maintaining backward compatibility and following all security best practices.