-- OTP Attempts Table for Rate Limiting
-- E-Commerce Platform Security Enhancement

CREATE TABLE IF NOT EXISTS otp_attempts (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) DEFAULT NULL,
    email VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    attempted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    success TINYINT(1) NOT NULL DEFAULT 0,
    token_type ENUM('email_verification', 'password_reset', 'email_change', 'two_fa_backup') NOT NULL DEFAULT 'email_verification',
    INDEX idx_user_id (user_id),
    INDEX idx_email (email),
    INDEX idx_ip_address (ip_address),
    INDEX idx_attempted_at (attempted_at),
    INDEX idx_token_type (token_type),
    PRIMARY KEY (id),
    CONSTRAINT fk_otp_attempts_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;