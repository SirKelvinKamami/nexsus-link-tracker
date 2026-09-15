<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentService
{
    protected $disk;
    protected $basePath;

    public function __construct()
    {
        $this->disk = config('documents.storage_disk', 'local');
        $this->basePath = config('documents.storage_path', 'documents');
    }

    /**
     * Store an uploaded document file.
     */
    public function store(UploadedFile $file, int $userId): array
    {
        $fileName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();

        $storedName = Str::uuid() . '.' . $extension;
        $path = $this->getUserPath($userId) . '/' . $storedName;

        $file->storeAs($this->getUserPath($userId), $storedName, $this->disk);

        return [
            'original_name' => $fileName,
            'stored_name' => $storedName,
            'path' => $path,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'extension' => $extension,
        ];
    }

    /**
     * Delete a document file.
     */
    public function delete(string $path): bool
    {
        if ($this->disk === 'local') {
            $fullPath = storage_path('app/' . $path);
            if (file_exists($fullPath)) {
                return unlink($fullPath);
            }
            return false;
        }

        return Storage::disk($this->disk)->delete($path);
    }

    /**
     * Get the full URL for a document file.
     */
    public function getUrl(string $path): ?string
    {
        if ($this->disk === 'local') {
            return '/storage/' . $path;
        }

        return Storage::disk($this->disk)->url($path);
    }

    /**
     * Get a temporary signed URL for private documents.
     */
    public function getTemporaryUrl(string $path, int $minutes = 60): ?string
    {
        if ($this->disk === 'local') {
            return $this->getUrl($path);
        }

        return Storage::disk($this->disk)->temporaryUrl($path, now()->addMinutes($minutes));
    }

    /**
     * Get the user-specific storage path.
     */
    protected function getUserPath(int $userId): string
    {
        return $this->basePath . '/' . $userId;
    }

    /**
     * Validate an uploaded file against document rules.
     */
    public function validate(UploadedFile $file): array
    {
        $errors = [];

        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = config('documents.allowed_extensions', []);

        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = "File type '{$extension}' is not allowed. Allowed: " . implode(', ', $allowedExtensions);
        }

        $maxSize = config('documents.max_file_size', 10240) * 1024; // Convert KB to bytes
        if ($file->getSize() > $maxSize) {
            $maxMb = config('documents.max_file_size', 10240) / 1024;
            $errors[] = "File size exceeds maximum of {$maxMb}MB.";
        }

        return $errors;
    }

    /**
     * Get file type category for preview rendering.
     */
    public function getFileCategory(string $extension): string
    {
        return match ($extension) {
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'image',
            'pdf' => 'pdf',
            'doc', 'docx' => 'document',
            'xls', 'xlsx' => 'spreadsheet',
            'zip' => 'archive',
            default => 'file',
        };
    }

    /**
     * Format file size for display.
     */
    public function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 1);
        $i = (int) floor(log($bytes) / log(1024));
        $i = min($i, count($units) - 1);

        return round($bytes / (1024 ** $i), 1) . ' ' . $units[$i];
    }
}
