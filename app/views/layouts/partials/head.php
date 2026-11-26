<?php
$page = $page ?? '';
$pageTitle = $title ?? 'Relova';
$description = $description ?? 'Tempat jual beli pakaian dan sepatu bekas terawat yang masih layak dipakai.';
$themeColor = $page === 'auth' ? '#f3f0e8' : '#f1ede4';
$bodyClass = trim('site-body ' . body_page($page) . (Auth::check() ? ' is-authenticated' : ' is-guest'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="<?= e($themeColor) ?>">
    <meta name="description" content="<?= e($description) ?>">
    <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
    <title><?= e($pageTitle) ?></title>
    <link rel="icon" href="<?= asset('images/branding/favicon.svg') ?>" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&amp;family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&amp;display=swap">
    <link rel="stylesheet" href="<?= asset('css/global.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/navbar.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/hero.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/categories.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/products.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/pages.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/responsive.css') ?>">
    <?php if ($page === 'auth'): ?>
        <link rel="stylesheet" href="<?= asset('css/auth-login.css') ?>">
    <?php endif; ?>
    <script>document.documentElement.classList.add('has-js');</script>
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip-link" href="#main-content">Lompat ke konten utama</a>
