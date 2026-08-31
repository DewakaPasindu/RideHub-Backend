<?php

namespace App\Services\Shared;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    /**
     * Upload a file.
     */
    public function upload(
        UploadedFile $file,
        string $directory,
        string $disk = 'public'
    ): string {

        $extension = $file->getClientOriginalExtension();

        $filename = Str::uuid() . '.' . $extension;

        return $file->storeAs(
            $directory,
            $filename,
            $disk
        );
    }

    /**
     * Delete a file.
     */
    public function delete(
        ?string $path,
        string $disk = 'public'
    ): bool {

        if (!$path) {
            return false;
        }

        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }

        return false;
    }

    /**
     * Replace an existing file.
     */
    public function replace(
        UploadedFile $file,
        ?string $oldPath,
        string $directory,
        string $disk = 'public'
    ): string {

        $this->delete($oldPath, $disk);

        return $this->upload(
            $file,
            $directory,
            $disk
        );
    }

    /**
     * Check if file exists.
     */
    public function exists(
        string $path,
        string $disk = 'public'
    ): bool {

        return Storage::disk($disk)->exists($path);
    }

    /**
     * Generate public URL.
     */
    public function url(
        string $path,
        string $disk = 'public'
    ): string {

        return Storage::disk($disk)->url($path);
    }
}