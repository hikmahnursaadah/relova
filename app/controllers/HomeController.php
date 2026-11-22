<?php

class HomeController extends Controller
{
    public function index(): void
    {
        $products = new ProductModel();
        $categories = (new CategoryModel())->all();
        $settings = (new SettingModel())->all();
        $featured = $products->featured(8);
        $wishlistIds = Auth::check() ? (new WishlistModel())->ids(Auth::id()) : [];
        $users = new UserModel();

        $this->render('home/index', [
            'title' => 're:wear — Pakaian Punya Kehidupan Kedua',
            'page' => 'home',
            'categories' => $categories,
            'featured' => $featured,
            'settings' => $settings,
            'wishlistIds' => $wishlistIds,
            'newest' => $products->newest(3),
            'liveStats' => [
                'products' => $products->total('aktif'),
                'sellers' => $users->activeSellers(),
                'cities' => $users->sellerCities(),
            ],
        ], 'main');
    }

    public function about(): void
    {
        $settings = (new SettingModel())->all();
        $users = new UserModel();
        $sellers = $users->all(['role' => 'penjual', 'status' => 'aktif'], 1, 3);
        $reviews = (new ReviewModel())->forSeller(2, 3);
        $this->render('home/about', [
            'title' => 'Tentang Kami — re:wear',
            'page' => 'about',
            'settings' => $settings,
            'sellers' => $sellers,
            'reviews' => $reviews,
            'liveStats' => [
                'sellers' => $users->activeSellers(),
                'cities' => $users->sellerCities(),
            ],
        ], 'main');
    }

    public function studio(): void
    {
        $this->render('home/studio', [
            'title' => 'Studio 3D — re:wear',
            'page' => 'studio',
        ], 'main');
    }

    public function notFound(): void
    {
        http_response_code(404);
        $this->render('errors/404', ['title' => 'Halaman tidak ditemukan'], 'main');
    }
}
