<?php

namespace App\Support;

use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ImageUploadHelper
{
    /**
     * Configure a FileUpload component for public WebP storage.
     *
     * Usage: ImageUploadHelper::make('image')->directory('products')
     * The ->directory() value will be used for the WebP file path.
     */
    public static function make(string $field): FileUpload
    {
        return FileUpload::make($field)
            ->image()
            ->disk('public')
            ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, FileUpload $component): string {
                $directory = $component->getDirectory() ?? 'uploads';

                return self::convertToWebp($file, $directory);
            });
    }

    /**
     * Convert an uploaded image to WebP and store it on the public disk.
     */
    public static function convertToWebp(TemporaryUploadedFile $file, string $directory): string
    {
        $filename = Str::uuid() . '.webp';
        $path = $directory . '/' . $filename;

        $imageData = file_get_contents($file->getRealPath());
        $image = imagecreatefromstring($imageData);

        if ($image === false) {
            // Fallback: store as-is if GD can't process it
            return $file->storeAs($directory, Str::uuid() . '.' . $file->getClientOriginalExtension(), 'public');
        }

        // Preserve transparency for PNG/WebP sources
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);

        ob_start();
        imagewebp($image, null, 85);
        $webpData = ob_get_clean();
        imagedestroy($image);

        Storage::disk('public')->put($path, $webpData);

        return $path;
    }
}
