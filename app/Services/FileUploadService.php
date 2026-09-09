<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\DomainException;

/**
 * Safe handling of uploaded product images.
 *
 * The old project moved the uploaded file to product_images/ using the name
 * the browser sent. Somebody could upload "shell.php" and then execute it.
 *
 * What this service does instead:
 *   1. checks the PHP upload error code
 *   2. checks the file size
 *   3. reads the REAL mime type from the file content (not from the browser)
 *   4. confirms the file really is an image
 *   5. generates a random file name with a safe extension
 *
 * On top of that, public/uploads/.htaccess forbids PHP execution in the
 * upload folder, so even a file that slipped through cannot run.
 */
final class FileUploadService
{
    private const MAX_BYTES = 2 * 1024 * 1024; // 2 MB

    /** Real mime type => the extension we will store the file with. */
    private const ALLOWED_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];

    public function __construct(private readonly string $directory)
    {
    }

    /**
     * Validate an uploaded file.
     *
     * @param array<string, mixed> $file one entry of $_FILES
     * @return string|null an error message, or null when the file is fine
     */
    public function validate(array $file): ?string
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($error !== UPLOAD_ERR_OK) {
            return match ($error) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The image is too large.',
                UPLOAD_ERR_PARTIAL                        => 'The image was only partially uploaded, please try again.',
                UPLOAD_ERR_NO_FILE                        => 'Please choose an image.',
                default                                   => 'The image could not be uploaded.',
            };
        }

        $tmpPath = (string) ($file['tmp_name'] ?? '');

        // Guarantees the file really came from an HTTP upload.
        if ($tmpPath === '' || !is_uploaded_file($tmpPath)) {
            return 'The image could not be uploaded.';
        }

        if ((int) ($file['size'] ?? 0) > self::MAX_BYTES) {
            return 'The image may not be larger than 2 MB.';
        }

        $mimeType = $this->detectMimeType($tmpPath);

        if ($mimeType === null || !isset(self::ALLOWED_TYPES[$mimeType])) {
            return 'Only JPG, PNG and WEBP images are allowed.';
        }

        // getimagesize() fails for a text file that merely claims to be an image.
        if (@getimagesize($tmpPath) === false) {
            return 'The uploaded file is not a valid image.';
        }

        return null;
    }

    /**
     * Move the uploaded file into the upload directory and return the new
     * (random) file name that should be stored in the database.
     *
     * @param array<string, mixed> $file
     */
    public function store(array $file): string
    {
        $error = $this->validate($file);

        if ($error !== null) {
            throw new DomainException($error);
        }

        $tmpPath   = (string) $file['tmp_name'];
        $mimeType  = (string) $this->detectMimeType($tmpPath);
        $extension = self::ALLOWED_TYPES[$mimeType];

        // Random name: the original file name is never trusted or reused.
        $filename = bin2hex(random_bytes(16)) . '.' . $extension;

        $this->ensureDirectoryExists();

        if (!move_uploaded_file($tmpPath, $this->directory . '/' . $filename)) {
            throw new DomainException('The image could not be saved. Please try again.');
        }

        @chmod($this->directory . '/' . $filename, 0644);

        return $filename;
    }

    /**
     * Remove a stored image. Ignores anything that is not a plain file name,
     * so a value like "../../config/database.php" can never be deleted.
     */
    public function delete(?string $filename): void
    {
        if ($filename === null || $filename === '' || basename($filename) !== $filename) {
            return;
        }

        $path = $this->directory . '/' . $filename;

        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function detectMimeType(string $path): ?string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if ($finfo === false) {
            return null;
        }

        $mimeType = finfo_file($finfo, $path);
        finfo_close($finfo);

        return $mimeType === false ? null : $mimeType;
    }

    private function ensureDirectoryExists(): void
    {
        if (!is_dir($this->directory) && !mkdir($this->directory, 0775, true) && !is_dir($this->directory)) {
            throw new DomainException('The upload directory is not writable.');
        }
    }
}
