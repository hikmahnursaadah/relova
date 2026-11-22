<?php

class WishlistController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin('Silakan masuk untuk melihat daftar favoritmu.');
        if (Auth::isAdmin()) {
            $this->redirect('/admin');
        }
        $wishlist = new WishlistModel();
        $this->render('wishlist/index', [
            'title' => 'Favorit — re:wear',
            'page' => 'wishlist',
            'items' => $wishlist->items(Auth::id()),
            'wishlistIds' => $wishlist->ids(Auth::id()),
        ], Auth::isSeller() ? 'seller' : 'buyer');
    }

    public function toggle(string $id): void
    {
        Auth::requireLogin('Silakan masuk untuk menyimpan produk ke favorit.');
        $productModel = new ProductModel();
        $product = $productModel->find((int) $id);
        if (!$product || $product['status'] !== 'aktif') {
            if (request_wants_json()) {
                json_response(['ok' => false, 'message' => 'Produk ini sudah tidak tersedia.'], 422);
            }
            Session::flash('error', 'Produk ini sudah tidak tersedia.');
            back('/jelajahi');
        }
        if ((int) $product['seller_id'] === Auth::id()) {
            if (request_wants_json()) {
                json_response(['ok' => false, 'message' => 'Produk milikmu tidak dapat disimpan ke favorit.'], 422);
            }
            Session::flash('info', 'Produk milikmu tidak dapat disimpan ke favorit.');
            back('/produk/' . $product['id']);
        }

        $wishlist = new WishlistModel();
        $saved = $wishlist->toggle(Auth::id(), (int) $product['id']);
        $count = $wishlist->count(Auth::id());

        if (request_wants_json()) {
            json_response([
                'ok' => true,
                'saved' => $saved,
                'count' => $count,
                'message' => $saved ? 'Disimpan ke favorit.' : 'Dihapus dari favorit.',
            ]);
        }

        Session::flash('success', $saved ? 'Produk ditambahkan ke favorit.' : 'Produk dihapus dari favorit.');
        back('/produk/' . $product['id']);
    }
}
