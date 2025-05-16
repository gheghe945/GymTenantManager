<?php
/**
 * Base Controller class that all controllers extend
 */
class BaseController {
    /**
     * View data container
     *
     * @var array
     */
    protected $data = [];
    
    /**
     * The middleware to apply to specific controller methods
     *
     * @var array
     */
    protected $middleware = [];
    
    /**
     * Database connection
     *
     * @var PDO
     */
    protected $db;
    
    /**
     * Constructor
     */
    public function __construct() {
        // Usa la classe Database che abbiamo definito
        $this->db = Database::getInstance();
    }
    
    /**
     * Get middleware configuration for the controller
     *
     * @return array
     */
    public function getMiddleware() {
        return $this->middleware;
    }
    
    /**
     * Render a view with data
     *
     * @param string $view Path to the view file
     * @param array $data Additional data to pass to the view
     * @return void
     */
    protected function render($view, $data = []) {
        // Merge the data arrays
        $viewData = array_merge($this->data, $data);
        
        // Extract the variables for use in the view
        extract($viewData);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        include APP_ROOT . '/app/Views/' . $view . '.php';
        
        // Get the content of the buffer
        $content = ob_get_clean();
        
        // Include the layout if it exists
        if (file_exists(APP_ROOT . '/app/Views/layouts/main.php')) {
            include APP_ROOT . '/app/Views/layouts/main.php';
        } else {
            echo $content;
        }
    }
    
    /**
     * Respond with JSON data
     *
     * @param mixed $data Data to encode as JSON
     * @param int $statusCode HTTP status code
     * @return void
     */
    protected function json($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }
    
    /**
     * Check if request is an AJAX request
     *
     * @return boolean
     */
    protected function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    
    /**
     * Get request method
     *
     * @return string
     */
    protected function getMethod() {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }
    
    /**
     * Load a view file
     *
     * @param string $view View name
     * @param array $data Data to pass to the view
     * @return void
     */
    protected function view($view, $data = []) {
        // Make the data available to the view
        if (is_array($data)) {
            extract($data);
        }
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        require_once APPROOT . '/Views/' . $view . '.php';
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }
    
    /**
     * Redirect to specified location
     *
     * @param string $path Path to redirect to
     * @return void
     */
    protected function redirect($path) {
        header('Location: ' . URLROOT . $path);
        exit;
    }
    
    /**
     * Set flash message
     *
     * @param string $type Message type (success, error, info)
     * @param string $message The message text
     * @return void
     */
    protected function setFlashMessage($type, $message) {
        if (!isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }
        
        $_SESSION['flash_messages'][$type] = $message;
    }
    
    /**
     * Set validation errors in session
     *
     * @param array $errors Array of errors
     * @return void
     */
    protected function setValidationErrors($errors) {
        $_SESSION['validation_errors'] = $errors;
    }
    
    /**
     * Get validation errors from session
     *
     * @return array
     */
    protected function getValidationErrors() {
        $errors = $_SESSION['validation_errors'] ?? [];
        
        // Clear errors after retrieving
        unset($_SESSION['validation_errors']);
        
        return $errors;
    }
}
