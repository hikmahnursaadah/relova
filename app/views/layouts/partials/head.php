<?php
$page = $page ?? '';
$pageTitle = $title ?? 're:wear';
$description = $description ?? 'Marketplace fashion preloved untuk pakaian dan sepatu berkualitas yang masih layak dipakai.';
$bodyClass = trim('site-body ' . body_page($page) . (Auth::check() ? ' is-authenticated' : ' is-guest'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f1ede4">
    <meta name="description" content="<?= e($description) ?>">
    <meta name="csrf-token" content="<?= e(Csrf::token()) ?>">
    <title><?= e($pageTitle) ?></title>
    <link rel="icon" href="<?= asset('images/branding/favicon.svg') ?>" type="image/svg+xml">
    <link rel="preload" href="<?= asset('fonts/instrument-serif-regular.ttf') ?>" as="font" type="font/ttf" crossorigin>
    <link rel="preload" href="<?= asset('fonts/manrope-400.ttf') ?>" as="font" type="font/ttf" crossorigin>
    <link rel="stylesheet" href="<?= asset('css/global.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/navbar.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/hero.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/categories.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/products.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/dashboard.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/pages.css') ?>">
    <link rel="stylesheet" href="<?= asset('css/responsive.css') ?>">
    <script>document.documentElement.classList.add('has-js');</script>
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip-link" href="#main-content">Lompat ke konten utama</a>
