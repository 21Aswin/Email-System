<?php
declare(strict_types=1);

namespace App\Core;

class View
{
    private static string $viewsPath = '';

    public static function init(string $path): void
    {
        self::$viewsPath = rtrim($path, DIRECTORY_SEPARATOR) . '/';
    }

    /**
     * Render a view wrapped in a layout
     */
    public static function render(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        $content = self::renderPartial($view, $data);
        
        if ($layout) {
            $layoutFile = self::$viewsPath . $layout . '.php';
            if (file_exists($layoutFile)) {
                // Pass $content and all $data to the layout
                extract($data);
                require $layoutFile;
                return;
            }
        }

        echo $content;
    }

    /**
     * Render a view partial and return as string
     */
    public static function renderPartial(string $view, array $data = []): string
    {
        $file = self::$viewsPath . $view . '.php';
        if (!file_exists($file)) {
            throw new \RuntimeException("View file not found: {$file}");
        }

        extract($data);
        ob_start();
        require $file;
        return ob_get_clean() ?: '';
    }

    /**
     * Helper to safely escape HTML in templates
     */
    public static function e(?string $string): string
    {
        return htmlspecialchars((string)($string ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
