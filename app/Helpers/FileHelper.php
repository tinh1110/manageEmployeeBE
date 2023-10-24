<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class FileHelper
{
    public static function deleteFileFromStorage($path): bool
    {
        return Storage::disk('public')->delete($path);
    }

    public static function saveFileToStorage($folder, $file, $path): bool
    {
        return Storage::putFileAs($folder, $file, $path);
    }

}
