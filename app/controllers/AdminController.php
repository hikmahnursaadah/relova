<?php

class AdminController extends Controller
{
    public function __construct()
    {
        Auth::requireAdmin();
    }

    public function dashboard(): void
    {
        $userModel = new UserModel();
        $productModel = new ProductModel();
        $orderModel = new OrderModel();
        $reportModel = new ReportModel();
        $this->render('admin/dashboard', [
            'title' => 'Dasbor Administrator — re:wear',
            'page' => 'admin-dashboard',
            'userTotal' => $userModel->total(),
            'newUsers' => $userModel->newThisMonth(),
            'productTotal' => $productModel->total(),
            'activeProducts' => $productModel->total('aktif'),
            'orderStats' => $orderModel->adminStats(),
            'pendingOrders' => $orderModel->adminPaginate(['status' => 'menunggu'], 1, 5)['items'],
            'recentReports' => $reportModel->recent(4),
            'openReports' => $reportModel->countOpen(),
            'moderation' => $productModel->moderationActivity(5),
            'dailyOrders' => $orderModel->dailyOrderCounts(7),
            'recentOrders' => $orderModel->recent(5),
        ], 'admin');
    }

    public function users(): void
    {
        $filters = [
            'q' => trim((string) query('q')),
            'role' => (string) query('peran'),
            'status' => (string) query('status'),
        ];
        $page = max(1, (int) query('halaman', 1));
        $model = new UserModel();
        $this->render('admin/users', [
            'title' => 'Kelola Pengguna — re:wear',
            'page' => 'admin-users',
            'users' => $model->all($filters, $page, 15),
            'total' => $model->countAll($filters),
            'currentPage' => $page,
            'pages' => max(1, (int) ceil($model->countAll($filters) / 15)),
            'filters' => $filters,
        ], 'admin');
    }

    public function userStatus(string $id): void
    {
        $target = (new UserModel())->find((int) $id);
        if (!$target) {
            Session::flash('error', 'Pengguna tidak ditemukan.');
            $this->redirect('/admin/pengguna');
        }
        if ((int) $target['id'] === Auth::id()) {
            Session::flash('error', 'Kamu tidak dapat menangguhkan akun sendiri.');
            $this->redirect('/admin/pengguna');
        }
        if ($target['role'] === 'admin') {
            Session::flash('error', 'Akun administrator tidak dapat ditangguhkan dari halaman ini.');
            $this->redirect('/admin/pengguna');
        }
        $status = (string) post('status');
        if (!in_array($status, ['aktif', 'ditangguhkan'], true)) {
            Session::flash('error', 'Status pengguna tidak valid.');
            $this->redirect('/admin/pengguna');
        }
        (new UserModel())->setStatus((int) $target['id'], $status);
        (new NotificationModel())->add(
            (int) $target['id'],
            'akun',
            $status === 'aktif' ? 'Akunmu telah diaktifkan kembali' : 'Akunmu ditangguhkan',
            $status === 'aktif'
                ? 'Akunmu kembali aktif. Tetap patuhi ketentuan komunitas re:wear.'
                : 'Akunmu ditangguhkan sementara. Hubungi tim re:wear jika membutuhkan bantuan.',
            '/'
        );
        Session::flash('success', 'Status akun ' . $target['name'] . ' diubah menjadi ' . ($status === 'aktif' ? 'aktif' : 'ditangguhkan') . '.');
        $this->redirect('/admin/pengguna');
    }

    public function products(): void
    {
        $filters = [
            'q' => trim((string) query('q')),
            'status' => (string) query('status'),
            'category_id' => (int) query('kategori', 0),
        ];
        $page = max(1, (int) query('halaman', 1));
        $model = new ProductModel();
        $result = $model->adminPaginate($filters, $page, 15);
        $this->render('admin/products', [
            'title' => 'Moderasi Produk — re:wear',
            'page' => 'admin-products',
            'products' => $result['items'],
            'pagination' => $result,
            'filters' => $filters,
            'categories' => (new CategoryModel())->all(),
        ], 'admin');
    }

    public function moderateProduct(string $id): void
    {
        $productModel = new ProductModel();
        $product = $productModel->find((int) $id);
        if (!$product) {
            Session::flash('error', 'Produk tidak ditemukan.');
            $this->redirect('/admin/produk');
        }
        $action = (string) post('action');
        $map = ['hapus' => 'dihapus', 'tinjau' => 'ditinjau', 'pulihkan' => 'aktif'];
        if (!isset($map[$action])) {
            Session::flash('error', 'Tindakan moderasi tidak valid.');
            $this->redirect('/admin/produk');
        }
        $status = $map[$action];
        if ($action === 'pulihkan' && $product['status'] === 'terjual') {
            Session::flash('error', 'Produk yang sudah terjual tidak dapat diaktifkan kembali.');
            $this->redirect('/admin/produk');
        }
        $productModel->setStatus((int) $product['id'], $status);
        if ($status === 'dihapus') {
            (new WishlistModel())->deleteForProduct((int) $product['id']);
        }
        $labels = [
            'hapus' => 'Produk dihapus dari etalase karena melanggar ketentuan komunitas.',
            'tinjau' => 'Produk sedang ditinjau oleh tim moderasi dan sementara disembunyikan.',
            'pulihkan' => 'Produk telah lolos peninjauan dan kembali tayang.',
        ];
        (new NotificationModel())->add(
            (int) $product['seller_id'],
            'moderasi',
            'Pembaruan status produk',
            '"' . $product['title'] . '": ' . $labels[$action],
            '/penjual/produk'
        );
        Session::flash('success', 'Produk "' . $product['title'] . '" berhasil diperbarui.');
        $this->redirect('/admin/produk');
    }

    public function transactions(): void
    {
        $filters = [
            'q' => trim((string) query('q')),
            'status' => (string) query('status'),
        ];
        $page = max(1, (int) query('halaman', 1));
        $model = new OrderModel();
        $result = $model->adminPaginate($filters, $page, 15);
        $this->render('admin/transactions', [
            'title' => 'Transaksi — re:wear',
            'page' => 'admin-transactions',
            'orders' => $result['items'],
            'pagination' => $result,
            'filters' => $filters,
            'stats' => $model->adminStats(),
        ], 'admin');
    }

    public function transactionDetail(string $id): void
    {
        $orderModel = new OrderModel();
        $order = $orderModel->findForUser((int) $id, Auth::id(), true);
        if (!$order) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Transaksi tidak ditemukan'], 'main');
            return;
        }
        $this->render('admin/order-detail', [
            'title' => 'Transaksi ' . $order['order_number'] . ' — re:wear',
            'page' => 'admin-order-detail',
            'order' => $order,
        ], 'admin');
    }

    public function transactionStatus(string $id): void
    {
        $orderModel = new OrderModel();
        $order = $orderModel->find((int) $id);
        if (!$order) {
            Session::flash('error', 'Transaksi tidak ditemukan.');
            $this->redirect('/admin/transaksi');
        }
        $status = (string) post('status');
        $allowed = [
            'menunggu' => ['dikonfirmasi', 'dibatalkan'],
            'dikonfirmasi' => ['diproses', 'dibatalkan'],
            'diproses' => ['dikirim', 'dibatalkan'],
            'dikirim' => ['selesai'],
            'selesai' => [],
            'dibatalkan' => [],
        ];
        if (!in_array($status, $allowed[$order['status']] ?? [], true)) {
            Session::flash('error', 'Perubahan status tersebut tidak dapat dilakukan.');
            $this->redirect('/admin/transaksi/' . $order['id']);
        }
        $orderModel->updateStatus((int) $order['id'], $status);
        foreach ([$order['buyer_id'], $order['seller_id']] as $userId) {
            (new NotificationModel())->add(
                (int) $userId,
                'pesanan',
                'Status transaksi diperbarui admin',
                'Pesanan ' . $order['order_number'] . ' kini berstatus ' . order_status_label($status) . '.',
                $userId === $order['buyer_id'] ? '/pesanan/' . $order['id'] : '/penjual/pesanan/' . $order['id']
            );
        }
        Session::flash('success', 'Status transaksi diperbarui.');
        $this->redirect('/admin/transaksi/' . $order['id']);
    }

    public function settings(): void
    {
        $this->render('admin/settings', [
            'title' => 'Pengaturan Platform — re:wear',
            'page' => 'admin-settings',
            'settings' => (new SettingModel())->all(),
            'categories' => (new CategoryModel())->all(),
            'reports' => (new ReportModel())->all(),
        ], 'admin');
    }

    public function updateSettings(): void
    {
        $allowed = ['site_name', 'site_tagline', 'contact_email', 'contact_phone', 'city', 'instagram', 'moderation_note'];
        $data = [];
        foreach ($allowed as $key) {
            $value = trim((string) post($key));
            if ($key === 'contact_email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                Session::flash('error', 'Format surel kontak belum benar.');
                $this->redirect('/admin/pengaturan');
            }
            $data[$key] = $value;
        }
        (new SettingModel())->setMany($data);
        Session::flash('success', 'Pengaturan platform berhasil disimpan.');
        $this->redirect('/admin/pengaturan');
    }

    public function createCategory(): void
    {
        $data = [
            'name' => trim((string) post('name')),
            'slug' => strtolower(trim((string) post('slug'))),
            'description' => trim((string) post('description')),
            'image' => trim((string) post('image')),
            'type' => (string) post('type'),
            'sort_order' => (int) post('sort_order'),
            'is_active' => post('is_active') ? 1 : 0,
        ];
        if (mb_strlen($data['name']) < 3 || !preg_match('/^[a-z0-9-]+$/', $data['slug']) || !in_array($data['type'], ['atasan', 'bawahan', 'outerwear', 'dress', 'sepatu'], true)) {
            Session::flash('error', 'Data kategori belum lengkap atau slug tidak valid.');
            $this->redirect('/admin/pengaturan#kategori');
        }
        if ((new CategoryModel())->findBySlug($data['slug'])) {
            Session::flash('error', 'Slug kategori sudah dipakai.');
            $this->redirect('/admin/pengaturan#kategori');
        }
        (new CategoryModel())->create($data);
        Session::flash('success', 'Kategori baru berhasil ditambahkan.');
        $this->redirect('/admin/pengaturan#kategori');
    }

    public function updateCategory(string $id): void
    {
        $category = (new CategoryModel())->find((int) $id);
        if (!$category) {
            Session::flash('error', 'Kategori tidak ditemukan.');
            $this->redirect('/admin/pengaturan#kategori');
        }
        $data = [
            'name' => trim((string) post('name')),
            'slug' => strtolower(trim((string) post('slug'))),
            'description' => trim((string) post('description')),
            'image' => trim((string) post('image')),
            'type' => (string) post('type'),
            'sort_order' => (int) post('sort_order'),
            'is_active' => post('is_active') ? 1 : 0,
        ];
        if (mb_strlen($data['name']) < 3 || !preg_match('/^[a-z0-9-]+$/', $data['slug']) || !in_array($data['type'], ['atasan', 'bawahan', 'outerwear', 'dress', 'sepatu'], true)) {
            Session::flash('error', 'Nama, slug, dan jenis kategori belum valid.');
            $this->redirect('/admin/pengaturan#kategori');
        }
        $duplicate = (new CategoryModel())->findBySlug($data['slug']);
        if ($duplicate && (int) $duplicate['id'] !== (int) $category['id']) {
            Session::flash('error', 'Slug kategori sudah dipakai.');
            $this->redirect('/admin/pengaturan#kategori');
        }
        (new CategoryModel())->update((int) $category['id'], $data);
        Session::flash('success', 'Kategori berhasil diperbarui.');
        $this->redirect('/admin/pengaturan#kategori');
    }

    public function resolveReport(string $id): void
    {
        $reportModel = new ReportModel();
        if (!$reportModel->find((int) $id)) {
            Session::flash('error', 'Laporan tidak ditemukan.');
            $this->redirect('/admin/pengaturan#laporan');
        }
        $status = (string) post('status');
        if (!in_array($status, ['baru', 'diproses', 'selesai'], true)) {
            Session::flash('error', 'Status laporan tidak valid.');
            $this->redirect('/admin/pengaturan#laporan');
        }
        $reportModel->updateStatus((int) $id, $status);
        Session::flash('success', 'Status laporan berhasil diperbarui.');
        $this->redirect('/admin/pengaturan#laporan');
    }
}
