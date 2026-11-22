<?php

class AuthController extends Controller
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect(Auth::isAdmin() ? '/admin' : '/');
        }
        $this->render('auth/login', [
            'title' => 'Masuk — re:wear',
            'page' => 'auth',
            'next' => query('next', ''),
        ], 'main');
    }

    public function login(): void
    {
        $email = strtolower(trim((string) post('email')));
        $password = (string) post('password');
        $next = Auth::safeNext((string) post('next'), '');

        if ($email === '' || $password === '') {
            Session::flash('error', 'Email dan kata sandi wajib diisi.');
            Session::old(['email' => $email, 'next' => $next]);
            $this->redirect('/masuk' . ($next ? '?next=' . urlencode($next) : ''));
        }

        $user = (new UserModel())->findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            Session::flash('error', 'Email atau kata sandi tidak sesuai.');
            Session::old(['email' => $email, 'next' => $next]);
            $this->redirect('/masuk' . ($next ? '?next=' . urlencode($next) : ''));
        }
        if ($user['status'] !== 'aktif') {
            Session::flash('error', 'Akun ini sedang ditangguhkan. Hubungi tim re:wear untuk bantuan.');
            Session::old(['email' => $email]);
            $this->redirect('/masuk');
        }

        Auth::loginById((int) $user['id']);
        Session::flash('success', 'Selamat datang kembali, ' . $user['name'] . '.');
        if ($next) {
            $this->redirect($next);
        }
        if ($user['role'] === 'admin') {
            $this->redirect('/admin');
        }
        $this->redirect('/');
    }

    public function showRegister(): void
    {
        if (Auth::check()) {
            $this->redirect('/');
        }
        $this->render('auth/register', [
            'title' => 'Buat Akun — re:wear',
            'page' => 'auth',
        ], 'main');
    }

    public function register(): void
    {
        $data = [
            'name' => trim((string) post('name')),
            'username' => strtolower(trim((string) post('username'))),
            'email' => strtolower(trim((string) post('email'))),
            'password' => (string) post('password'),
            'password_confirmation' => (string) post('password_confirmation'),
            'city' => trim((string) post('city')),
        ];
        $errors = [];

        if (mb_strlen($data['name']) < 2) {
            $errors[] = 'Nama lengkap minimal 2 karakter.';
        }
        if (!preg_match('/^[a-z0-9._]{3,20}$/', $data['username'])) {
            $errors[] = 'Nama pengguna harus 3–20 karakter dan hanya memakai huruf, angka, titik, atau garis bawah.';
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email belum benar.';
        }
        if (mb_strlen($data['password']) < 8) {
            $errors[] = 'Kata sandi minimal 8 karakter.';
        }
        if ($data['password'] !== $data['password_confirmation']) {
            $errors[] = 'Konfirmasi kata sandi belum sama.';
        }
        if (!post('terms')) {
            $errors[] = 'Kamu perlu menyetujui ketentuan komunitas re:wear.';
        }

        $users = new UserModel();
        if ($users->findByEmail($data['email'])) {
            $errors[] = 'Email ini sudah terdaftar. Coba masuk atau gunakan email lain.';
        }
        if ($users->findByUsername($data['username'])) {
            $errors[] = 'Nama pengguna ini sudah dipakai.';
        }

        if ($errors) {
            foreach ($errors as $error) {
                Session::flash('error', $error);
            }
            Session::old($data);
            $this->redirect('/daftar');
        }

        $id = $users->create($data);
        Auth::loginById($id);
        Session::flash('success', 'Akunmu berhasil dibuat. Selamat datang di re:wear.');
        $this->redirect('/');
    }

    public function logout(): void
    {
        Auth::logout();
        Session::flash('info', 'Kamu telah keluar dari akun.');
        $this->redirect('/');
    }
}
