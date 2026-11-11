<?php

namespace Tests\Feature;

use App\Http\Livewire\Admin\Collections\Invoices\InvoiceTableList;
use App\Http\Livewire\Admin\Collections\MonthlyDues\MonthlyDueTableList;
use App\Http\Livewire\Admin\Collections\MonthlyDues\MonthlyDueTableMatrix;
use App\Models\Admin;
use App\Models\Role;
use App\Utility\PermissionHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class CollectionsExportPermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_collections_list_exports_with_its_own_export_permission(): void
    {
        $this->signIn([
            PermissionHelper::key('Invoices', 'View'),
            PermissionHelper::key('Invoices', 'Export'),
            PermissionHelper::key('Monthly Dues', 'View'),
            PermissionHelper::key('Monthly Dues', 'Export'),
        ]);

        $invoiceFile = 'invoice-export-' . now()->format('Y-m-d') . '.csv';
        $dueFile = 'due-export-' . now()->format('Y-m-d') . '.csv';
        $matrixFile = 'monthly-dues-table-export-' . now()->format('Y-m-d') . '.csv';

        Livewire::test(InvoiceTableList::class)
            ->assertSee('Export CSV')
            ->call('export')
            ->assertFileDownloaded($invoiceFile);

        Livewire::test(MonthlyDueTableList::class)
            ->assertSee('Export CSV')
            ->call('export')
            ->assertFileDownloaded($dueFile);

        Livewire::test(MonthlyDueTableMatrix::class)
            ->assertSee('Export CSV')
            ->call('export')
            ->assertFileDownloaded($matrixFile);
    }

    public function test_an_export_without_its_permission_is_hidden_from_the_list(): void
    {
        $this->signIn([
            PermissionHelper::key('Invoices', 'View'),
            PermissionHelper::key('Monthly Dues', 'View'),
            PermissionHelper::key('Monthly Dues', 'Export'),
        ]);

        Livewire::test(InvoiceTableList::class)
            ->assertDontSee('Export CSV');

        Livewire::test(MonthlyDueTableList::class)
            ->assertSee('Export CSV');

        Livewire::test(MonthlyDueTableMatrix::class)
            ->assertSee('Export CSV');
    }

    private function signIn(array $permissions): Admin
    {
        $role = Role::create([
            'name'        => 'Collections Clerk ' . uniqid(),
            'description' => 'Collections clerk',
            'permissions' => $permissions,
        ]);

        $admin = Admin::create([
            'name'                => 'Collections Clerk',
            'email'               => 'collections-export-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);

        $this->actingAs($admin, 'web');

        return $admin;
    }
}
