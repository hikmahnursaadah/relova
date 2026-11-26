<?php

class BuyerController extends Controller
{
    public function dashboard(): void
    {
        Auth::requireLogin('Silakan masuk untuk membuka ruang pembeli.');
        if (Auth::isAdmin()) {
            $this->redirect('/admin');
        }

        $orderModel = new OrderModel();
        $wishlistModel = new WishlistModel();

        $this->render('buyer/dashboard', [
            'title' => 'Ruang Pembeli — Relova',
            'page' => 'buyer-dashboard',
            'orders' => $orderModel->recentForBuyer(Auth::id(), 4),
            'stats' => $orderModel->buyerStats(Auth::id()),
            'wishlist' => array_slice($wishlistModel->items(Auth::id()), 0, 3),
            'wishlistIds' => $wishlistModel->ids(Auth::id()),
            'notifications' => (new NotificationModel())->recent(Auth::id(), 4),
            'unreadChats' => (new ChatModel())->unreadCount(Auth::id()),
            'recommendations' => (new ProductModel())->featured(3),
        ], 'buyer');
    }
}
