<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Utility\StorageHelper;
use Illuminate\Support\Facades\Storage;

/**
 * The one way a submitted valid ID is read back.
 *
 * The file is on the private disk and has no public URL; an officer with the
 * Residents view permission downloads it here, and every other request is
 * refused before a byte of the image is read.
 */
class ResidentValidIdController extends Controller
{
    /**
     * Stream the resident's submitted ID.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function show(Resident $resident)
    {
        $this->authorize('viewValidId', $resident);

        $path = $resident->valid_id_path;

        abort_unless($path && StorageHelper::privateExists($path), 404);

        return Storage::disk('local')->download(
            $path,
            'valid-id-' . $resident->id . '.' . pathinfo($path, PATHINFO_EXTENSION)
        );
    }
}
