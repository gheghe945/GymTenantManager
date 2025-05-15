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
}
