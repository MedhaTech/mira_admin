-- Mira Chat Bot MySQL database and multi-bot tables
CREATE DATABASE IF NOT EXISTS mira_chatbot;
USE mira_chatbot;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

-- Bots table
CREATE TABLE IF NOT EXISTS bots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    logo VARCHAR(255), -- Path to logo image file
    primary_color VARCHAR(20),
    secondary_color VARCHAR(20),
    knowledge_base LONGTEXT, -- Store large knowledge base as text
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Support Queries table
CREATE TABLE IF NOT EXISTS support_queries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bot_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    category VARCHAR(100),
    description TEXT NOT NULL,
    file_path VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bot_id) REFERENCES bots(id) ON DELETE CASCADE
);
