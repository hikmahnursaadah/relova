<?php

class ProductController extends Controller
{
    public function index(): void
    {
        $filters = [
            'q' => trim((string) query('q')),
            'category' => $_GET['kategori'] ?? [],
            'type' => (string) query('jenis'),
            'size' => (string) query('ukuran'),
            'condition' => (string) query('kondisi'),
            'min_price' => (string) query('min'),
            'max_price' => (string) query('max'),
            'sort' => (string) query('urutkan', 'terbaru'),
            'seller_id' => (int) query('penjual', 0),
        ];
        if (!is_array($filters['category'])) {
            $filters['category'] = $filters['category'] ? [$filters['category']] : [];
        }
        $filters['category'] = array_values(array_filter(array_map('strval', $filters['category'])));

        $page = max(1, (int) query('halaman', 1));
        $productModel = new ProductModel();
        $result = $productModel->paginate($filters, $page, 12);
        $wishlistIds = Auth::check() ? (new WishlistModel())->ids(Auth::id()) : [];

        $seller = $filters['seller_id'] ? (new UserModel())->sellerProfile($filters['seller_id']) : null;

        $this->render('products/index', [
            'title' => 'Jelajahi Koleksi — re:wear',
            'page' => 'catalog',
            'products' => $result['items'],
            'pagination' => $result,
            'filters' => $filters,
            'categories' => (new CategoryModel())->forCatalog(),
            'wishlistIds' => $wishlistIds,
            'seller' => $seller,
        ], 'main');
    }

    public function detail(string $id): void
    {
        $productModel = new ProductModel();
        $product = $productModel->find((int) $id);
        if (!$product) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Produk tidak ditemukan'], 'main');
            return;
        }

        $isOwner = Auth::check() && (int) $product['seller_id'] === Auth::id();
        $isAdmin = Auth::isAdmin();
        if (!in_array($product['status'], ['aktif', 'terjual'], true) && !$isOwner && !$isAdmin) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Produk tidak ditemukan'], 'main');
            return;
        }

        $images = $productModel->images((int) $product['id']);
        $reviews = (new ReviewModel())->forProduct((int) $product['id']);
        $reviewStats = (new ReviewModel())->statsForProduct((int) $product['id']);
        $seller = (new UserModel())->sellerProfile((int) $product['seller_id']);
        $wishlisted = Auth::check() ? (new WishlistModel())->exists(Auth::id(), (int) $product['id']) : false;

        $viewed = Session::get('viewed_products', []);
        if (!in_array((int) $product['id'], $viewed, true) && !$isOwner) {
            $productModel->incrementView((int) $product['id']);
            $viewed[] = (int) $product['id'];
            Session::put('viewed_products', $viewed);
        }

        $this->render('products/detail', [
            'title' => $product['title'] . ' — re:wear',
            'page' => 'product-detail',
            'product' => $product,
            'images' => $images,
            'reviews' => $reviews,
            'reviewStats' => $reviewStats,
            'seller' => $seller,
            'wishlisted' => $wishlisted,
            'isOwner' => $isOwner,
            'isAdmin' => $isAdmin,
            'related' => $productModel->related($product, 4),
            'sellerProducts' => $productModel->bySeller((int) $product['seller_id'], (int) $product['id'], 3),
            'wishlistIds' => Auth::check() ? (new WishlistModel())->ids(Auth::id()) : [],
        ], 'main');
    }

    public function report(string $id): void
    {
        Auth::requireLogin('Silakan masuk untuk mengirim laporan.');
        $productModel = new ProductModel();
        $product = $productModel->find((int) $id);
        if (!$product) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Produk tidak ditemukan'], 'main');
            return;
        }

        $reason = trim((string) post('reason'));
        $details = trim((string) post('details'));
        $allowedReasons = ['Produk diduga tiruan', 'Foto tidak sesuai', 'Produk tidak layak pakai', 'Konten tidak pantas', 'Lainnya'];
        if (!in_array($reason, $allowedReasons, true)) {
            Session::flash('error', 'Pilih alasan laporan yang tersedia.');
            back('/produk/' . $product['id']);
        }

        (new ReportModel())->create(
            Auth::id(),
            (int) $product['seller_id'],
            (int) $product['id'],
            $reason,
            $details
        );

        foreach ((new UserModel())->all(['role' => 'admin'], 1, 20) as $admin) {
            (new NotificationModel())->add(
                (int) $admin['id'],
                'laporan',
                'Laporan produk baru',
                Auth::user()['name'] . ' melaporkan "' . $product['title'] . '".',
                '/admin/pengaturan#laporan'
            );
        }

        Session::flash('success', 'Laporanmu sudah kami terima. Tim moderasi akan meninjau dalam 1×24 jam.');
        back('/produk/' . $product['id']);
    }
}
