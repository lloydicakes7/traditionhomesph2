<?php

namespace App\Utility;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DatabaseBackupHelper
{
    public static function directory(): string
    {
        return 'backups';
    }

    public static function ensureDirectory(): void
    {
        if (! Storage::disk('local')->exists(self::directory())) {
            Storage::disk('local')->makeDirectory(self::directory());
        }
    }

    public static function create(): string
    {
        self::ensureDirectory();

        $connection = config('database.connections.' . config('database.default'));
        $database = $connection['database'] ?? '';
        $username = $connection['username'] ?? '';
        $password = $connection['password'] ?? '';
        $host = $connection['host'] ?? '127.0.0.1';
        $port = $connection['port'] ?? '3306';

        if (empty($database) || empty($username)) {
            throw new \RuntimeException('Database connection is not configured for backup creation.');
        }

        $filename = 'backup-' . date('Y-m-d-H-i-s') . '.sql';
        $path = self::directory() . '/' . $filename;
        $fullPath = Storage::disk('local')->path($path);
        $dumpBinary = self::dumpBinary();

        if (empty($dumpBinary)) {
            throw new \RuntimeException('The MySQL dump utility (mysqldump) is not available on this server.');
        }

        $command = sprintf(
            '%s --user=%s --password=%s --host=%s --port=%s --single-transaction --routines --triggers --databases %s > %s 2>&1',
            escapeshellarg($dumpBinary),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($database),
            escapeshellarg($fullPath)
        );

        exec($command, $output, $exitCode);

        if ($exitCode !== 0 || ! file_exists($fullPath) || filesize($fullPath) === 0) {
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            $message = implode(' ', $output);
            throw new \RuntimeException(trim($message) ?: 'Database backup could not be created.');
        }

        return $filename;
    }

    public static function all(): array
    {
        self::ensureDirectory();

        $files = Storage::disk('local')->files(self::directory());
        $backups = [];

        foreach ($files as $file) {
            if (substr($file, -4) !== '.sql') {
                continue;
            }

            $realPath = Storage::disk('local')->path($file);
            $backups[] = [
                'name' => basename($file),
                'size' => self::humanReadableBytes(filesize($realPath)),
                'size_bytes' => filesize($realPath),
                'created_at' => date('Y-m-d H:i:s', filemtime($realPath)),
            ];
        }

        usort($backups, function ($a, $b) {
            return strcmp($b['name'], $a['name']);
        });

        return $backups;
    }

    public static function delete(string $filename): bool
    {
        $filename = basename($filename);
        $path = self::directory() . '/' . $filename;

        return Storage::disk('local')->delete($path);
    }

    public static function download(string $filename): BinaryFileResponse
    {
        $path = self::filePath($filename);

        if (! file_exists($path)) {
            abort(404, 'Backup file not found.');
        }

        return response()->download($path, basename($filename));
    }

    public static function filePath(string $filename): string
    {
        $filename = basename($filename);

        return Storage::disk('local')->path(self::directory() . '/' . $filename);
    }

    public static function restore(string $filename): void
    {
        $path = self::filePath($filename);

        if (! file_exists($path)) {
            throw new \RuntimeException('Backup file not found: ' . $filename);
        }

        $connection = config('database.connections.' . config('database.default'));
        $database = $connection['database'] ?? '';
        $username = $connection['username'] ?? '';
        $password = $connection['password'] ?? '';
        $host = $connection['host'] ?? '127.0.0.1';
        $port = $connection['port'] ?? '3306';

        if (empty($database) || empty($username)) {
            throw new \RuntimeException('Database connection is not configured for restore.');
        }

        $mysqlBinary = self::mysqlBinary();

        if (empty($mysqlBinary)) {
            throw new \RuntimeException('The MySQL client utility is not available on this server.');
        }

        $command = sprintf(
            '%s --user=%s --password=%s --host=%s --port=%s %s < %s 2>&1',
            escapeshellarg($mysqlBinary),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($database),
            escapeshellarg($path)
        );

        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            $message = implode(' ', $output);
            throw new \RuntimeException(trim($message) ?: 'Database restore could not be completed.');
        }
    }

    protected static function mysqlBinary(): string
    {
        $candidates = [
            'mysql',
            'mysql.exe',
            'C:/xampp/mysql/bin/mysql.exe',
            'C:/xampp/mysql/bin/mysql',
            'C:/Program Files/MySQL/MySQL Server 8.0/bin/mysql.exe',
            'C:/Program Files/MySQL/MySQL Server 5.7/bin/mysql.exe',
            '/usr/bin/mysql',
            '/usr/local/bin/mysql',
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }

            if (DIRECTORY_SEPARATOR === '\\') {
                $resolved = @exec('where ' . $candidate . ' 2> NUL');
            } else {
                $resolved = @exec('which ' . $candidate . ' 2>/dev/null');
            }

            if (! empty($resolved) && file_exists(trim($resolved))) {
                return trim($resolved);
            }
        }

        return '';
    }

    protected static function dumpBinary(): string
    {
        $candidates = [
            'mysqldump',
            'mysqldump.exe',
            'C:/xampp/mysql/bin/mysqldump.exe',
            'C:/xampp/mysql/bin/mysqldump',
            'C:/Program Files/MySQL/MySQL Server 8.0/bin/mysqldump.exe',
            'C:/Program Files/MySQL/MySQL Server 5.7/bin/mysqldump.exe',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }

            if (DIRECTORY_SEPARATOR === '\\') {
                $resolved = @exec('where ' . $candidate . ' 2> NUL');
            } else {
                $resolved = @exec('which ' . $candidate . ' 2>/dev/null');
            }

            if (! empty($resolved) && file_exists(trim($resolved))) {
                return trim($resolved);
            }
        }

        return '';
    }

    protected static function humanReadableBytes($bytes): string
    {
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2) . ' GB';
        }

        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }

        return $bytes . ' B';
    }
}
