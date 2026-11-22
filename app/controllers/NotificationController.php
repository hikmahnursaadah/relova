<?php

class NotificationController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin('Silakan masuk untuk melihat notifikasi.');
        $notificationModel = new NotificationModel();
        $notificationModel->markAllRead(Auth::id());
        $layout = Auth::isAdmin() ? 'admin' : (Auth::isSeller() ? 'seller' : 'buyer');
        $this->render('notifications/index', [
            'title' => 'Notifikasi — re:wear',
            'page' => 'notifications',
            'notifications' => $notificationModel->all(Auth::id(), 1, 30),
        ], $layout);
    }

    public function markAll(): void
    {
        Auth::requireLogin();
        (new NotificationModel())->markAllRead(Auth::id());
        back('/');
    }

    public function markRead(string $id): void
    {
        Auth::requireLogin();
        $notification = null;
        foreach ((new NotificationModel())->recent(Auth::id(), 50) as $item) {
            if ((int) $item['id'] === (int) $id) {
                $notification = $item;
                break;
            }
        }
        (new NotificationModel())->markRead((int) $id, Auth::id());
        if ($notification && $notification['link']) {
            $this->redirect($notification['link']);
        }
        back('/notifikasi');
    }
}
