<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Represents the incoming HTTP request.
 *
 * Controllers read input through this object instead of touching the
 * $_GET / $_POST / $_FILES superglobals directly.
 */
final class Request
{
    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $body
     * @param array<string, mixed> $files
     */
    private function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $query,
        private readonly array $body,
        private readonly array $files,
    ) {
    }

    public static function capture(): self
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $path   = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // Remove the sub folder the app is installed in, e.g. "/assessment/public".
        $basePath = (string) config('app.base_path');

        if ($basePath !== '' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath));
        }

        $path = '/' . trim($path, '/');

        return new self($method, $path === '/' ? '/' : rtrim($path, '/'), $_GET, $_POST, $_FILES);
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    /**
     * Read a POST value. Always a trimmed string (or the default).
     */
    public function input(string $key, ?string $default = null): ?string
    {
        $value = $this->body[$key] ?? null;

        if (is_array($value)) {
            return $default;
        }

        return $value === null ? $default : trim((string) $value);
    }

    /**
     * Read a query string value.
     */
    public function query(string $key, ?string $default = null): ?string
    {
        $value = $this->query[$key] ?? null;

        if (is_array($value)) {
            return $default;
        }

        return $value === null ? $default : trim((string) $value);
    }

    /**
     * Read an integer value from the request body (0 when missing/invalid).
     */
    public function integer(string $key, int $default = 0): int
    {
        $value = $this->input($key);

        return is_numeric($value) ? (int) $value : $default;
    }

    /**
     * @return array<string, mixed> the raw POST payload
     */
    public function all(): array
    {
        return $this->body;
    }

    /**
     * @param string[] $keys
     * @return array<string, string|null>
     */
    public function only(array $keys): array
    {
        $data = [];

        foreach ($keys as $key) {
            $data[$key] = $this->input($key);
        }

        return $data;
    }

    /**
     * @return array<string, mixed>|null the raw $_FILES entry
     */
    public function file(string $key): ?array
    {
        $file = $this->files[$key] ?? null;

        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        return $file;
    }
}
