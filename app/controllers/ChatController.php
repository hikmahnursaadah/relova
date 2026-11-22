<?php

class ChatController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin('Silakan masuk untuk membuka pesan.');
        if (Auth::isAdmin()) {
            $this->redirect('/admin');
        }
        $chatModel = new ChatModel();
        $conversations = $chatModel->conversations(Auth::id());
        $this->render('chat/index', [
            'title' => 'Pesan — re:wear',
            'page' => 'chat',
            'conversations' => $conversations,
            'activeChat' => null,
            'messages' => [],
        ], Auth::isSeller() ? 'seller' : 'buyer');
    }

    public function show(string $id): void
    {
        Auth::requireLogin('Silakan masuk untuk membuka pesan.');
        $chatModel = new ChatModel();
        $chat = $chatModel->findForUser((int) $id, Auth::id());
        if (!$chat) {
            http_response_code(404);
            $this->render('errors/404', ['title' => 'Percakapan tidak ditemukan'], 'main');
            return;
        }
        $chatModel->markRead((int) $chat['id'], Auth::id());

        $this->render('chat/index', [
            'title' => 'Pesan dengan ' . (((int) $chat['buyer_id'] === Auth::id()) ? $chat['seller_name'] : $chat['buyer_name']) . ' — re:wear',
            'page' => 'chat',
            'conversations' => $chatModel->conversations(Auth::id()),
            'activeChat' => $chat,
            'messages' => $chatModel->messages((int) $chat['id']),
        ], Auth::isSeller() ? 'seller' : 'buyer');
    }

    public function start(): void
    {
        Auth::requireLogin('Silakan masuk untuk memulai percakapan dengan penjual.');
        $productId = (int) post('product_id');
        $product = (new ProductModel())->find($productId);
        if (!$product || $product['status'] !== 'aktif') {
            Session::flash('error', 'Produk ini sudah tidak tersedia.');
            $this->redirect('/jelajahi');
        }
        if ((int) $product['seller_id'] === Auth::id()) {
            Session::flash('info', 'Kamu tidak dapat memulai chat untuk produk milikmu sendiri.');
            $this->redirect('/produk/' . $product['id']);
        }

        $chatModel = new ChatModel();
        $chatId = $chatModel->findOrCreate($productId, Auth::id(), (int) $product['seller_id']);
        $message = trim((string) post('message'));
        if ($message === '') {
            $message = 'Halo, apakah produk ini masih tersedia?';
        }
        $message = mb_substr($message, 0, 1000);
        $existing = $chatModel->messages($chatId);
        if (!$existing) {
            $chatModel->send($chatId, Auth::id(), $message);
        } else {
            $chatModel->send($chatId, Auth::id(), $message);
        }
        (new NotificationModel())->add(
            (int) $product['seller_id'],
            'pesan',
            'Pesan baru dari pembeli',
            Auth::user()['name'] . ' mengirim pesan tentang "' . $product['title'] . '".',
            '/chat/' . $chatId
        );

        $this->redirect('/chat/' . $chatId);
    }

    public function send(string $id): void
    {
        Auth::requireLogin();
        $chatModel = new ChatModel();
        $chat = $chatModel->findForUser((int) $id, Auth::id());
        if (!$chat) {
            if (request_wants_json()) {
                json_response(['ok' => false, 'message' => 'Percakapan tidak ditemukan.'], 404);
            }
            Session::flash('error', 'Percakapan tidak ditemukan.');
            $this->redirect('/chat');
        }

        $message = trim((string) post('message'));
        if ($message === '') {
            if (request_wants_json()) {
                json_response(['ok' => false, 'message' => 'Pesan tidak boleh kosong.'], 422);
            }
            Session::flash('error', 'Tulis pesan terlebih dahulu.');
            $this->redirect('/chat/' . $chat['id']);
        }
        if (mb_strlen($message) > 1000) {
            Session::flash('error', 'Pesan maksimal 1.000 karakter.');
            $this->redirect('/chat/' . $chat['id']);
        }

        $chatModel->send((int) $chat['id'], Auth::id(), $message);
        $otherId = (int) $chat['buyer_id'] === Auth::id() ? (int) $chat['seller_id'] : (int) $chat['buyer_id'];
        (new NotificationModel())->add(
            $otherId,
            'pesan',
            'Pesan baru dari ' . Auth::user()['name'],
            mb_strimwidth($message, 0, 80, '…'),
            '/chat/' . $chat['id']
        );

        if (request_wants_json()) {
            json_response(['ok' => true]);
        }
        $this->redirect('/chat/' . $chat['id']);
    }
}
