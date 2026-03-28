<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PresentationFileService
{
    /**
     * Save presentation slides to a JSON file
     *
     * @param int $userId
     * @param string $presentationId
     * @param array $slides
     * @return array ['path' => string, 'size' => int]
     */
    public function saveSlides($userId, $presentationId, array $slides)
    {
        $directory = "presentations/{$userId}";
        $filename = "{$presentationId}.json";
        $path = "{$directory}/{$filename}";

        // Ensure directory exists
        Storage::disk('local')->makeDirectory($directory);

        // Compress and save slides
        $jsonData = json_encode($slides, JSON_PRETTY_PRINT);
        $compressed = gzencode($jsonData, 9);
        
        Storage::disk('local')->put($path, $compressed);

        return [
            'path' => $path,
            'size' => strlen($compressed)
        ];
    }

    /**
     * Load presentation slides from a JSON file
     *
     * @param string $filePath
     * @return array|null
     */
    public function loadSlides($filePath)
    {
        if (!Storage::disk('local')->exists($filePath)) {
            return null;
        }

        $compressed = Storage::disk('local')->get($filePath);
        $jsonData = gzdecode($compressed);
        
        if ($jsonData === false) {
            // Try reading as uncompressed JSON (backward compatibility)
            $jsonData = $compressed;
        }

        return json_decode($jsonData, true);
    }

    /**
     * Delete presentation slides file
     *
     * @param string $filePath
     * @return bool
     */
    public function deleteSlides($filePath)
    {
        if (Storage::disk('local')->exists($filePath)) {
            return Storage::disk('local')->delete($filePath);
        }

        return true;
    }

    /**
     * Get file size
     *
     * @param string $filePath
     * @return int
     */
    public function getFileSize($filePath)
    {
        if (Storage::disk('local')->exists($filePath)) {
            return Storage::disk('local')->size($filePath);
        }

        return 0;
    }

    /**
     * Copy slides file for backup
     *
     * @param string $sourcePath
     * @param int $userId
     * @param string $backupId
     * @return string|null New backup path
     */
    public function copyForBackup($sourcePath, $userId, $backupId)
    {
        if (!Storage::disk('local')->exists($sourcePath)) {
            return null;
        }

        $directory = "presentations/{$userId}/backups";
        $filename = "{$backupId}.json";
        $backupPath = "{$directory}/{$filename}";

        Storage::disk('local')->makeDirectory($directory);
        Storage::disk('local')->copy($sourcePath, $backupPath);

        return $backupPath;
    }

    /**
     * Export slides as downloadable JSON
     *
     * @param string $filePath
     * @return string|null JSON string
     */
    public function exportSlides($filePath)
    {
        $slides = $this->loadSlides($filePath);
        
        if ($slides === null) {
            return null;
        }

        return json_encode($slides, JSON_PRETTY_PRINT);
    }

    /**
     * Import slides from JSON string
     *
     * @param string $jsonString
     * @param int $userId
     * @param string $presentationId
     * @return array ['path' => string, 'size' => int]
     */
    public function importSlides($jsonString, $userId, $presentationId)
    {
        $slides = json_decode($jsonString, true);
        
        if ($slides === null) {
            throw new \InvalidArgumentException('Invalid JSON format');
        }

        return $this->saveSlides($userId, $presentationId, $slides);
    }

    /**
     * Get storage statistics for a user
     *
     * @param int $userId
     * @return array
     */
    public function getUserStorageStats($userId)
    {
        $directory = "presentations/{$userId}";
        
        if (!Storage::disk('local')->exists($directory)) {
            return [
                'total_files' => 0,
                'total_size' => 0,
                'total_size_formatted' => '0 B'
            ];
        }

        $files = Storage::disk('local')->allFiles($directory);
        $totalSize = 0;

        foreach ($files as $file) {
            $totalSize += Storage::disk('local')->size($file);
        }

        return [
            'total_files' => count($files),
            'total_size' => $totalSize,
            'total_size_formatted' => $this->formatBytes($totalSize)
        ];
    }

    /**
     * Format bytes to human readable format
     *
     * @param int $bytes
     * @return string
     */
    protected function formatBytes($bytes)
    {
        if ($bytes === 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Clean up old backup files (older than specified days)
     *
     * @param int $userId
     * @param int $daysOld
     * @return int Number of files deleted
     */
    public function cleanupOldBackups($userId, $daysOld = 90)
    {
        $directory = "presentations/{$userId}/backups";
        
        if (!Storage::disk('local')->exists($directory)) {
            return 0;
        }

        $files = Storage::disk('local')->allFiles($directory);
        $cutoffTime = now()->subDays($daysOld)->timestamp;
        $deletedCount = 0;

        foreach ($files as $file) {
            $lastModified = Storage::disk('local')->lastModified($file);
            
            if ($lastModified < $cutoffTime) {
                Storage::disk('local')->delete($file);
                $deletedCount++;
            }
        }

        return $deletedCount;
    }
}
