<?php

class SellerController extends Controller
{
    public function landing(): void
    {
        if (Auth::check() && Auth::isSeller()) {
            $this->redirect('/penjual');
        }
        $this->render('seller/landing', [
            'title' => 'Mulai Menjual — Relova',
            'page' => 'sell',
            'products' => (new ProductModel())->featured(3),
        ], 'main');
    }

    public function dashboard(): void
    {
        Auth::requireLogin('Silakan masuk untuk membuka dasbor penjual.');
        if (Auth::isAdmin()) {
            $this->redirect('/admin');
        }
        $productModel = new ProductModel();
        $orderModel = new OrderModel();
        $reviewModel = new ReviewModel();
        $this->render('seller/dashboard', [
            'title' => 'Dasbor Penjual — Relova',
            'page' => 'seller-dashboard',
            'productStats' => $productModel->sellerStats(Auth::id()),
            'orderStats' => $orderModel->sellerStats(Auth::id()),
            'recentOrders' => $orderModel->recentForSeller(Auth::id(), 5),
            'reviews' => $reviewModel->forSeller(Auth::id(), 3),
            'reviewStats' => $reviewModel->statsForSeller(Auth::id()),
            'products' => $productModel->forSeller(Auth::id()),
        ], 'seller');
    }

    public function products(): void
    {
        Auth::requireLogin('Silakan masuk untuk mengelola produk.');
        if (Auth::isAdmin()) {
            $this->redirect('/admin/produk');
        }
        $status = (string) query('status');
        $this->render('seller/products', [
            'title' => 'Produk Saya — Relova',
            'page' => 'seller-products',
            'products' => (new ProductModel())->forSeller(Auth::id(), ['status' => $status]),
            'statusFilter' => $status,
            'productStats' => (new ProductModel())->sellerStats(Auth::id()),
        ], 'seller');
    }

    public function create(): void
    {
        Auth::requireLogin('Silakan masuk untuk menambahkan produk.');
        if (Auth::isAdmin()) {
            $this->redirect('/admin/produk');
        }
        $this->render('seller/form', [
            'title' => 'Tambah Produk — Relova',
            'page' => 'seller-product-form',
            'product' => null,
            'images' => [],
            'categories' => (new CategoryModel())->forCatalog(),
        ], 'seller');
    }

    public function store(): void
    {
        Auth::requireLogin('Silakan masuk untuk menambahkan produk.');
        if (Auth::isAdmin()) {
            $this->redirect('/admin/produk');
        }
        [$data, $errors] = $this->validateProduct();
        $images = [];
        if (!$errors) {
            [$images, $imageErrors] = $this->processImages($_FILES['images'] ?? null, true);
            $errors = array_merge($errors, $imageErrors);
        }

        if ($errors) {
            foreach ($errors as $error) {
                Session::flash('error', $error);
            }
            Session::old($data);
            $this->redirect('/penjual/produk/tambah');
        }

        $productModel = new ProductModel();
        $productId = $productModel->create(Auth::id(), $data, $images);
        if (Auth::user()['role'] === 'pembeli') {
            (new UserModel())->setRole(Auth::id(), 'penjual');
            Auth::refresh();
        }
        Session::flash('success', 'Produk berhasil diterbitkan. Pantau pesan dan pesanan dari pembeli.');
        $this->redirect('/produk/' . $productId);
    }

    public function edit(string $id): void
    {
        Auth::requireLogin('Silakan masuk untuk mengedit produk.');
        $productModel = new ProductModel();
        $product = $productModel->ownProduct((int) $id, Auth::id());
        if (!$product) {
            Session::flash('error', 'Produk tidak ditemukan atau bukan milikmu.');
            $this->redirect('/penjual/produk');
        }
        $this->render('seller/form', [
            'title' => 'Ubah Produk — Relova',
            'page' => 'seller-product-form',
            'product' => $product,
            'images' => $productModel->images((int) $product['id']),
            'categories' => (new CategoryModel())->forCatalog(),
        ], 'seller');
    }

    public function update(string $id): void
    {
        Auth::requireLogin();
        $productModel = new ProductModel();
        $product = $productModel->ownProduct((int) $id, Auth::id());
        if (!$product) {
            Session::flash('error', 'Produk tidak ditemukan atau bukan milikmu.');
            $this->redirect('/penjual/produk');
        }
        if ($product['status'] === 'terjual') {
            Session::flash('error', 'Produk yang sudah terjual tidak dapat diubah.');
            $this->redirect('/penjual/produk');
        }

        [$data, $errors] = $this->validateProduct();
        $removeIds = array_map('intval', (array) post('remove_images', []));
        $existing = $productModel->images((int) $product['id']);
        $newImages = [];
        if (!$errors) {
            [$newImages, $imageErrors] = $this->processImages($_FILES['images'] ?? null, false);
            $errors = array_merge($errors, $imageErrors);
        }
        $remaining = count(array_filter($existing, fn ($image) => !in_array((int) $image['id'], $removeIds, true)));
        if ($remaining + count($newImages) < 1) {
            $errors[] = 'Produk harus memiliki minimal satu foto.';
        }
        if ($remaining + count($newImages) > 5) {
            $errors[] = 'Maksimal 5 foto untuk setiap produk.';
        }

        if ($errors) {
            foreach ($errors as $error) {
                Session::flash('error', $error);
            }
            Session::old($data);
            $this->redirect('/penjual/produk/' . $product['id'] . '/edit');
        }

        $productModel->update((int) $product['id'], $data, $newImages, $removeIds);
        Session::flash('success', 'Perubahan produk berhasil disimpan.');
        $this->redirect('/produk/' . $product['id']);
    }

    public function delete(string $id): void
    {
        Auth::requireLogin();
        $productModel = new ProductModel();
        $product = $productModel->ownProduct((int) $id, Auth::id());
        if (!$product) {
            Session::flash('error', 'Produk tidak ditemukan atau bukan milikmu.');
            $this->redirect('/penjual/produk');
        }
        if ($product['status'] === 'terjual') {
            Session::flash('error', 'Produk yang sudah terjual tidak dapat dihapus karena terhubung dengan riwayat pesanan.');
            $this->redirect('/penjual/produk');
        }
        $productModel->softDelete((int) $product['id']);
        Session::flash('success', 'Produk berhasil dihapus dari etalase.');
        $this->redirect('/penjual/produk');
    }

    public function orders(): void
    {
        Auth::requireLogin('Silakan masuk untuk melihat pesanan masuk.');
        if (Auth::isAdmin()) {
            $this->redirect('/admin/transaksi');
        }
        $status = (string) query('status');
        $orderModel = new OrderModel();
        $this->render('seller/orders', [
            'title' => 'Pesanan Masuk — Relova',
            'page' => 'seller-orders',
            'orders' => $orderModel->forSeller(Auth::id(), $status),
            'statusFilter' => $status,
            'stats' => $orderModel->sellerStats(Auth::id()),
        ], 'seller');
    }

    public function orderDetail(string $id): void
    {
        Auth::requireLogin();
        $orderModel = new OrderModel();
        $order = $orderModel->findForUser((int) $id, Auth::id());
        if (!$order || (int) $order['seller_id'] !== Auth::id()) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Pesanan tidak ditemukan'], 'main');
            return;
        }
        $this->render('seller/order-detail', [
            'title' => 'Kelola Pesanan ' . $order['order_number'] . ' — Relova',
            'page' => 'seller-order-detail',
            'order' => $order,
        ], 'seller');
    }

    public function updateStatus(string $id): void
    {
        Auth::requireLogin();
        $orderModel = new OrderModel();
        $order = $orderModel->findForUser((int) $id, Auth::id());
        if (!$order || (int) $order['seller_id'] !== Auth::id()) {
            Session::flash('error', 'Pesanan tidak ditemukan.');
            $this->redirect('/penjual/pesanan');
        }
        $newStatus = (string) post('status');
        $allowed = [
            'menunggu' => ['dikonfirmasi', 'dibatalkan'],
            'dikonfirmasi' => ['diproses', 'dibatalkan'],
            'diproses' => ['dikirim', 'dibatalkan'],
            'dikirim' => ['selesai'],
            'selesai' => [],
            'dibatalkan' => [],
        ];
        if (!in_array($newStatus, $allowed[$order['status']] ?? [], true)) {
            Session::flash('error', 'Perubahan status tersebut tidak dapat dilakukan.');
            $this->redirect('/penjual/pesanan/' . $order['id']);
        }

        $orderModel->updateStatus((int) $order['id'], $newStatus);
        $messages = [
            'dikonfirmasi' => 'Pesananmu sudah dikonfirmasi penjual.',
            'diproses' => 'Penjual sedang menyiapkan pesananmu.',
            'dikirim' => 'Pesananmu sedang dalam perjalanan.',
            'selesai' => 'Pesanan telah selesai. Jangan lupa beri ulasan.',
            'dibatalkan' => 'Pesanan dibatalkan oleh penjual.',
        ];
        (new NotificationModel())->add(
            (int) $order['buyer_id'],
            'pesanan',
            'Status pesanan diperbarui',
            $messages[$newStatus] ?? 'Status pesananmu berubah.',
            '/pesanan/' . $order['id']
        );
        Session::flash('success', 'Status pesanan diperbarui menjadi ' . order_status_label($newStatus) . '.');
        $this->redirect('/penjual/pesanan/' . $order['id']);
    }

    private function validateProduct(): array
    {
        $data = [
            'title' => trim((string) post('title')),
            'category_id' => (int) post('category_id'),
            'type' => (string) post('type'),
            'size' => (string) post('size'),
            'price' => (string) post('price'),
            'condition' => (string) post('condition'),
            'description' => trim((string) post('description')),
            'brand' => trim((string) post('brand')),
            'color' => trim((string) post('color')),
            'defect' => trim((string) post('defect')),
        ];
        $errors = [];

        if (mb_strlen($data['title']) < 5 || mb_strlen($data['title']) > 100) {
            $errors[] = 'Nama produk harus 5–100 karakter.';
        }
        $category = (new CategoryModel())->find($data['category_id']);
        if (!$category || !(int) $category['is_active']) {
            $errors[] = 'Pilih kategori produk yang tersedia.';
        } elseif ($data['type'] !== $category['type']) {
            $errors[] = 'Jenis barang harus sesuai dengan kategori yang dipilih.';
        }
        if (!in_array($data['size'], sizes(), true)) {
            $errors[] = 'Pilih ukuran yang tersedia.';
        }
        if (!is_numeric($data['price']) || (int) $data['price'] < 10000 || (int) $data['price'] > 100000000) {
            $errors[] = 'Harga harus antara Rp 10.000 dan Rp 100.000.000.';
        }
        if (!in_array($data['condition'], conditions(), true)) {
            $errors[] = 'Pilih kondisi barang yang sesuai.';
        }
        if (mb_strlen($data['description']) < 30 || mb_strlen($data['description']) > 2000) {
            $errors[] = 'Deskripsi produk harus 30–2.000 karakter.';
        }
        if (mb_strlen($data['defect']) < 3 || mb_strlen($data['defect']) > 300) {
            $errors[] = 'Tuliskan kondisi cacat atau nyatakan "Tidak ada cacat" pada bagian catatan kondisi.';
        }
        if (mb_strlen($data['brand']) > 60 || mb_strlen($data['color']) > 40) {
            $errors[] = 'Nama merek atau warna terlalu panjang.';
        }
        $data['price'] = (int) $data['price'];
        return [$data, $errors];
    }

    private function processImages(?array $files, bool $required): array
    {
        $errors = [];
        $saved = [];
        if (!$files || !isset($files['name']) || !is_array($files['name'])) {
            if ($required) {
                $errors[] = 'Unggah minimal satu foto produk.';
            }
            return [$saved, $errors];
        }

        $count = count(array_filter($files['name'], fn ($name) => $name !== ''));
        if ($count === 0) {
            if ($required) {
                $errors[] = 'Unggah minimal satu foto produk.';
            }
            return [$saved, $errors];
        }
        if ($count > 5) {
            $errors[] = 'Maksimal 5 foto untuk setiap produk.';
            return [$saved, $errors];
        }

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $maxSize = (int) config('max_upload_size', 4 * 1024 * 1024);
        $uploadDir = config('upload_dir') . DIRECTORY_SEPARATOR . 'products';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        foreach ($files['name'] as $index => $name) {
            if ($name === '') {
                continue;
            }
            $tmp = $files['tmp_name'][$index] ?? '';
            $error = (int) ($files['error'][$index] ?? UPLOAD_ERR_NO_FILE);
            $size = (int) ($files['size'][$index] ?? 0);
            if ($error !== UPLOAD_ERR_OK) {
                $errors[] = upload_error_message($error);
                continue;
            }
            if ($size > $maxSize) {
                $errors[] = 'Foto "' . $name . '" melebihi ukuran 4 MB.';
                continue;
            }
            $mime = $finfo->file($tmp);
            if (!isset($allowed[$mime])) {
                $errors[] = 'Format foto harus JPG, PNG, atau WEBP.';
                continue;
            }
            $filename = 'produk-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
            if (!move_uploaded_file($tmp, $uploadDir . DIRECTORY_SEPARATOR . $filename)) {
                $errors[] = 'Foto gagal disimpan. Coba unggah kembali.';
                continue;
            }
            $saved[] = 'uploads/products/' . $filename;
        }

        if (!$saved && !$errors && $required) {
            $errors[] = 'Unggah minimal satu foto produk.';
        }
        return [$saved, $errors];
    }
}
