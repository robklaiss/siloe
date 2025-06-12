<?php

namespace App\Core;

/**
 * View class for rendering views with layouts and data
 */
class View {
    /**
     * The base path for views
     */
    protected static $viewPath;
    
    /**
     * The data available to the view
     */
    protected $data = [];
    
    /**
     * The view file to render
     */
    protected $view;
    
    /**
     * The layout file to use
     */
    protected $layout = 'layouts/main';
    
    /**
     * The section content
     */
    protected $sections = [];
    
    /**
     * The current section being captured
     */
    protected $currentSection;
    
    /**
     * Set the base view path
     */
    public static function setViewPath($path) {
        self::$viewPath = rtrim($path, '/') . '/';
    }
    
    /**
     * Create a new view instance
     */
    public function __construct($view, $data = []) {
        $this->view = $this->normalizeViewName($view);
        $this->data = $data;
    }
    
    /**
     * Normalize the view name
     */
    protected function normalizeViewName($view) {
        // Replace dots with directory separators
        $view = str_replace('.', '/', $view);
        
        // Add .php extension if not present
        if (substr($view, -4) !== '.php') {
            $view .= '.php';
        }
        
        return $view;
    }
    
    /**
     * Set the layout to use
     */
    public function layout($layout) {
        $this->layout = $this->normalizeViewName($layout);
        return $this;
    }
    
    /**
     * Set data for the view
     */
    public function with($key, $value = null) {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }
        
        return $this;
    }
    
    /**
     * Render the view content
     */
    public function renderContent() {
        // Extract the data to variables
        extract($this->data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        $this->includeFile($this->view);
        
        // Get the content and clean the buffer
        $content = ob_get_clean();
        
        // If a layout is set, render it with the content
        if ($this->layout) {
            $layout = $this->layout;
            $this->layout = null; // Prevent infinite loops
            
            // Add the content to the data
            $this->data['content'] = $content;
            
            // Render the layout
            return $this->includeFile($layout, true);
        }
        
        return $content;
    }
    
    /**
     * Include a view file
     */
    protected function includeFile($view, $return = false) {
        $file = self::$viewPath . $view;
        
        if (!file_exists($file)) {
            throw new \Exception("View [{$view}] not found.");
        }
        
        // Extract the data to variables
        extract($this->data);
        
        if ($return) {
            ob_start();
            include $file;
            return ob_get_clean();
        }
        
        include $file;
    }
    
    /**
     * Start a section
     */
    public function section($name) {
        $this->currentSection = $name;
        ob_start();
    }
    
    /**
     * End a section
     */
    public function endSection() {
        if ($this->currentSection) {
            $this->sections[$this->currentSection] = ob_get_clean();
            $this->currentSection = null;
        }
    }
    
    /**
     * Get a section's content
     */
    public function yield($section, $default = '') {
        return $this->sections[$section] ?? $default;
    }
    
    /**
     * Include a partial
     */
    public function include($view, $data = []) {
        $view = $this->normalizeViewName($view);
        $file = self::$viewPath . $view;
        
        if (!file_exists($file)) {
            throw new \Exception("View [{$view}] not found.");
        }
        
        // Extract the data to variables
        extract(array_merge($this->data, $data));
        
        include $file;
    }
    
    /**
     * Create a new view instance (static constructor)
     */
    public static function make($view, $data = []) {
        return new static($view, $data);
    }
    
    /**
     * Render a view (static method)
     */
    public static function renderView($view, $data = []) {
        return static::make($view, $data)->renderContent();
    }
    
    /**
     * Get the rendered content of the view
     */
    public function __toString() {
        return $this->renderContent();
    }
    
    /**
     * Escape HTML special characters
     */
    public function e($value) {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
    }
    
    /**
     * Escape JavaScript string
     */
    public function js($value) {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
    }
}
