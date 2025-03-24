CREATE TABLE IF NOT EXISTS bostad_tracker_subscribers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    secret_code VARCHAR(64) UNIQUE NOT NULL,
    frequency ENUM('daily', 'weekly') DEFAULT 'daily',
    latest_notified DATETIME DEFAULT CURRENT_TIMESTAMP,
    verification_code VARCHAR(64),
    verified BOOLEAN DEFAULT 0,

    -- Areas
    city_areas TEXT DEFAULT NULL,
    kommuns TEXT DEFAULT NULL,

    -- Numbers
    max_floor INT DEFAULT NULL,
    min_floor INT DEFAULT NULL,
    min_num_rooms INT DEFAULT 0,
    max_num_rooms INT DEFAULT NULL,
    min_size_sqm INT DEFAULT 0,
    max_size_sqm INT DEFAULT NULL,
    min_rent INT DEFAULT 0,
    max_rent INT DEFAULT NULL,

    -- Excluding types
    require_balcony BOOLEAN DEFAULT FALSE,
    require_elevator BOOLEAN DEFAULT FALSE,
    require_new_production BOOLEAN DEFAULT FALSE,

    -- Including types
    include_youth BOOLEAN DEFAULT FALSE,
    include_student BOOLEAN DEFAULT FALSE,
    include_senior BOOLEAN DEFAULT FALSE,
    include_short_lease BOOLEAN DEFAULT TRUE,
    include_regular BOOLEAN DEFAULT TRUE
);


CREATE TABLE IF NOT EXISTS bostad_tracker_apartments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    internal_id VARCHAR(255) NOT NULL,
    city_area VARCHAR(255) NOT NULL,
    address VARCHAR(255) NOT NULL,
    kommun VARCHAR(255) NOT NULL,
    floor INT DEFAULT NULL,
    num_rooms INT DEFAULT NULL,
    size_sqm INT DEFAULT NULL,
    rent INT DEFAULT NULL,
    latitude DECIMAL(9,6) DEFAULT NULL,
    longitude DECIMAL(9,6) DEFAULT NULL,
    url VARCHAR(2083) NOT NULL,

    has_balcony BOOLEAN DEFAULT FALSE,
    has_elevator BOOLEAN DEFAULT FALSE,
    new_production BOOLEAN DEFAULT FALSE,
    youth BOOLEAN DEFAULT FALSE,
    student BOOLEAN DEFAULT FALSE,
    senior BOOLEAN DEFAULT FALSE,
    short_lease BOOLEAN DEFAULT FALSE,
    regular BOOLEAN DEFAULT FALSE,
    apartment_type VARCHAR(255) NOT NULL,

    first_seen DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    published_date DATE NOT NULL,
    last_date DATE NOT NULL
);
