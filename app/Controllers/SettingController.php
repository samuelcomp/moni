<?php

namespace App\Controllers;

use App\Models\Setting;

class SettingController extends Controller
{
    private $settingModel;

    public function __construct()
    {
        parent::__construct();
        $this->settingModel = new Setting();
    }

    /**
     * Display the settings page
     */
    public function index()
    {
        $settings = $this->settingModel->getAll();
        
        $this->view('settings/index', [
            'settings' => $settings,
            'title' => 'Settings'
        ]);
    }

    /**
     * Update settings
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process form submission
            foreach ($_POST as $key => $value) {
                if ($key !== 'submit') {
                    $this->settingModel->updateSetting($key, $value);
                }
            }
            
            // Redirect back to settings page with success message
            $_SESSION['success'] = 'Settings updated successfully';
            header('Location: /settings');
            exit;
        }
        
        // If not POST, redirect to index
        header('Location: /settings');
        exit;
    }
} 