CREATE TABLE IF NOT EXISTS bostad_tracker_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    secret_code VARCHAR(64) UNIQUE NOT NULL,
    frequency ENUM('daily', 'weekly') DEFAULT 'daily',
    filter JSON NOT NULL DEFAULT ('{}'),
    latest_notified DATE DEFAULT NULL,
    verification_code VARCHAR(64),
    verified BOOLEAN DEFAULT 0
);
