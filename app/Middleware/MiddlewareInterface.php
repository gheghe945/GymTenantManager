<?php
/**
 * Interface that all middleware must implement
 */
interface MiddlewareInterface {
    /**
     * Handle the middleware request
     *
     * @return void
     */
    public function handle();
}
