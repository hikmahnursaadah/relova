<?php

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
$basePath = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
if ($basePath === '.' || $basePath === '/') {
    $basePath = '';
}

return [
    'name' => 're:wear',
    'tagline' => 'Fashion deserves another life.',
    'description' => 'Marketplace fashion preloved pilihan untuk pakaian dan sepatu yang masih layak dipakai.',
    'base_url' => getenv('APP_URL') ?: $basePath,
    'timezone' => 'Asia/Jakarta',
    'upload_dir' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads',
    'max_upload_size' => 4 * 1024 * 1024,
    'allowed_image_types' => ['image/jpeg', 'image/png', 'image/webp'],
];
