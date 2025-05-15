<?php
/**
 * Authentication Middleware
 */
class AuthMiddleware implements MiddlewareInterface {
    /**
     * Handle the middleware request
     * 
     * Checks if user is logged in, redirects to login page if not
     *
     * @return void
     */
    public function handle() {
        // Check if user is logged in
        if (!isLoggedIn()) {
            // Redirect to login page
            redirect('login');
        }
    }
}
