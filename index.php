<?php

// Parse the URL
$uri = $_SERVER['REQUEST_URI'];
$uri = parse_url($uri, PHP_URL_PATH);

// Device routes
if ($uri === '/devices') {
    $controller = new \App\Controllers\DeviceController();
    $controller->index();
    exit;
} elseif ($uri === '/devices/create') {
    $controller = new \App\Controllers\DeviceController();
    $controller->create();
    exit;
} elseif (preg_match('/^\/devices\/edit\/(\d+)$/', $uri, $matches)) {
    $controller = new \App\Controllers\DeviceController();
    $controller->edit($matches[1]);
    exit;
} elseif (preg_match('/^\/devices\/test-connection\/(\d+)$/', $uri, $matches)) {
    $controller = new \App\Controllers\DeviceController();
    $controller->testConnection($matches[1]);
    exit;
} elseif (preg_match('/^\/devices\/sync\/(\d+)$/', $uri, $matches)) {
    $controller = new \App\Controllers\DeviceController();
    $controller->sync($matches[1]);
    exit;
} elseif ($uri === '/devices/realtime') {
    $controller = new \App\Controllers\DeviceController();
    $controller->realtime();
    exit;
}

// ... other routes ... 