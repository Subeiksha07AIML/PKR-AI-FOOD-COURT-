USE pkr_food_court;

-- Clear old menu items and reset auto-increment
TRUNCATE TABLE menu_items;

-- Insert your exact menu items and prices
INSERT INTO menu_items (item_name, category, price, max_quantity, current_orders) VALUES
-- Beverages
('Coffee', 'Beverages', 20.00, 20, 0),
('Juice', 'Beverages', 20.00, 20, 0),
('Tea', 'Beverages', 15.00, 20, 0),

-- Vegetarian Meals & Rice
('Veg combo', 'Vegetarian', 160.00, 20, 0),
('Veg biryani', 'Vegetarian', 50.00, 20, 0),
('Veg rice', 'Vegetarian', 60.00, 20, 0),
('Tomato rice', 'Vegetarian', 40.00, 20, 0),
('Curd rice', 'Vegetarian', 40.00, 20, 0),

-- Non-Vegetarian Items
('Non veg combo (biryani+rice+65)', 'Non-Vegetarian', 200.00, 20, 0),
('Chicken biryani', 'Non-Vegetarian', 100.00, 20, 0),
('Chicken rice', 'Non-Vegetarian', 80.00, 20, 0),
('Chicken noodles', 'Non-Vegetarian', 90.00, 20, 0),
('Chicken 65 (100 g)', 'Non-Vegetarian', 50.00, 20, 0),

-- Gravy & Thali
('Manchurian Gravy', 'Gravy', 35.00, 20, 0),
('Chicken Gravy', 'Gravy', 50.00, 20, 0),
('Veg/Non-Veg Thali (Roti + Gravy + Rice)', 'Thali', 70.00, 20, 0);
