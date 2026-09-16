CREATE DATABASE IF NOT EXISTS pkr_food_court;
USE pkr_food_court;

CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    max_quantity INT DEFAULT 20,
    current_orders INT DEFAULT 0
);

CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    register_number VARCHAR(50) NOT NULL,
    item_id INT NOT NULL,
    quantity INT DEFAULT 1
);

CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token_number VARCHAR(20) NOT NULL,
    register_number VARCHAR(50) NOT NULL,
    student_name VARCHAR(100) NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    payment_type VARCHAR(50) DEFAULT 'Pending UPI',
    status VARCHAR(50) DEFAULT 'Preparing'
);
