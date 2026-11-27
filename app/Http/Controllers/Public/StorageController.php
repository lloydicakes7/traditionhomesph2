<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StorageController extends Controller
{
    public function show(string $path)
    {
        $base = realpath(storage_path('app/public'));
        $file = realpath($base.DIRECTORY_SEPARATOR.$path);

        if ($file === false || strpos($file, $base) !== 0 || ! is_file($file)) {
            abort(404);
        }

        return response()->file($file);
    }
}
