<?php

namespace Tests\Feature;

use App\Actions\Settings\Print\RemovePrintImage;
use App\Actions\Settings\Print\StorePrintImage;
use App\Models\Admin;
use App\Models\Role;
use App\Queries\Documents\DocumentStaffQuery;
use App\Queries\Settings\PrintSettingsQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrintPresidentialImageTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        return Admin::create([
            'name'                => 'Officer',
            'email'               => 'president-image-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'is_active'           => true,
            'role_id'             => $role->id,
        ]);
    }

    public function test_the_presidential_image_is_stored_and_removed_through_print_settings(): void
    {
        Storage::fake('local');

        $admin = $this->admin();
        $upload = new UploadedFile(public_path('image/logo.jpg'), 'president.jpg', 'image/jpeg', null, true);

        $path = app(StorePrintImage::class)->handle('president', $upload, $admin);

        $this->assertSame($path, app(PrintSettingsQuery::class)->load()['president_image_path']);

        $photo = app(DocumentStaffQuery::class)->build()['photo'];

        $this->assertNotNull($photo);
        $this->assertStringStartsWith('data:image/jpeg', $photo);

        app(RemovePrintImage::class)->handle('president', $admin);

        $this->assertSame('', app(PrintSettingsQuery::class)->load()['president_image_path']);
    }
}
