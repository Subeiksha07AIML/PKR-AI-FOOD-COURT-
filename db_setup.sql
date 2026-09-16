CREATE DATABASE IF NOT EXISTS pkr_food_court;
USE pkr_food_court;

-- Students table for login & registration
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    register_number VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    dob DATE NOT NULL
);

-- Orders table tracked exclusively by admin
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token_number VARCHAR(50) NOT NULL,
    register_number VARCHAR(50) NOT NULL,
    student_name VARCHAR(100) NOT NULL,
    item_name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    payment_type VARCHAR(50) NOT NULL,
    status VARCHAR(50) DEFAULT 'Preparing'
);

-- Menu items table with 20 quantity limit & time windows
CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL, -- 'Daily' or 'Special'
    price DECIMAL(10,2) NOT NULL,
    max_quantity INT DEFAULT 20,
    current_orders INT DEFAULT 0,
    available_from TIME NULL,
    available_to TIME NULL
);

-- Insert initial menu items with your requested pricing
INSERT INTO menu_items (item_name, category, price, max_quantity, current_orders, available_from, available_to) VALUES 
('Hot Tea', 'Daily', 10.00, 20, 0, NULL, NULL),
('Fresh Fruit Juice', 'Daily', 15.00, 20, 0, NULL, NULL),
('Sweet Candy', 'Daily', 5.00, 20, 0, NULL, NULL),
('Ice Cream Variety', 'Daily', 20.00, 20, 0, NULL, NULL),
('Special South Indian Meals', 'Daily', 70.00, 20, 0, NULL, NULL),
('Evening Special Samosa', 'Special', 15.00, 20, 0, '16:00:00', '18:00:00');
