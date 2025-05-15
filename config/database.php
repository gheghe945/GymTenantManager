<?php
/**
 * Database Configuration
 * 
 * This file contains database connection settings
 */

// Database credentials from environment variables
define('DB_HOST', getenv('PGHOST') ?: 'localhost');
define('DB_USER', getenv('PGUSER') ?: 'gym_user');
define('DB_PASS', getenv('PGPASSWORD') ?: 'gym_password');
define('DB_NAME', getenv('PGDATABASE') ?: 'gym_manager');
define('DB_PORT', getenv('PGPORT') ?: '5432');

// Database charset for PostgreSQL
define('DB_CHARSET', 'UTF8');
