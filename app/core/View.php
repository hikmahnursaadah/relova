<?php

class View
{
    public static function render(string $template, array $data = [], ?string $layout = 'main'): void
    {
        $data['title'] = $data['title'] ?? 'Relova';
        $data['page'] = $data['page'] ?? '';
        $viewFile = dirname(__DIR__) . '/views/' . $template . '.php';

        if (!file_exists($viewFile)) {
            throw new RuntimeException('Tampilan tidak ditemukan: ' . $template);
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout === null) {
            echo $content;
            return;
        }

        $layoutFile = dirname(__DIR__) . '/views/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            throw new RuntimeException('Layout tidak ditemukan: ' . $layout);
        }
        require $layoutFile;
    }

    public static function partial(string $template, array $data = []): void
    {
        $viewFile = dirname(__DIR__) . '/views/' . $template . '.php';
        if (!file_exists($viewFile)) {
            throw new RuntimeException('Komponen tidak ditemukan: ' . $template);
        }
        extract($data, EXTR_SKIP);
        require $viewFile;
    }
}
