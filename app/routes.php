<?php

/** @var Router $router */

$router->get('/', HomeController::class, 'index');
$router->get('/tentang', HomeController::class, 'about');
$router->get('/studio-3d', HomeController::class, 'studio');

$router->get('/jelajahi', ProductController::class, 'index');
$router->get('/produk/{id}', ProductController::class, 'detail');
$router->post('/produk/{id}/laporan', ProductController::class, 'report');

$router->get('/masuk', AuthController::class, 'showLogin');
$router->post('/masuk', AuthController::class, 'login');
$router->get('/daftar', AuthController::class, 'showRegister');
$router->post('/daftar', AuthController::class, 'register');
$router->post('/keluar', AuthController::class, 'logout');

$router->get('/favorit', WishlistController::class, 'index');
$router->post('/favorit/{id}/toggle', WishlistController::class, 'toggle');

$router->get('/keranjang', CartController::class, 'index');
$router->post('/keranjang/tambah/{id}', CartController::class, 'add');
$router->post('/keranjang/hapus/{id}', CartController::class, 'remove');
$router->post('/keranjang/kosongkan', CartController::class, 'clear');
$router->get('/checkout', CartController::class, 'checkout');
$router->post('/checkout', CartController::class, 'place');

$router->get('/akun', BuyerController::class, 'dashboard');

$router->get('/pesanan', OrderController::class, 'index');
$router->get('/pesanan/{id}', OrderController::class, 'detail');
$router->post('/pesanan/{id}/batal', OrderController::class, 'cancel');
$router->post('/pesanan/{id}/ulasan', OrderController::class, 'review');

$router->get('/profil', ProfileController::class, 'index');
$router->post('/profil', ProfileController::class, 'update');
$router->post('/profil/kata-sandi', ProfileController::class, 'password');

$router->get('/notifikasi', NotificationController::class, 'index');
$router->post('/notifikasi/baca-semua', NotificationController::class, 'markAll');
$router->post('/notifikasi/{id}/baca', NotificationController::class, 'markRead');

$router->get('/chat', ChatController::class, 'index');
$router->get('/chat/{id}', ChatController::class, 'show');
$router->post('/chat/{id}/kirim', ChatController::class, 'send');
$router->post('/chat/mulai', ChatController::class, 'start');

$router->get('/jual', SellerController::class, 'landing');
$router->get('/penjual', SellerController::class, 'dashboard');
$router->get('/penjual/produk', SellerController::class, 'products');
$router->get('/penjual/produk/tambah', SellerController::class, 'create');
$router->post('/penjual/produk', SellerController::class, 'store');
$router->get('/penjual/produk/{id}/edit', SellerController::class, 'edit');
$router->post('/penjual/produk/{id}', SellerController::class, 'update');
$router->post('/penjual/produk/{id}/hapus', SellerController::class, 'delete');
$router->get('/penjual/pesanan', SellerController::class, 'orders');
$router->get('/penjual/pesanan/{id}', SellerController::class, 'orderDetail');
$router->post('/penjual/pesanan/{id}/status', SellerController::class, 'updateStatus');

$router->get('/admin', AdminController::class, 'dashboard');
$router->get('/admin/pengguna', AdminController::class, 'users');
$router->post('/admin/pengguna/{id}/status', AdminController::class, 'userStatus');
$router->get('/admin/produk', AdminController::class, 'products');
$router->post('/admin/produk/{id}/moderasi', AdminController::class, 'moderateProduct');
$router->get('/admin/transaksi', AdminController::class, 'transactions');
$router->get('/admin/transaksi/{id}', AdminController::class, 'transactionDetail');
$router->post('/admin/transaksi/{id}/status', AdminController::class, 'transactionStatus');
$router->get('/admin/pengaturan', AdminController::class, 'settings');
$router->post('/admin/pengaturan', AdminController::class, 'updateSettings');
$router->post('/admin/kategori', AdminController::class, 'createCategory');
$router->post('/admin/kategori/{id}', AdminController::class, 'updateCategory');
$router->post('/admin/laporan/{id}', AdminController::class, 'resolveReport');
