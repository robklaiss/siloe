<?php

namespace App\Controllers;

class HomeController {
    public function index() {
        echo '<h1>Welcome to Siloe Lunch System</h1>';
        echo '<p><a href="/login">Login</a> | <a href="/register">Register</a></p>';
    }
}
