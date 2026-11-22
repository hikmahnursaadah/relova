<?php

abstract class Controller
{
    protected function render(string $view, array $data = [], ?string $layout = 'main'): void
    {
        View::render($view, $data, $layout);
    }

    protected function redirect(string $path): never
    {
        redirect($path);
    }

    protected function validateCsrf(): void
    {
        if (!Csrf::validate()) {
            http_response_code(419);
            if (request_wants_json()) {
                json_response(['ok' => false, 'message' => 'Sesi keamanan kedaluwarsa. Muat ulang halaman.'], 419);
            }
            Session::flash('error', 'Sesi keamanan kedaluwarsa. Silakan coba lagi.');
            redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }
    }
}
