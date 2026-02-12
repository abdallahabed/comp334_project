-- Database Schema for Freelance Services Marketplace
-- Student ID: 1210802

CREATE DATABASE IF NOT EXISTS web1210802_freelance_db
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; 

USE web1210802_freelance_db; 

CREATE TABLE users ( 
    user_id VARCHAR(10) PRIMARY KEY, 
    first_name VARCHAR(50) NOT NULL, 
    last_name VARCHAR(50) NOT NULL, 
    email VARCHAR(100) NOT NULL UNIQUE, 
    password VARCHAR(255) NOT NULL, 
    phone VARCHAR(10) NOT NULL, 
    country VARCHAR(50) NOT NULL, 
    city VARCHAR(50) NOT NULL, 
    role ENUM('Client', 'Freelancer') NOT NULL, 
    status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active', 
    profile_photo VARCHAR(255), 
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    failed_attempts INT DEFAULT 0,
    last_attempt TIMESTAMP NULL DEFAULT NULL;
); 

CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    category_name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255)
);

INSERT INTO categories (category_name) VALUES 
('Web Development'), 
('Graphic Design'), 
('Writing & Translation'), 
('Digital Marketing'), 
('Video & Animation');

CREATE TABLE services ( 
    service_id VARCHAR(10) PRIMARY KEY, 
    freelancer_id VARCHAR(10) NOT NULL, 
    title VARCHAR(200) NOT NULL, 
    category VARCHAR(100) NOT NULL, 
    subcategory VARCHAR(100) NOT NULL, 
    description TEXT NOT NULL, 
    price DECIMAL(10,2) NOT NULL, 
    delivery_time INT NOT NULL, 
    revisions_included INT NOT NULL, 
    image_1 VARCHAR(255) NOT NULL, 
    image_2 VARCHAR(255), 
    image_3 VARCHAR(255), 
    status ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active', 
    featured_status ENUM('Yes', 'No') NOT NULL DEFAULT 'No', 
    created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    FOREIGN KEY (freelancer_id) REFERENCES users(user_id) ON DELETE CASCADE 
); 

CREATE TABLE orders ( 
    order_id VARCHAR(10) PRIMARY KEY, 
    client_id VARCHAR(10) NOT NULL, 
    freelancer_id VARCHAR(10) NOT NULL, 
    service_id VARCHAR(10) NOT NULL, 
    service_title VARCHAR(200) NOT NULL, 
    price DECIMAL(10,2) NOT NULL, 
    delivery_time INT NOT NULL, 
    revisions_included INT NOT NULL, 
    requirements TEXT NOT NULL, 
    deliverable_notes TEXT, 
    status ENUM('Pending', 'In Progress', 'Delivered', 'Completed', 'Revision Requested', 'Cancelled') NOT NULL DEFAULT 'Pending', 
    payment_method VARCHAR(50) NOT NULL, 
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    expected_delivery DATE NOT NULL, 
    completion_date TIMESTAMP NULL DEFAULT NULL, 
    FOREIGN KEY (client_id) REFERENCES users(user_id) ON DELETE CASCADE, 
    FOREIGN KEY (freelancer_id) REFERENCES users(user_id) ON DELETE CASCADE, 
    FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE RESTRICT 
); 

CREATE TABLE revision_requests ( 
    revision_id INT AUTO_INCREMENT PRIMARY KEY, 
    order_id VARCHAR(10) NOT NULL, 
    revision_notes TEXT NOT NULL, 
    revision_file VARCHAR(255), 
    request_status ENUM('Pending', 'Accepted', 'Rejected') NOT NULL DEFAULT 'Pending', 
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    response_date TIMESTAMP NULL DEFAULT NULL, 
    freelancer_response TEXT, 
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE 
); 

CREATE TABLE file_attachments ( 
    file_id INT AUTO_INCREMENT PRIMARY KEY, 
    order_id VARCHAR(10) NOT NULL, 
    file_path VARCHAR(255) NOT NULL, 
    original_filename VARCHAR(255) NOT NULL, 
    file_size INT NOT NULL, 
    file_type ENUM('requirement', 'deliverable', 'revision') NOT NULL, 
    upload_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP, 
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE 
);





-- ==========================================================
-- some  data to test for use cases

-- INSERT INTO users (user_id, first_name, last_name, email, password, phone, country, city, role, status, profile_photo) VALUES
-- ('1000000001', 'Main', 'Client', 'client@bzu.edu', '$2y$10$8W9K.D1p.m/V6CqY6E1Yue.G/A6L6v6F6', '0599000001', 'Palestine', 'Ramallah', 'Client', 'Active', 'uploads/profiles/1000000001/profile_photo.jpg'),
-- ('1000000002', 'Main', 'Freelancer', 'freelancer@bzu.edu', '$2y$10$8W9K.D1p.m/V6CqY6E1Yue.G/A6L6v6F6', '0599000002', 'Palestine', 'Nablus', 'Freelancer', 'Active', 'uploads/profiles/1000000002/profile_photo.jpg'),
-- ('1000000003', 'John', 'Doe', 'john@bzu.edu', '$2y$10$8W9K.D1p.m/V6CqY6E1Yue.G/A6L6v6F6', '0599000003', 'USA', 'NY', 'Client', 'Active', NULL),
-- ('1000000004', 'Alice', 'Smith', 'alice@bzu.edu', '$2y$10$8W9K.D1p.m/V6CqY6E1Yue.G/A6L6v6F6', '0599000004', 'USA', 'LA', 'Freelancer', 'Active', NULL),
-- ('1000000005', 'Bob', 'Brown', 'bob@bzu.edu', '$2y$10$8W9K.D1p.m/V6CqY6E1Yue.G/A6L6v6F6', '0599000005', 'UK', 'London', 'Freelancer', 'Active', NULL);


-- INSERT INTO services (service_id, freelancer_id, title, category, subcategory, description, price, delivery_time, revisions_included, image_1, featured_status) VALUES
-- ('9000000001', '1000000002', 'Responsive Website', 'Web Development', 'Frontend', 'Custom HTML/CSS design', 300.00, 7, 2, 'uploads/services/9000000001/web_main.jpg', 'Yes'),
-- ('9000000002', '1000000002', 'E-commerce Shop', 'Web Development', 'Fullstack', 'PHP/MySQL Online store', 850.00, 14, 3, 'uploads/services/9000000002/web_main.jpg', 'No'),
-- ('9000000003', '1000000002', 'Portfolio Site', 'Web Development', 'Frontend', 'Personal showcase site', 150.00, 5, 1, 'uploads/services/9000000003/web_main.jpg', 'No'),
-- ('9000000004', '1000000002', 'Bug Fix PHP', 'Web Development', 'Backend', 'Fixing database errors', 50.00, 1, 1, 'uploads/services/9000000004/web_main.jpg', 'No'),
-- ('9000000005', '1000000004', 'Landing Page', 'Web Development', 'Frontend', 'High-converting sales page', 200.00, 3, 2, 'uploads/services/9000000005/web_main.jpg', 'Yes'),
-- ('9000000006', '1000000004', 'API Integration', 'Web Development', 'Backend', 'Connect Stripe/PayPal', 180.00, 4, 1, 'uploads/services/9000000006/web_main.jpg', 'No'),
-- ('9000000007', '1000000004', 'WordPress Setup', 'Web Development', 'CMS', 'Install and config theme', 100.00, 2, 2, 'uploads/services/9000000007/web_main.jpg', 'No'),
-- ('9000000008', '1000000004', 'Web Audit', 'Web Development', 'Optimization', 'Performance and speed check', 75.00, 2, 1, 'uploads/services/9000000008/web_main.jpg', 'No'),
-- ('9000000009', '1000000004', 'Professional Logo', 'Graphic Design', 'Branding', 'Vector logo for company', 120.00, 3, 3, 'uploads/services/9000000009/design_main.jpg', 'Yes'),
-- ('9000000010', '1000000004', 'Business Cards', 'Graphic Design', 'Print', 'Double sided design', 45.00, 2, 2, 'uploads/services/9000000010/design_main.jpg', 'No'),
-- ('9000000011', '1000000004', 'Flyer Design', 'Graphic Design', 'Print', 'Promotional event flyer', 60.00, 2, 2, 'uploads/services/9000000011/design_main.jpg', 'No'),
-- ('9000000012', '1000000004', 'Banner Design', 'Graphic Design', 'Digital', 'Web/Social media banners', 40.00, 2, 1, 'uploads/services/9000000012/design_main.jpg', 'No'),
-- ('9000000013', '1000000005', 'UI/UX Mockup', 'Graphic Design', 'UI', 'Mobile app visual design', 400.00, 10, 5, 'uploads/services/9000000013/design_main.jpg', 'Yes'),
-- ('9000000014', '1000000005', 'Illustration', 'Graphic Design', 'Digital', 'Custom digital drawing', 150.00, 5, 2, 'uploads/services/9000000014/design_main.jpg', 'No'),
-- ('9000000015', '1000000005', 'Infographics', 'Graphic Design', 'Digital', 'Visual data graphics', 90.00, 4, 2, 'uploads/services/9000000015/design_main.jpg', 'No'),
-- ('9000000016', '1000000005', 'Photo Editing', 'Graphic Design', 'Editing', 'Retouch and background removal', 30.00, 1, 3, 'uploads/services/9000000016/design_main.jpg', 'No'),
-- ('9000000017', '1000000005', 'SEO Optimization', 'Digital Marketing', 'SEO', 'Google ranking improvement', 250.00, 10, 1, 'uploads/services/9000000017/marketing_main.jpg', 'Yes'),
-- ('9000000018', '1000000005', 'FB Ad Campaign', 'Digital Marketing', 'SMM', 'Setup targeted Facebook ads', 150.00, 3, 2, 'uploads/services/9000000018/marketing_main.jpg', 'No'),
-- ('9000000019', '1000000005', 'Google Ads', 'Digital Marketing', 'SEM', 'Manage PPC campaigns', 200.00, 5, 2, 'uploads/services/9000000019/marketing_main.jpg', 'No'),
-- ('9000000020', '1000000005', 'Email Marketing', 'Digital Marketing', 'Email', 'Newsletter setup and copy', 110.00, 4, 1, 'uploads/services/9000000020/marketing_main.jpg', 'No'),
-- ('9000000021', '1000000002', 'SMM Strategy', 'Digital Marketing', 'SMM', 'Full monthly strategy', 500.00, 30, 0, 'uploads/services/9000000021/marketing_main.jpg', 'Yes'),
-- ('9000000022', '1000000002', 'Keywords Research', 'Digital Marketing', 'SEO', 'Find top niches', 80.00, 2, 1, 'uploads/services/9000000022/marketing_main.jpg', 'No'),
-- ('9000000023', '1000000002', 'Content Plan', 'Digital Marketing', 'Strategy', '90-day content calendar', 120.00, 5, 2, 'uploads/services/9000000023/marketing_main.jpg', 'No'),
-- ('9000000024', '1000000002', 'Affiliate Audit', 'Digital Marketing', 'Consulting', 'Check affiliate links', 140.00, 3, 1, 'uploads/services/9000000024/marketing_main.jpg', 'No'),
-- ('9000000025', '1000000002', 'Blog Post Writing', 'Writing & Translation', 'Content', '500 words SEO article', 35.00, 2, 2, 'uploads/services/9000000025/writing_main.jpg', 'Yes'),
-- ('9000000026', '1000000002', 'Technical Doc', 'Writing & Translation', 'Technical', 'Product user guides', 160.00, 7, 3, 'uploads/services/9000000026/writing_main.jpg', 'No'),
-- ('9000000027', '1000000002', 'Arabic Translation', 'Writing & Translation', 'Translation', 'EN to AR manual translation', 25.00, 1, 5, 'uploads/services/9000000027/writing_main.jpg', 'No'),
-- ('9000000028', '1000000002', 'Proofreading', 'Writing & Translation', 'Editing', 'Grammar and style check', 20.00, 1, 999, 'uploads/services/9000000028/writing_main.jpg', 'No'),
-- ('9000000029', '1000000005', 'Sales Copy', 'Writing & Translation', 'Marketing', 'Ad copy that converts', 200.00, 4, 3, 'uploads/services/9000000029/writing_main.jpg', 'Yes'),
-- ('9000000030', '1000000005', 'Resume Edit', 'Writing & Translation', 'Professional', 'Modern CV makeover', 65.00, 3, 2, 'uploads/services/9000000030/writing_main.jpg', 'No'),
-- ('9000000031', '1000000005', 'Scriptwriting', 'Writing & Translation', 'Creative', 'YouTube or video scripts', 100.00, 3, 2, 'uploads/services/9000000031/writing_main.jpg', 'No'),
-- ('9000000032', '1000000005', 'Ghostwriting', 'Writing & Translation', 'Creative', 'Short story or book', 450.00, 20, 4, 'uploads/services/9000000032/writing_main.jpg', 'No'),
-- ('9000000033', '1000000005', 'Video Editing', 'Video & Animation', 'Editing', 'YouTube/Vlog pro editing', 120.00, 4, 2, 'uploads/services/9000000033/video_main.jpg', 'Yes'),
-- ('9000000034', '1000000005', '2D Animation', 'Video & Animation', 'Animation', 'Animated explainer video', 350.00, 10, 3, 'uploads/services/9000000034/video_main.jpg', 'Yes'),
-- ('9000000035', '1000000005', 'Logo Reveal', 'Video & Animation', 'Animation', 'Cinematic logo intro', 75.00, 2, 2, 'uploads/services/9000000035/video_main.jpg', 'No'),
-- ('9000000036', '1000000004', 'Color Grading', 'Video & Animation', 'Editing', 'Hollywood look for footage', 90.00, 2, 1, 'uploads/services/9000000036/video_main.jpg', 'No'),
-- ('9000000037', '1000000004', 'Subtitle Add', 'Video & Animation', 'Editing', 'Timed captions for video', 40.00, 1, 2, 'uploads/services/9000000037/video_main.jpg', 'No'),
-- ('9000000038', '1000000004', 'Green Screen', 'Video & Animation', 'VFX', 'Chroma keying service', 140.00, 3, 1, 'uploads/services/9000000038/video_main.jpg', 'No'),
-- ('9000000039', '1000000004', 'Short Ads', 'Video & Animation', 'Marketing', 'TikTik/Reels editing', 80.00, 2, 2, 'uploads/services/9000000039/video_main.jpg', 'No'),
-- ('9000000040', '1000000004', 'Intro Outro', 'Video & Animation', 'Animation', 'Start/End screens', 55.00, 2, 2, 'uploads/services/9000000040/video_main.jpg', 'No');

-- INSERT INTO orders (order_id, client_id, freelancer_id, service_id, service_title, price, delivery_time, revisions_included, requirements, status, payment_method, expected_delivery) VALUES
-- ('5000000001', '1000000001', '1000000002', '9000000001', 'Responsive Website', 300.00, 7, 2, 'I need a corporate site for my clinic.', 'In Progress', 'Credit Card', DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY)),
-- ('5000000002', '1000000003', '1000000004', '9000000009', 'Professional Logo', 120.00, 3, 3, 'Logo for a new coffee shop.', 'Pending', 'PayPal', DATE_ADD(CURRENT_DATE, INTERVAL 3 DAY));









-- INSERT INTO orders (order_id, client_id, freelancer_id, service_id, service_title, price, delivery_time, revisions_included, requirements, status, payment_method, expected_delivery, completion_date) VALUES
-- ('5000000003', '1000000001', '1000000004', '9000000013', 'UI/UX Mockup', 400.00, 10, 5, 'Design for a food delivery app.', 'Completed', 'Credit Card', '2025-12-15', '2025-12-14 10:00:00'),
-- ('5000000004', '1000000003', '1000000005', '9000000017', 'SEO Optimization', 250.00, 10, 1, 'Optimize my jewelry website.', 'Delivered', 'PayPal', DATE_ADD(CURRENT_DATE, INTERVAL 2 DAY), NULL),
-- ('5000000005', '1000000001', '1000000002', '9000000025', 'Blog Post Writing', 35.00, 2, 2, 'Article about AI in 2026.', 'Revision Requested', 'Credit Card', DATE_ADD(CURRENT_DATE, INTERVAL 1 DAY), NULL),
-- ('5000000006', '1000000003', '1000000005', '9000000033', 'Video Editing', 120.00, 4, 2, 'Edit my travel vlog.', 'In Progress', 'PayPal', DATE_ADD(CURRENT_DATE, INTERVAL 4 DAY), NULL),
-- ('5000000007', '1000000001', '1000000002', '9000000001', 'Responsive Website', 300.00, 7, 2, 'Simple 3-page site.', 'Cancelled', 'Credit Card', '2025-11-20', NULL),
-- ('5000000008', '1000000003', '1000000004', '9000000005', 'Landing Page', 200.00, 3, 2, 'One page for my book launch.', 'Completed', 'Credit Card', '2025-12-20', '2025-12-19 15:30:00'),
-- ('5000000009', '1000000001', '1000000005', '9000000034', '2D Animation', 350.00, 10, 3, 'Explainer for my startup.', 'Pending', 'PayPal', DATE_ADD(CURRENT_DATE, INTERVAL 10 DAY), NULL),
-- ('5000000010', '1000000003', '1000000002', '9000000021', 'SMM Strategy', 500.00, 30, 0, 'Full Instagram growth plan.', 'In Progress', 'Credit Card', DATE_ADD(CURRENT_DATE, INTERVAL 25 DAY), NULL),
-- ('5000000011', '1000000001', '1000000004', '9000000009', 'Professional Logo', 120.00, 3, 3, 'Minimalist tech logo.', 'Delivered', 'PayPal', DATE_ADD(CURRENT_DATE, INTERVAL 1 DAY), NULL),
-- ('5000000012', '1000000003', '1000000005', '9000000029', 'Sales Copy', 200.00, 4, 3, 'Write a sales email sequence.', 'In Progress', 'Credit Card', DATE_ADD(CURRENT_DATE, INTERVAL 3 DAY), NULL);



-- INSERT INTO revision_requests (order_id, revision_notes, request_status, freelancer_response) VALUES
-- ('5000000005', 'Please make the tone more professional and add a section about ethics.', 'Pending', NULL),
-- ('5000000003', 'Can you change the primary color from blue to green?', 'Accepted', 'Sure, I will update the mockups now.'),
-- ('5000000011', 'The logo font is too thin, please try a bolder version.', 'Pending', NULL),
-- ('5000000008', 'The mobile view needs more padding.', 'Accepted', 'Fixed and re-uploaded.'),
-- ('5000000004', 'The keyword density is a bit low for the main page.', 'Rejected', 'The current density is optimized for 2026 Google standards.');