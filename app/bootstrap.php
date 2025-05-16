<?php
/**
 * Bootstrap file that sets up the application environment
 * 
 * This file includes all required configurations and files
 * needed for the application to run
 */

// Error handling
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Session lifetime in seconds (30 days)
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);

// Session cookie lifetime in seconds (30 days)
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30);

// Start session
session_start();

// Load configurations
require_once APP_ROOT . '/config/config.php';
require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/config/translations.php';

// Load core classes
require_once APP_ROOT . '/app/Router.php';

// Load controllers
require_once APP_ROOT . '/app/Controllers/BaseController.php';
require_once APP_ROOT . '/app/Controllers/AuthController.php';
require_once APP_ROOT . '/app/Controllers/DashboardController.php';
require_once APP_ROOT . '/app/Controllers/UserController.php';
require_once APP_ROOT . '/app/Controllers/CourseController.php';
require_once APP_ROOT . '/app/Controllers/MembershipController.php';
require_once APP_ROOT . '/app/Controllers/AttendanceController.php';
require_once APP_ROOT . '/app/Controllers/PaymentController.php';
require_once APP_ROOT . '/app/Controllers/ReportController.php';
require_once APP_ROOT . '/app/Controllers/TenantController.php';
require_once APP_ROOT . '/app/Controllers/CalendarController.php';
require_once APP_ROOT . '/app/Controllers/SettingController.php';
require_once APP_ROOT . '/app/Controllers/InviteController.php';
require_once APP_ROOT . '/app/Controllers/GymSettingsController.php';
require_once APP_ROOT . '/app/Controllers/PasswordResetController.php';
require_once APP_ROOT . '/app/Controllers/GlobalSmtpController.php';

// Load models
require_once APP_ROOT . '/app/Models/BaseModel.php';
require_once APP_ROOT . '/app/Models/User.php';
require_once APP_ROOT . '/app/Models/Tenant.php';
require_once APP_ROOT . '/app/Models/Course.php';
require_once APP_ROOT . '/app/Models/Membership.php';
require_once APP_ROOT . '/app/Models/Attendance.php';
require_once APP_ROOT . '/app/Models/Payment.php';
require_once APP_ROOT . '/app/Models/Invite.php';
require_once APP_ROOT . '/app/Models/SmtpSetting.php';
require_once APP_ROOT . '/app/Models/UserProfile.php';
require_once APP_ROOT . '/app/Models/GymSetting.php';
require_once APP_ROOT . '/app/Models/PasswordReset.php';
require_once APP_ROOT . '/app/Models/GlobalSmtpSetting.php';

// Load middleware
require_once APP_ROOT . '/app/Middleware/MiddlewareInterface.php';
require_once APP_ROOT . '/app/Middleware/TenantMiddleware.php';
require_once APP_ROOT . '/app/Middleware/AuthMiddleware.php';
require_once APP_ROOT . '/app/Middleware/RoleMiddleware.php';

// Helper functions

/**
 * Redirect user to a specific page
 *
 * @param string $page Page to redirect to
 * @return void
 */
function redirect($page) {
    // Check if headers have been sent
    if (!headers_sent()) {
        header('Location: ' . URLROOT . '/' . $page);
        exit;
    } else {
        // JavaScript fallback when headers have been sent
        echo '<script>window.location.href="' . URLROOT . '/' . $page . '";</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . URLROOT . '/' . $page . '"></noscript>';
        exit;
    }
}

/**
 * Sanitize input data
 *
 * @param mixed $data Data to sanitize
 * @return mixed Sanitized data
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Check if user is logged in
 *
 * @return boolean
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get the current tenant ID from session
 *
 * @return int|null
 */
function getCurrentTenantId() {
    return $_SESSION['tenant_id'] ?? null;
}

/**
 * Check if user has a specific role
 *
 * @param string $role Role to check
 * @return boolean
 */
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Flash message to session
 *
 * @param string $name Message name
 * @param string $message Message content
 * @param string $class CSS class for the message
 * @return void
 */
function flash($name = '', $message = '', $class = 'alert alert-success') {
    if (!empty($name)) {
        if (!empty($message) && empty($_SESSION[$name])) {
            $_SESSION[$name] = $message;
            $_SESSION[$name . '_class'] = $class;
        } elseif (empty($message) && !empty($_SESSION[$name])) {
            $class = !empty($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : '';
            echo '<div class="' . $class . '" id="msg-flash">' . $_SESSION[$name] . '</div>';
            unset($_SESSION[$name]);
            unset($_SESSION[$name . '_class']);
        }
    }
}

/**
 * Create database connection using PDO
 *
 * @return PDO
 */
function getDbConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = 'pgsql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';port=' . DB_PORT;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    
    return $pdo;
}

/**
 * Database class with singleton pattern for database connection
 */
class Database {
    private static $instance = null;
    
    /**
     * Get database connection instance
     *
     * @return PDO
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = getDbConnection();
        }
        
        return self::$instance;
    }
}
