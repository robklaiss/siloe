<?php

namespace App\Controllers;

use App\Core\Controller;

class HomeController extends Controller {
    public function index() {
        // Use the uploaded Siloe logo by default; can be easily swapped
        $defaultLogo = '684ddb17c8715-siloe-logo.jpg';
        $logoUrl = function_exists('logo_url') ? logo_url($defaultLogo) : ('/uploads/logos/' . $defaultLogo);

        $this->view('home/index', [
            'title' => APP_NAME,
            'logo_url' => $logoUrl,
        ]);
    }
}
