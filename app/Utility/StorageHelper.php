<?php

namespace App\Utility;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Domain-agnostic photo/file storage helper.
 *
 * Centralizes store/delete logic for uploaded images (resident profile
 * photos, property pictures, admin profile photos) so that Services
 * never repeat the same Storage::disk('public')->... calls.
 */
class StorageHelper
{
    /**
     * Store an uploaded file under the given directory on the public disk.
     *
     * @param  UploadedFile  $file
     * @param  string        $directory  e.g. 'photos/residents'
     * @return string        Stored file path
     */
    public static function storePhoto(UploadedFile $file, string $directory = 'photos'): string
    {
        return $file->store($directory, 'public');
    }

    /**
     * Store a resident profile photo.
     */
    public static function storeResidentPhoto(UploadedFile $file): string
    {
        return static::storePhoto($file, 'photos/residents');
    }

    /**
     * Store a property picture.
     */
    public static function storePropertyPhoto(UploadedFile $file): string
    {
        return static::storePhoto($file, 'photos/properties');
    }

    /**
     * Store an uploaded file under the given directory on the public disk (generic).
     *
     * @param  UploadedFile  $file
     * @param  string        $directory  e.g. 'photos/properties'
     * @return string        Stored file path
     */
    public static function storeFile(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'public');
    }

    /**
     * Delete a file from the public disk by its stored path (generic).
     *
     * @param  string|null  $path
     * @return void
     */
    public static function deleteFile(?string $path): void
    {
        if (! empty($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Delete a file from the public disk by its stored path.
     *
     * @param  string|null  $path
     * @return void
     */
    public static function deletePhoto(?string $path): void
    {
        if (! empty($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    /**
     * Public URL for a stored file, or the given fallback when no path is set
     * or the file is no longer on disk — the public pages' logo resolution.
     *
     * @param  string|null  $path
     * @param  string       $fallback  URL to use when there is no stored file
     * @return string
     */
    public static function photoUrl(?string $path, string $fallback = ''): string
    {
        if (! empty($path) && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return $fallback;
    }

    /**
     * Replace an existing photo: delete the old one and store the new one.
     *
     * @param  UploadedFile  $file
     * @param  string|null   $existingPath  Old file path to delete (nullable)
     * @param  string        $directory     Storage subdirectory
     * @return string        New stored path
     */
    public static function replacePhoto(UploadedFile $file, ?string $existingPath, string $directory = 'photos'): string
    {
        static::deletePhoto($existingPath);

        return static::storePhoto($file, $directory);
    }

    // =====================================================================
    // Private disk
    // -----------------------------------------------------------------
    // Files that are never publicly served — uploaded documents, the print
    // letterhead's own images — live on the private disk (storage/app) and are
    // read back only by the code that prints them.
    // =====================================================================

    /**
     * Store an uploaded file on the private disk under the given directory.
     *
     * @param  UploadedFile  $file
     * @param  string        $directory  e.g. 'documents/print-settings'
     * @return string        Stored file path
     */
    public static function storePrivate(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'local');
    }

    /**
     * Delete a file from the private disk by its stored path.
     *
     * @param  string|null  $path
     * @return void
     */
    public static function deletePrivate(?string $path): void
    {
        if (! empty($path)) {
            Storage::disk('local')->delete($path);
        }
    }

    /**
     * Whether a file exists on the private disk.
     *
     * @param  string|null  $path
     * @return bool
     */
    public static function privateExists(?string $path): bool
    {
        return ! empty($path) && Storage::disk('local')->exists($path);
    }

    /**
     * Read a private file back as a data URI, so a PDF renderer can embed it
     * without a public URL or a filesystem path leaking into the document.
     *
     * @param  string|null  $path
     * @return string|null  "data:image/png;base64,…", or null when there is no file
     */
    public static function privateDataUri(?string $path): ?string
    {
        if (! static::privateExists($path)) {
            return null;
        }

        $disk = Storage::disk('local');
        $mime = $disk->mimeType($path) ?: static::mimeFromExtension($path);

        return 'data:' . $mime . ';base64,' . base64_encode((string) $disk->get($path));
    }

    /**
     * A private image as a data URI for a PDF renderer to embed.
     *
     * dompdf embeds JPEG from its bytes alone, but decodes PNG and the other
     * formats with GD — so a server without the GD extension can print a JPEG
     * letterhead and nothing else. Returning null for a format the server
     * cannot decode is what keeps a PDF from throwing "the PHP GD extension is
     * required" mid-render.
     *
     * @param  string|null  $path
     * @return string|null
     */
    public static function privatePdfImage(?string $path): ?string
    {
        $uri = static::privateDataUri($path);

        if ($uri === null) {
            return null;
        }

        $isJpeg = strpos($uri, 'data:image/jpeg') === 0;

        return $isJpeg || static::supportsImageDecoding() ? $uri : null;
    }

    /**
     * Whether this PHP can decode an image beyond a JPEG — i.e. whether the GD
     * extension is loaded. One check, so the upload rules and the PDF renderer
     * agree on what the server can handle.
     */
    public static function supportsImageDecoding(): bool
    {
        return function_exists('imagecreatefromstring');
    }

    /**
     * A best-effort mime type when the disk cannot report one.
     */
    private static function mimeFromExtension(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return [
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
        ][$extension] ?? 'application/octet-stream';
    }
}
