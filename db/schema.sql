-- Create admin_users table
CREATE TABLE IF NOT EXISTS admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create password_reset_tokens table
CREATE TABLE IF NOT EXISTS password_reset_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  admin_id INT NOT NULL,
  token VARCHAR(255) UNIQUE NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE CASCADE
);

-- Create index for faster token lookup
CREATE INDEX idx_token ON password_reset_tokens(token);
CREATE INDEX idx_email ON admin_users(email);

-- Create website_settings table for website identity & configuration
CREATE TABLE IF NOT EXISTS website_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) UNIQUE NOT NULL,
  setting_value LONGTEXT NOT NULL,
  setting_type VARCHAR(50) DEFAULT 'text',
  description VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create articles table
CREATE TABLE IF NOT EXISTS articles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) UNIQUE NOT NULL,
  content LONGTEXT NOT NULL,
  excerpt VARCHAR(500),
  featured_image VARCHAR(500),
  category VARCHAR(100),
  author_id INT,
  status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
  views INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  published_at DATETIME,
  FOREIGN KEY (author_id) REFERENCES admin_users(id) ON DELETE SET NULL,
  KEY idx_status (status),
  KEY idx_category (category),
  KEY idx_published (published_at)
);

-- Create events table
CREATE TABLE IF NOT EXISTS events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) UNIQUE NOT NULL,
  description LONGTEXT NOT NULL,
  short_description VARCHAR(500),
  event_date DATETIME NOT NULL,
  event_end_date DATETIME,
  location VARCHAR(500),
  latitude DECIMAL(10, 8),
  longitude DECIMAL(11, 8),
  featured_image VARCHAR(500),
  category VARCHAR(100),
  organizer VARCHAR(255),
  status ENUM('draft', 'published', 'cancelled', 'completed') DEFAULT 'draft',
  max_participants INT,
  participants_count INT DEFAULT 0,
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  published_at DATETIME,
  FOREIGN KEY (created_by) REFERENCES admin_users(id) ON DELETE SET NULL,
  KEY idx_status (status),
  KEY idx_date (event_date),
  KEY idx_category (category),
  KEY idx_published (published_at)
);

-- Create images table to track uploaded images
CREATE TABLE IF NOT EXISTS images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  filename VARCHAR(500) NOT NULL,
  original_filename VARCHAR(500) NOT NULL,
  file_path VARCHAR(500) NOT NULL,
  file_size INT,
  mime_type VARCHAR(100),
  width INT,
  height INT,
  alt_text VARCHAR(255),
  image_type ENUM('article', 'event', 'gallery', 'general') DEFAULT 'general',
  related_id INT,
  uploaded_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (uploaded_by) REFERENCES admin_users(id) ON DELETE SET NULL,
  KEY idx_type (image_type),
  KEY idx_related (related_id)
);

-- Create gallery table for image collections
CREATE TABLE IF NOT EXISTS gallery (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) UNIQUE NOT NULL,
  description LONGTEXT,
  category VARCHAR(100),
  is_featured BOOLEAN DEFAULT FALSE,
  status ENUM('draft', 'published') DEFAULT 'draft',
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES admin_users(id) ON DELETE SET NULL,
  KEY idx_status (status),
  KEY idx_featured (is_featured)
);

-- Create gallery_images junction table
CREATE TABLE IF NOT EXISTS gallery_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  gallery_id INT NOT NULL,
  image_id INT NOT NULL,
  display_order INT DEFAULT 0,
  FOREIGN KEY (gallery_id) REFERENCES gallery(id) ON DELETE CASCADE,
  FOREIGN KEY (image_id) REFERENCES images(id) ON DELETE CASCADE,
  UNIQUE KEY unique_gallery_image (gallery_id, image_id)
);

-- Create contacts/submissions table
CREATE TABLE IF NOT EXISTS contact_submissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(20),
  subject VARCHAR(255),
  message LONGTEXT NOT NULL,
  is_read BOOLEAN DEFAULT FALSE,
  is_replied BOOLEAN DEFAULT FALSE,
  category VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_read (is_read),
  KEY idx_category (category)
);

-- Insert default website settings
INSERT INTO website_settings (setting_key, setting_value, setting_type, description) VALUES
('site_name', 'Culture Bridge Indonesia', 'text', 'Nama website'),
('site_tagline', 'Jembatan Budaya Indonesia', 'text', 'Tagline website'),
('site_description', 'Platform budaya untuk menghubungkan warisan budaya Indonesia', 'text', 'Deskripsi website'),
('site_logo', '', 'image', 'Logo website'),
('site_favicon', '', 'image', 'Favicon website'),
('contact_email', 'contact@culturebridgeindonesia.my.id', 'text', 'Email kontak utama'),
('contact_phone', '', 'text', 'Nomor telepon'),
('contact_address', '', 'text', 'Alamat fisik'),
('social_facebook', '', 'url', 'Link Facebook'),
('social_instagram', '', 'url', 'Link Instagram'),
('social_twitter', '', 'url', 'Link Twitter'),
('social_youtube', '', 'url', 'Link YouTube'),
('about_us', '', 'richtext', 'Tentang kami'),
('footer_text', '© 2026 Culture Bridge Indonesia', 'text', 'Teks footer'),
('maintenance_mode', '0', 'boolean', 'Mode maintenance ON/OFF');

-- Create security_events table for tracking security incidents
CREATE TABLE IF NOT EXISTS security_events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_type VARCHAR(100) NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  user_agent VARCHAR(500),
  url VARCHAR(500),
  details LONGTEXT,
  user_id INT,
  severity ENUM('low', 'medium', 'high', 'critical') DEFAULT 'low',
  is_resolved BOOLEAN DEFAULT FALSE,
  resolved_by INT,
  resolved_at DATETIME,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE SET NULL,
  FOREIGN KEY (resolved_by) REFERENCES admin_users(id) ON DELETE SET NULL,
  KEY idx_event_type (event_type),
  KEY idx_ip_address (ip_address),
  KEY idx_created_at (created_at),
  KEY idx_severity (severity)
);

-- Create login_attempts table for tracking login activity
CREATE TABLE IF NOT EXISTS login_attempts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(255) NOT NULL,
  email VARCHAR(255),
  ip_address VARCHAR(45) NOT NULL,
  success BOOLEAN DEFAULT FALSE,
  failure_reason VARCHAR(255),
  user_agent VARCHAR(500),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY idx_username (username),
  KEY idx_ip_address (ip_address),
  KEY idx_created_at (created_at)
);

-- Create audit_logs table for comprehensive auditing
CREATE TABLE IF NOT EXISTS audit_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  action VARCHAR(100) NOT NULL,
  table_name VARCHAR(100),
  record_id INT,
  user_id INT,
  old_values JSON,
  new_values JSON,
  ip_address VARCHAR(45),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE SET NULL,
  KEY idx_action (action),
  KEY idx_table_name (table_name),
  KEY idx_user_id (user_id),
  KEY idx_created_at (created_at)
);

-- Create api_logs table for API request tracking
CREATE TABLE IF NOT EXISTS api_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  endpoint VARCHAR(500) NOT NULL,
  method VARCHAR(10) NOT NULL,
  ip_address VARCHAR(45),
  user_id INT,
  request_data JSON,
  response_code INT,
  response_time INT,
  error_message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE SET NULL,
  KEY idx_endpoint (endpoint),
  KEY idx_method (method),
  KEY idx_created_at (created_at)
);

-- Create ip_blacklist table
CREATE TABLE IF NOT EXISTS ip_blacklist (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ip_address VARCHAR(45) UNIQUE NOT NULL,
  reason TEXT,
  added_by INT,
  added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  expires_at DATETIME,
  is_permanent BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (added_by) REFERENCES admin_users(id) ON DELETE SET NULL,
  KEY idx_ip_address (ip_address),
  KEY idx_expires_at (expires_at)
);
