<?php

class ProfileController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin('Silakan masuk untuk membuka profilmu.');
        $user = Auth::user();
        $layout = Auth::isAdmin() ? 'admin' : (Auth::isSeller() ? 'seller' : 'buyer');
        $sellerStats = Auth::isSeller() ? (new OrderModel())->sellerStats(Auth::id()) : null;
        $reviews = (new ReviewModel())->forSeller(Auth::id(), 3);
        $reviewStats = (new ReviewModel())->statsForSeller(Auth::id());

        $this->render('profile/index', [
            'title' => 'Profil Saya — Relova',
            'page' => 'profile',
            'user' => $user,
            'sellerStats' => $sellerStats,
            'reviews' => $reviews,
            'reviewStats' => $reviewStats,
        ], $layout);
    }

    public function update(): void
    {
        Auth::requireLogin();
        $data = [
            'name' => trim((string) post('name')),
            'username' => strtolower(trim((string) post('username'))),
            'email' => strtolower(trim((string) post('email'))),
            'phone' => trim((string) post('phone')),
            'city' => trim((string) post('city')),
            'address' => trim((string) post('address')),
            'bio' => trim((string) post('bio')),
        ];
        $errors = [];
        if (mb_strlen($data['name']) < 2) {
            $errors[] = 'Nama minimal 2 karakter.';
        }
        if (!preg_match('/^[a-z0-9._]{3,20}$/', $data['username'])) {
            $errors[] = 'Nama pengguna harus 3–20 karakter tanpa spasi.';
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format surel belum benar.';
        }
        if (mb_strlen($data['bio']) > 280) {
            $errors[] = 'Bio maksimal 280 karakter.';
        }

        $users = new UserModel();
        $byEmail = $users->findByEmail($data['email']);
        if ($byEmail && (int) $byEmail['id'] !== Auth::id()) {
            $errors[] = 'Email ini sudah dipakai akun lain.';
        }
        $byUsername = $users->findByUsername($data['username']);
        if ($byUsername && (int) $byUsername['id'] !== Auth::id()) {
            $errors[] = 'Nama pengguna ini sudah dipakai.';
        }

        if ($errors) {
            foreach ($errors as $error) {
                Session::flash('error', $error);
            }
            Session::old($data);
            $this->redirect('/profil');
        }

        $users->updateProfile(Auth::id(), $data);
        Auth::refresh();
        Session::flash('success', 'Profil berhasil diperbarui.');
        $this->redirect('/profil');
    }

    public function password(): void
    {
        Auth::requireLogin();
        $current = (string) post('current_password');
        $new = (string) post('new_password');
        $confirmation = (string) post('password_confirmation');
        $user = Auth::user();

        if (!password_verify($current, $user['password_hash'])) {
            Session::flash('error', 'Kata sandi saat ini tidak sesuai.');
            $this->redirect('/profil#keamanan');
        }
        if (mb_strlen($new) < 8) {
            Session::flash('error', 'Kata sandi baru minimal 8 karakter.');
            $this->redirect('/profil#keamanan');
        }
        if ($new !== $confirmation) {
            Session::flash('error', 'Konfirmasi kata sandi baru belum sama.');
            $this->redirect('/profil#keamanan');
        }

        (new UserModel())->updatePassword(Auth::id(), $new);
        Session::flash('success', 'Kata sandi berhasil diperbarui.');
        $this->redirect('/profil#keamanan');
    }
}
