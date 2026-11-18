<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Queries\Settings\GeneralSettingsQuery;
use App\Utility\StorageHelper;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * The association's logo at one fixed public address.
 *
 * Email headers and the public pages point here instead of a storage URL, so
 * the image keeps loading whether or not the server has a public storage link.
 * The logo saved in General Settings wins; the bundled mark is served when the
 * office has not uploaded one.
 */
class LogoController extends Controller
{
    public function __invoke(GeneralSettingsQuery $settings): BinaryFileResponse
    {
        $stored = StorageHelper::publicFile($settings->get('logo_path'));

        return response()->file($stored ?: public_path('image/logo.jpg'));
    }
}
