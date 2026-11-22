<?php

class CartController extends Controller
{
    private function cartIds(): array
    {
        $ids = Session::get('cart', []);
        return is_array($ids) ? array_values(array_unique(array_map('intval', $ids))) : [];
    }

    private function saveCart(array $ids): void
    {
        Session::put('cart', array_values(array_unique(array_map('intval', $ids))));
    }

    private function rejectAdmin(): void
    {
        if (!Auth::isAdmin()) {
            return;
        }
        $message = 'Administrator tidak dapat menggunakan keranjang belanja.';
        if (request_wants_json()) {
            json_response(['ok' => false, 'message' => $message], 403);
        }
        Session::flash('error', $message);
        $this->redirect('/admin');
    }

    private function items(): array
    {
        $model = new ProductModel();
        $cartIds = $this->cartIds();
        $reservedIds = (new OrderModel())->reservedProductIds($cartIds);
        $items = [];
        $validIds = [];
        foreach ($cartIds as $id) {
            if (in_array($id, $reservedIds, true)) {
                continue;
            }
            $product = $model->find($id);
            if ($product && $product['status'] === 'aktif' && $product['seller_role'] !== 'admin') {
                if (Auth::check() && (int) $product['seller_id'] === Auth::id()) {
                    continue;
                }
                $product['quantity'] = 1;
                $items[] = $product;
                $validIds[] = $id;
            }
        }
        if (count($validIds) !== count($cartIds)) {
            $this->saveCart($validIds);
        }
        return $items;
    }

    private function groupedItems(array $items): array
    {
        $groups = [];
        foreach ($items as $item) {
            $sellerId = (int) $item['seller_id'];
            if (!isset($groups[$sellerId])) {
                $groups[$sellerId] = [
                    'seller_id' => $sellerId,
                    'seller_name' => $item['seller_name'],
                    'seller_city' => $item['seller_city'],
                    'items' => [],
                    'subtotal' => 0,
                ];
            }
            $groups[$sellerId]['items'][] = $item;
            $groups[$sellerId]['subtotal'] += (int) $item['price'];
        }
        return $groups;
    }

    public function index(): void
    {
        Auth::requireLogin('Silakan masuk untuk melihat keranjangmu.');
        if (Auth::isAdmin()) {
            $this->redirect('/admin');
        }
        $items = $this->items();
        $this->render('cart/index', [
            'title' => 'Keranjang — re:wear',
            'page' => 'cart',
            'items' => $items,
            'groups' => $this->groupedItems($items),
            'wishlistIds' => (new WishlistModel())->ids(Auth::id()),
        ], Auth::isSeller() ? 'seller' : 'buyer');
    }

    public function add(string $id): void
    {
        Auth::requireLogin('Silakan masuk untuk menambahkan produk ke keranjang.');
        $this->rejectAdmin();
        $productModel = new ProductModel();
        $product = $productModel->find((int) $id);
        if (!$product || $product['status'] !== 'aktif') {
            $message = 'Produk ini sudah tidak tersedia.';
            if (request_wants_json()) {
                json_response(['ok' => false, 'message' => $message], 422);
            }
            Session::flash('error', $message);
            back('/jelajahi');
        }
        if ((int) $product['seller_id'] === Auth::id()) {
            $message = 'Produk milikmu tidak dapat ditambahkan ke keranjang.';
            if (request_wants_json()) {
                json_response(['ok' => false, 'message' => $message], 422);
            }
            Session::flash('info', $message);
            back('/produk/' . $product['id']);
        }

        $ids = $this->cartIds();
        $already = in_array((int) $product['id'], $ids, true);
        if (!$already) {
            $ids[] = (int) $product['id'];
            $this->saveCart($ids);
        }

        $count = count($this->cartIds());
        $checkoutMode = post('mode') === 'checkout';
        if (request_wants_json()) {
            json_response([
                'ok' => true,
                'count' => $count,
                'message' => $already ? 'Produk sudah ada di keranjang.' : 'Produk ditambahkan ke keranjang.',
                'redirect' => $checkoutMode ? url('/checkout') : null,
            ]);
        }

        Session::flash('success', $already ? 'Produk sudah ada di keranjang.' : 'Produk ditambahkan ke keranjang.');
        $this->redirect($checkoutMode ? '/checkout' : '/keranjang');
    }

    public function remove(string $id): void
    {
        Auth::requireLogin();
        $this->rejectAdmin();
        $ids = array_values(array_filter($this->cartIds(), fn ($cartId) => $cartId !== (int) $id));
        $this->saveCart($ids);
        Session::flash('info', 'Produk dihapus dari keranjang.');
        $this->redirect('/keranjang');
    }

    public function clear(): void
    {
        Auth::requireLogin();
        $this->rejectAdmin();
        Session::forget('cart');
        Session::flash('info', 'Keranjang berhasil dikosongkan.');
        $this->redirect('/keranjang');
    }

    public function checkout(): void
    {
        Auth::requireLogin('Silakan masuk untuk melanjutkan pemesanan.');
        if (Auth::isAdmin()) {
            $this->redirect('/admin');
        }
        $items = $this->items();
        if (!$items) {
            Session::flash('info', 'Keranjangmu masih kosong. Pilih produk terlebih dahulu.');
            $this->redirect('/jelajahi');
        }
        $this->render('cart/checkout', [
            'title' => 'Konfirmasi Pesanan — re:wear',
            'page' => 'checkout',
            'items' => $items,
            'groups' => $this->groupedItems($items),
        ], Auth::isSeller() ? 'seller' : 'buyer');
    }

    public function place(): void
    {
        Auth::requireLogin('Silakan masuk untuk membuat pesanan.');
        $this->rejectAdmin();
        $items = $this->items();
        if (!$items) {
            Session::flash('error', 'Keranjangmu kosong atau produk sudah tidak tersedia.');
            $this->redirect('/jelajahi');
        }

        $address = trim((string) post('shipping_address'));
        $notes = trim((string) post('notes'));
        $shippingInput = post('shipping', []);
        $shippingInput = is_array($shippingInput) ? $shippingInput : [];
        $groups = $this->groupedItems($items);
        $errors = [];

        if (mb_strlen($address) < 15) {
            $errors[] = 'Alamat pengiriman minimal 15 karakter dan harus lengkap.';
        }

        $shippingCosts = [];
        foreach ($groups as $sellerId => $group) {
            $value = $shippingInput[$sellerId] ?? 0;
            if (!is_numeric($value) || (int) $value < 0 || (int) $value > 500000) {
                $errors[] = 'Ongkos kirim untuk ' . $group['seller_name'] . ' tidak valid.';
                break;
            }
            $shippingCosts[$sellerId] = (int) $value;
        }

        if ($errors) {
            foreach ($errors as $error) {
                Session::flash('error', $error);
            }
            Session::old(['shipping_address' => $address, 'notes' => $notes]);
            $this->redirect('/checkout');
        }

        $batchGroups = [];
        foreach ($groups as $sellerId => $group) {
            $batchGroups[$sellerId] = [
                'seller_id' => (int) $sellerId,
                'items' => array_map(fn ($item) => [
                    'product_id' => (int) $item['id'],
                    'title' => $item['title'],
                    'image' => $item['primary_image'],
                    'price' => (int) $item['price'],
                ], $group['items']),
            ];
        }

        try {
            $created = (new OrderModel())->createBatchForBuyer(Auth::id(), $batchGroups, $shippingCosts, $address, $notes);
        } catch (Throwable $e) {
            Session::flash('error', 'Pesanan gagal dibuat. Silakan coba lagi.');
            $this->redirect('/checkout');
        }

        $notification = new NotificationModel();
        $buyerName = Auth::user()['name'];
        $index = 0;
        foreach ($batchGroups as $sellerId => $group) {
            $orderId = $created[$index] ?? null;
            $index++;
            if (!$orderId) {
                continue;
            }
            try {
                $notification->add(
                    (int) $sellerId,
                    'pesanan',
                    'Pesanan baru masuk',
                    $buyerName . ' memesan ' . count($group['items']) . ' produk. Segera konfirmasi pesanan.',
                    '/penjual/pesanan/' . $orderId
                );
            } catch (Throwable $e) {
                // Notifikasi bersifat tambahan dan tidak membatalkan pesanan yang sudah dibuat.
            }
        }
        Session::forget('cart');
        $this->render('cart/success', [
            'title' => 'Pesanan Dibuat — re:wear',
            'page' => 'order-success',
            'orderIds' => $created,
        ], Auth::isSeller() ? 'seller' : 'buyer');
    }
}
