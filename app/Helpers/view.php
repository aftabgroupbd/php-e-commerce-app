<?php

declare(strict_types=1);

use App\Support\Session;

/**
 * View helpers: rendering templates and escaping output.
 */

if (!function_exists('e')) {
    /**
     * Escape a value before printing it in HTML.
     *
     * ALWAYS use this in views: <?= e($product->name) ?>
     * It is what stops an XSS attack such as a product named "<script>...</script>".
     */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('render_view')) {
    /**
     * Render a template file from /views and return the produced HTML.
     *
     * @param array<string, mixed> $data variables made available to the template
     */
    function render_view(string $template, array $data = []): string
    {
        $path = base_path('views/' . $template . '.php');

        if (!is_file($path)) {
            throw new RuntimeException("View [{$template}] was not found.");
        }

        extract($data, EXTR_SKIP);

        ob_start();

        try {
            require $path;
        } catch (Throwable $e) {
            ob_end_clean();

            throw $e;
        }

        return (string) ob_get_clean();
    }
}

if (!function_exists('view')) {
    /**
     * Render a template inside a layout and send it to the browser.
     *
     * view('products/index', ['products' => $products]);
     *
     * The layout receives the rendered template as $content, which is why the
     * <html> boilerplate only exists once (views/layouts/*.php).
     */
    function view(string $template, array $data = [], string $layout = 'layouts/app'): void
    {
        $content = render_view($template, $data);

        if ($layout === '') {
            echo $content;

            return;
        }

        echo render_view($layout, array_merge($data, ['content' => $content]));
    }
}

if (!function_exists('old')) {
    /**
     * Re-fill a form field after a failed validation.
     */
    function old(string $key, string $default = ''): string
    {
        /** @var array<string, mixed> $values */
        $values = $GLOBALS['_old_input'] ??= (array) Session::getFlash('old', []);

        return e($values[$key] ?? $default);
    }
}

if (!function_exists('validation_errors')) {
    /**
     * All validation errors flashed by the previous request.
     *
     * @return array<string, string>
     */
    function validation_errors(): array
    {
        /** @var array<string, string> $errors */
        $errors = $GLOBALS['_validation_errors'] ??= (array) Session::getFlash('errors', []);

        return $errors;
    }
}

if (!function_exists('error_for')) {
    /**
     * The validation error message for a single field, if any.
     */
    function error_for(string $field): ?string
    {
        return validation_errors()[$field] ?? null;
    }
}

if (!function_exists('flash_message')) {
    /**
     * One-time status message, e.g. "Product created successfully".
     *
     * @return array{type:string, text:string}|null
     */
    function flash_message(): ?array
    {
        /** @var array{type:string, text:string}|null $message */
        $message = $GLOBALS['_flash_message'] ??= Session::getFlash('status');

        return $message;
    }
}
