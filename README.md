# Task Master - PHP Web Application

A full-featured task management system with user authentication, CRUD operations, and responsive UI.

![image](https://github.com/user-attachments/assets/e33dd87a-fb89-41a6-857f-d5bd2b17e638)


## 🛠 Technologies Used
- PHP 8.2
- MariaDB
- Apache
- HTML5/CSS3/JavaScript

## 📦 Project Structure
        project
        ├── assets/
        │ ├── style.css
        │ └── script.js
        ├── includes/
        │ ├── connection.php
        │ └── navbar.php
        └── (PHP endpoint files)

## 🚀 Setup on Amazon Lightsail

### Prerequisites
- Amazon Lightsail instance: LAMP(PHP 8)
- WinSCP installed
- PuTTY installed
- Download default SSH key

### Step 1: Connect to Server
1. **PuTTYgen**: Change .pem key into .ppk key
2. **WinSCP**: Upload all project files to `/var/www/html`
3. **PuTTY**: SSH into your instance with your .ppk key:
   ```bash
   sudo systemctl start bitnami.mariadb //Start DB
   cat /home/bitnami/bitnami_application_password //Get your DB password
   sudo /opt/bitnami/mysql/bin/mysql -u root -p //Access to DB
### Step 2: Database Setup
Connect to MariaDB:

        ```bash
        cat /home/bitnami/bitnami_application_password //get your DB password
        sudo /opt/bitnami/mysql/bin/mysql -u root -p //Access to DB
        Create database and user:

Creat User:

        sql
        CREATE USER 'php_user'@'localhost' IDENTIFIED BY 'your_password';
        GRANT ALL PRIVILEGES ON task_manager.* TO 'php_user'@'localhost'; // Get PRIVILEGES
        FLUSH PRIVILEGES;
        EXIT;
Create DB

        CREATE DATABASE task_db;
        CREATE USER 'php_user'@'localhost' IDENTIFIED BY 'y1Z:@4jQKZR4';
        GRANT ALL PRIVILEGES ON task_db.* TO 'php_user'@'localhost';
        FLUSH PRIVILEGES;
        Create tables:

Create Table

        sql
        USE task_db;

        CREATE TABLE users (
          user_id INT AUTO_INCREMENT PRIMARY KEY,
          username VARCHAR(50),
          email VARCHAR(100) UNIQUE,
          password VARCHAR(255)
        );
        
        CREATE TABLE tasks (
          task_id INT AUTO_INCREMENT PRIMARY KEY,
          user_id INT,
          task_name VARCHAR(255),
          description TEXT,
          due_date DATE,
          status ENUM('pending', 'completed') DEFAULT 'pending'
        );

### Step 3: Configure Permissions

        bash
        sudo chmod -R 755 /var/www/html
        sudo chown -R www-data:www-data /var/www/html
        
## ⚙️ Configuration
Edit includes/connection.php with your DB credentials

## 🌐 Access Application
Visit in your browser: http://your-instance-ip/

## 🧪 Testing
Registration: Try creating new account

Task Operations:

Add new tasks

Mark complete/restore

Edit/Delete tasks

Validation:

Test empty submissions

Verify CSRF protection

Check session persistence

## 🔒 Security Notes
Keep the DB password and SSH key Secure

Change default database credentials

Keep PHP updated

Configure .htaccess for:

apache
Options -Indexes
php_flag display_errors off

## 🚨 Troubleshooting
Database Connection Issues:

Verify credentials in connection.php

Check MariaDB service status: sudo systemctl status bitnami.mariadb

File Permissions:

        bash
        sudo chmod 644 /var/www/html/*
        sudo chmod 755 /var/www/html/includes
Error Logs:

        bash
        tail -f /var/log/apache2/error.log

Inspired by Vercel PHP Example - Adapted for Amazon Lightsail deployment
Database credentials shown are for demonstration - always use secure credentials in production
