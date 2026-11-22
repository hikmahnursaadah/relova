<?php

class OrderController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin('Silakan masuk untuk melihat pesananmu.');
        if (Auth::isAdmin()) {
            $this->redirect('/admin/transaksi');
        }
        $status = (string) query('status');
        $orderModel = new OrderModel();
        $this->render('orders/index', [
            'title' => 'Pesanan Saya — re:wear',
            'page' => 'orders',
            'orders' => $orderModel->forBuyer(Auth::id(), $status),
            'statusFilter' => $status,
            'stats' => $orderModel->buyerStats(Auth::id()),
        ], 'buyer');
    }

    public function detail(string $id): void
    {
        Auth::requireLogin('Silakan masuk untuk melihat detail pesanan.');
        $orderModel = new OrderModel();
        $order = $orderModel->findForUser((int) $id, Auth::id(), Auth::isAdmin());
        if (!$order) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Pesanan tidak ditemukan'], 'main');
            return;
        }

        $layout = 'buyer';
        if (Auth::isAdmin()) {
            $layout = 'admin';
        } elseif ((int) $order['seller_id'] === Auth::id()) {
            $layout = 'seller';
        }

        $canCancel = (int) $order['buyer_id'] === Auth::id() && $order['status'] === 'menunggu';
        $canReview = (int) $order['buyer_id'] === Auth::id() && $orderModel->canReview((int) $order['id'], Auth::id());
        $this->render('orders/detail', [
            'title' => 'Pesanan ' . $order['order_number'] . ' — re:wear',
            'page' => 'order-detail',
            'order' => $order,
            'canCancel' => $canCancel,
            'canReview' => $canReview,
        ], $layout);
    }

    public function cancel(string $id): void
    {
        Auth::requireLogin();
        $orderModel = new OrderModel();
        $order = $orderModel->findForUser((int) $id, Auth::id());
        if (!$order || (int) $order['buyer_id'] !== Auth::id()) {
            Session::flash('error', 'Pesanan tidak ditemukan.');
            $this->redirect('/pesanan');
        }
        if ($order['status'] !== 'menunggu') {
            Session::flash('error', 'Pesanan ini sudah diproses penjual sehingga tidak dapat dibatalkan.');
            $this->redirect('/pesanan/' . $order['id']);
        }

        $orderModel->updateStatus((int) $order['id'], 'dibatalkan');
        (new NotificationModel())->add(
            (int) $order['seller_id'],
            'pesanan',
            'Pesanan dibatalkan',
            'Pesanan ' . $order['order_number'] . ' dibatalkan oleh pembeli.',
            '/penjual/pesanan/' . $order['id']
        );
        Session::flash('success', 'Pesanan berhasil dibatalkan. Produk kembali tersedia untuk pembeli lain.');
        $this->redirect('/pesanan/' . $order['id']);
    }

    public function review(string $id): void
    {
        Auth::requireLogin('Silakan masuk untuk memberi ulasan.');
        $orderModel = new OrderModel();
        $order = $orderModel->findForUser((int) $id, Auth::id());
        if (!$order || (int) $order['buyer_id'] !== Auth::id()) {
            Session::flash('error', 'Pesanan tidak ditemukan.');
            $this->redirect('/pesanan');
        }
        if (!$orderModel->canReview((int) $order['id'], Auth::id())) {
            Session::flash('error', 'Ulasan hanya dapat diberikan untuk pesanan yang sudah selesai dan belum diulas.');
            $this->redirect('/pesanan/' . $order['id']);
        }

        $productId = (int) post('product_id');
        $rating = (int) post('rating');
        $comment = trim((string) post('comment'));
        $validItem = false;
        foreach ($order['items'] as $item) {
            if ((int) $item['product_id'] === $productId && !$item['review_id']) {
                $validItem = true;
                break;
            }
        }
        if (!$validItem) {
            Session::flash('error', 'Produk yang diulas tidak sesuai dengan pesanan ini.');
            $this->redirect('/pesanan/' . $order['id']);
        }
        if ($rating < 1 || $rating > 5) {
            Session::flash('error', 'Pilih rating antara 1 sampai 5 bintang.');
            $this->redirect('/pesanan/' . $order['id']);
        }
        if (mb_strlen($comment) > 600) {
            Session::flash('error', 'Ulasan maksimal 600 karakter.');
            $this->redirect('/pesanan/' . $order['id']);
        }

        (new ReviewModel())->create((int) $order['id'], $productId, Auth::id(), (int) $order['seller_id'], $rating, $comment);
        (new NotificationModel())->add(
            (int) $order['seller_id'],
            'ulasan',
            'Ulasan baru diterima',
            Auth::user()['name'] . ' memberi ulasan ' . $rating . ' bintang.',
            '/penjual'
        );
        Session::flash('success', 'Terima kasih! Ulasanmu membantu pembeli lain memilih dengan lebih yakin.');
        $this->redirect('/pesanan/' . $order['id']);
    }
}
