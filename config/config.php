<?php
/**
 * Application Configuration
 * 
 * This file contains the main application settings
 */

// App Root
define('APPROOT', dirname(dirname(__FILE__)));

// URL Root (without trailing slash)
// Adjust this based on your deployment environment
define('URLROOT', 'http://' . $_SERVER['HTTP_HOST']);

// Site Name
define('SITENAME', 'GymManager');

// App Version
define('APPVERSION', '1.0.0');

// Default timezone
date_default_timezone_set('UTC');

// Session lifetime in seconds (30 days)
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);

// Session cookie lifetime in seconds (30 days)
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30);
