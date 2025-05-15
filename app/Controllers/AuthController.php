<?php
/**
 * Authentication Controller
 */
class AuthController extends BaseController {
    /**
     * User model instance
     *
     * @var User
     */
    private $userModel;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->userModel = new User();
    }
    
    /**
     * Display login form or process login
     *
     * @return void
     */
    public function login() {
        // If user is already logged in, redirect to dashboard
        if (isLoggedIn()) {
            redirect('dashboard');
        }
        
        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Initialize data with sanitized input
            $data = [
                'email' => filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL),
                'password' => trim($_POST['password'] ?? ''),
                'email_err' => '',
                'password_err' => '',
            ];
            
            // Validate email
            if (empty($data['email'])) {
                $data['email_err'] = 'Please enter email';
            }
            
            // Validate password
            if (empty($data['password'])) {
                $data['password_err'] = 'Please enter password';
            }
            
            // Check for errors
            if (empty($data['email_err']) && empty($data['password_err'])) {
                // Attempt to authenticate user
                $user = $this->userModel->findUserByEmail($data['email']);
                
                // Debug information
                error_log("Login attempt for email: " . $data['email']);
                if ($user) {
                    error_log("User found with ID: " . $user['id']);
                    error_log("Password verification result: " . (password_verify($data['password'], $user['password']) ? 'true' : 'false'));
                    // For debugging - show actual password used in database
                    error_log("Stored password hash: " . $user['password']);
                } else {
                    error_log("No user found with this email");
                }
                
                if ($user && password_verify($data['password'], $user['password'])) {
                    // Create session
                    $this->createUserSession($user);
                    
                    // Check if tenant is already set
                    if (!isset($_SESSION['tenant_id']) && $user['role'] !== 'SUPER_ADMIN') {
                        // If not SUPER_ADMIN, set tenant_id
                        $_SESSION['tenant_id'] = $user['tenant_id'];
                    }
                    
                    redirect('dashboard');
                } else {
                    $data['password_err'] = 'Invalid email or password';
                    $this->render('auth/login', $data);
                }
            } else {
                // Load view with errors
                $this->render('auth/login', $data);
            }
        } else {
            // Initialize empty data
            $data = [
                'email' => '',
                'password' => '',
                'email_err' => '',
                'password_err' => '',
            ];
            
            // Render view
            $this->render('auth/login', $data);
        }
    }
    
    /**
     * Log user out and destroy session
     *
     * @return void
     */
    public function logout() {
        // Unset session variables
        unset($_SESSION['user_id']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_role']);
        unset($_SESSION['tenant_id']);
        
        // Destroy session
        session_destroy();
        
        redirect('login');
    }
    
    /**
     * Create user session
     *
     * @param array $user User data
     * @return void
     */
    private function createUserSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        
        // Set tenant_id for non-SUPER_ADMIN users
        if ($user['role'] !== 'SUPER_ADMIN') {
            $_SESSION['tenant_id'] = $user['tenant_id'];
        }
    }
}
