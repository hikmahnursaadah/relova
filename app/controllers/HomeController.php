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

        $this->render('home/index', [
            'title' => 're:wear — Fashion Punya Kehidupan Kedua',
            'page' => 'home',
            'categories' => $categories,
            'featured' => $featured,
            'settings' => $settings,
            'wishlistIds' => $wishlistIds,
            'newest' => $products->newest(3),
        ], 'main');
    }

    public function about(): void
    {
        $settings = (new SettingModel())->all();
        $sellers = (new UserModel())->all(['role' => 'penjual', 'status' => 'aktif'], 1, 3);
        $reviews = (new ReviewModel())->forSeller(2, 3);
        $this->render('home/about', [
            'title' => 'Tentang Kami — re:wear',
            'page' => 'about',
            'settings' => $settings,
            'sellers' => $sellers,
            'reviews' => $reviews,
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
