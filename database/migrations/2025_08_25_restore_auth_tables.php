<?php

// Create users table if it doesn't exist
$sql = <<<SQL
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'user',
    company_id INTEGER,
    remember_token TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
SQL;

// Create password_resets table
$sql .= <<<SQL

CREATE TABLE IF NOT EXISTS password_resets (
    email TEXT NOT NULL,
    token TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
SQL;

// Create sessions table
$sql .= <<<SQL

CREATE TABLE IF NOT EXISTS sessions (
    id TEXT PRIMARY KEY,
    user_id INTEGER,
    ip_address TEXT,
    user_agent TEXT,
    payload TEXT,
    last_activity INTEGER
);
SQL;

// Create an admin user if none exists
$sql .= <<<SQL

INSERT OR IGNORE INTO users (name, email, password, role) 
VALUES (
    'Admin User', 
    'admin@example.com', 
    '\$2y$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  // password is 'password'
    'admin'
);
SQL;

// Execute the SQL
$db = new PDO('sqlite:' . __DIR__ . '/../../database/siloe.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->exec('PRAGMA foreign_keys = ON;');
$db->exec($sql);

echo "Database tables created successfully!\n";
