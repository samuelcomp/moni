<?php

namespace App\Controllers;

class Controller
{
    protected $db;

    public function __construct()
    {
        global $db;
        $this->db = $db;
    }

    /**
     * Load and render a view
     *
     * @param string $view The view file to load
     * @param array $data Data to pass to the view
     * @return void
     */
    protected function view($view, $data = [])
    {
        // Extract data to make variables available in the view
        extract($data);
        
        // Define the full path to the view file
        $viewPath = __DIR__ . '/../../resources/views/' . $view . '.php';
        
        // Check if the view file exists
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            die("View {$view} not found");
        }
    }

    /**
     * Redirect to a specific URL
     *
     * @param string $url The URL to redirect to
     * @return void
     */
    protected function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }
} 