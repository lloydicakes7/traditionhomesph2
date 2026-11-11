<?php

namespace Tests\Feature;

use App\Http\Livewire\Admin\Accounting\JournalEntries\JournalEntryTableList;
use App\Http\Livewire\Admin\Accounting\Transfers\TransferTableList;
use App\Http\Livewire\Admin\AmenitiesBooking\Bookings\Modals\BookingMassDeleteModal;
use App\Http\Livewire\Admin\Collections\AdvanceDeposits\Modals\AdvanceDepositMassDeleteModal;
use App\Http\Livewire\Concerns\WithCsvExport;
use App\Http\Livewire\Admin\Collections\AdvanceDeposits\AdvanceDepositTableList;
use App\Http\Livewire\Admin\Collections\Invoices\Modals\InvoiceMassDeleteModal;
use App\Http\Livewire\Admin\Collections\Invoices\InvoiceTableList;
use App\Http\Livewire\Admin\Collections\MonthlyDues\Modals\MonthlyDueMassDeleteModal;
use App\Http\Livewire\Admin\Collections\MonthlyDues\MonthlyDueTableList;
use App\Http\Livewire\Admin\Collections\MonthlyDues\MonthlyDueTableMatrix;
use App\Http\Livewire\Admin\Collections\Payments\Modals\PaymentMassDeleteModal;
use App\Http\Livewire\Admin\Collections\Payments\PaymentTableList;
use App\Http\Livewire\Admin\Collections\ProductCategories\Modals\ProductCategoryMassDeleteModal;
use App\Http\Livewire\Admin\Collections\ProductCategories\ProductCategoryTableList;
use App\Http\Livewire\Admin\Collections\Products\Modals\ProductMassDeleteModal;
use App\Http\Livewire\Admin\Collections\Soa\Modals\SoaMassDeleteModal;
use App\Http\Livewire\Admin\Expenses\CategoryTableList;
use App\Http\Livewire\Admin\Expenses\ExpenseTableList;
use App\Http\Livewire\Admin\Expenses\Modals\ExpenseCategoryMassDeleteModal;
use App\Http\Livewire\Admin\Expenses\Modals\ExpenseMassDeleteModal;
use App\Http\Livewire\Admin\Expenses\Modals\SupplierMassDeleteModal;
use App\Http\Livewire\Admin\Homeowners\Homeowner\HomeownerTableList;
use App\Http\Livewire\Admin\Homeowners\Homeowner\Modals\HomeownerMassDeleteModal;
use App\Http\Livewire\Admin\Homeowners\Property\Modals\PropertyMassDeleteModal;
use App\Http\Livewire\Admin\Homeowners\Resident\Modals\ResidentMassDeleteModal;
use App\Http\Livewire\Admin\Support\Complaints\ComplaintTableList;
use App\Http\Livewire\Admin\Support\Complaints\Modals\ComplaintMassDeleteModal;
use App\Http\Livewire\Admin\Support\Requests\Modals\RequestMassDeleteModal;
use App\Http\Livewire\Admin\Support\Violations\Modals\ViolationMassDeleteModal;
use App\Models\Admin;
use App\Models\Product\ProductCategory;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The bulk delete, on the `ModalComponent` + `modal-loader.js` contract.
 *
 * Three things are worth pinning down. First, no list spends a Livewire call
 * opening the confirmation: the toolbar's "Delete Selected" button is an
 * ordinary Bootstrap trigger, it exists only when there is a selection to
 * carry, and it carries the whole set as the one `data-id` the loader reads.
 * Second, the dialog turns that single comma-separated attribute into ids,
 * deletes them, tells the list to re-read itself and hides its own shell.
 * Third, the retired hand-off is gone — `InteractsWithModals` and the
 * `confirmMassDelete()` / `openModal()` round trip it offered are not on any
 * adopter.
 */
class MassDeleteModalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every mass-delete dialog in the panel.
     *
     * @return array<int, class-string>
     */
    private function modalClasses(): array
    {
        return [
            BookingMassDeleteModal::class,
            AdvanceDepositMassDeleteModal::class,
            InvoiceMassDeleteModal::class,
            MonthlyDueMassDeleteModal::class,
            PaymentMassDeleteModal::class,
            ProductCategoryMassDeleteModal::class,
            ProductMassDeleteModal::class,
            SoaMassDeleteModal::class,
            ExpenseCategoryMassDeleteModal::class,
            ExpenseMassDeleteModal::class,
            SupplierMassDeleteModal::class,
            HomeownerMassDeleteModal::class,
            PropertyMassDeleteModal::class,
            ResidentMassDeleteModal::class,
            ComplaintMassDeleteModal::class,
            RequestMassDeleteModal::class,
            ViolationMassDeleteModal::class,
        ];
    }

    /**
     * A list that offers a bulk delete, and the dialog its toolbar opens.
     *
     * @return array<class-string, string>
     */
    private function toolbars(): array
    {
        return [
            ProductCategoryTableList::class => 'productCategoryMassDeleteModal',
            CategoryTableList::class       => 'expenseCategoryMassDeleteModal',
            ComplaintTableList::class      => 'complaintMassDeleteModal',
            HomeownerTableList::class      => 'homeownerMassDeleteModal',
            InvoiceTableList::class        => 'invoiceMassDeleteModal',
        ];
    }

    /**
     * The lists that publish a CSV download, so the shared trait's abstract
     * contract has an implementer in every one of them.
     *
     * @return array<int, class-string>
     */
    private function csvExporters(): array
    {
        return [
            JournalEntryTableList::class,
            TransferTableList::class,
            InvoiceTableList::class,
            MonthlyDueTableList::class,
            MonthlyDueTableMatrix::class,
            PaymentTableList::class,
            ExpenseTableList::class,
        ];
    }

    private function signIn(): Admin
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'mass-delete-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
        ]);

        $this->actingAs($admin, 'web');

        return $admin;
    }

    /**
     * The loader derives the event stem from the dialog's DOM id, so the id, the
     * MODAL_ID constant and the two listeners have to agree — and the view has
     * to carry the markers `isLazyModal()` keys on.
     */
    public function test_every_dialog_is_wired_to_the_loader_contract(): void
    {
        $this->signIn();

        foreach ($this->modalClasses() as $class) {
            $reflection = new \ReflectionClass($class);

            $this->assertTrue($reflection->hasConstant('MODAL_ID'), "$class names no MODAL_ID");

            $modalId = $reflection->getConstant('MODAL_ID');
            $stem = ucfirst(preg_replace('/Modal$/', '', $modalId));
            $listeners = $reflection->getDefaultProperties()['listeners'];

            $this->assertSame(
                'load',
                $listeners['load' . $stem . 'Modal'] ?? null,
                "$class does not answer load{$stem}Modal"
            );
            $this->assertSame(
                'closeModal',
                $listeners['close' . $stem . 'Modal'] ?? null,
                "$class does not answer close{$stem}Modal"
            );

            Livewire::test($class)
                ->assertSet('loaded', false)
                ->assertSee('id="' . $modalId . '"', false)
                // The loader's `isLazyModal()` keys on these two attributes, so
                // a dialog without them opens onto an endless spinner.
                ->assertSee('data-modal-skeleton class=', false)
                ->assertSee('data-modal-content class=', false);

            // ...and one of each, not two branches sharing one marker: the
            // loader flips the pair, it does not search for them.
            $view = file_get_contents($this->modalView($class));

            $this->assertSame(1, substr_count($view, 'data-modal-skeleton'));
            $this->assertSame(1, substr_count($view, 'data-modal-content'));
        }
    }

    public function test_the_toolbar_carries_the_selection_as_the_loaders_data_id(): void
    {
        $this->signIn();

        foreach ($this->toolbars() as $list => $modalId) {
            $component = Livewire::test($list);

            // No selection, no trigger: the button is only ever shown with
            // something to hand over.
            $component->assertDontSee('data-target="#' . $modalId . '"', false)
                ->assertDontSee('Delete Selected');

            // With a selection, the whole set rides along as one value.
            $component->set('selectedIds', [7, 9])
                ->assertSee('data-target="#' . $modalId . '"', false)
                ->assertSee('data-id="7,9"', false)
                ->assertSee('2 selected');
        }
    }

    public function test_the_dialog_loads_the_ids_off_the_trigger_and_deletes_them(): void
    {
        $this->signIn();

        $first  = ProductCategory::create(['name' => 'Dues', 'description' => '']);
        $second = ProductCategory::create(['name' => 'Fees', 'description' => '']);
        $kept   = ProductCategory::create(['name' => 'Permits', 'description' => '']);

        Livewire::test(ProductCategoryMassDeleteModal::class)
            ->assertSet('loaded', false)
            ->call('load', $first->id . ',' . $second->id)
            ->assertSet('loaded', true)
            ->assertSet('selectedIds', [$first->id, $second->id])
            ->call('deleteMass')
            ->assertSet('selectedIds', [])
            // The list reloads on this event; the dialog then hides itself.
            ->assertEmitted('product-category-saved')
            ->assertEmitted('notify', 'success', '2 categories deleted successfully.')
            ->assertDispatchedBrowserEvent('modal-close', ['id' => 'productCategoryMassDeleteModal']);

        $this->assertDatabaseMissing('product_categories', ['id' => $first->id]);
        $this->assertDatabaseMissing('product_categories', ['id' => $second->id]);
        $this->assertDatabaseHas('product_categories', ['id' => $kept->id]);
    }

    public function test_the_retired_hand_off_is_gone_from_the_code(): void
    {
        // The concern every list used to reach for, and the calls it offered.
        $this->assertFileNotExists(
            app_path('Http/Livewire/Admin/Concerns/InteractsWithModals.php'),
            'InteractsWithModals is still on disk'
        );

        $retired = ['InteractsWithModals', 'modalPrefix(', 'openModal(', 'closeModals(', 'confirmMassDelete'];
        $offenders = [];

        foreach ($this->sourceFiles() as $file) {
            $source = file_get_contents($file);

            foreach ($retired as $needle) {
                if (Str::contains($source, $needle)) {
                    $offenders[] = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file) . " names $needle";
                }
            }
        }

        $this->assertSame([], $offenders);

        // The export filename follows the page, so the trait states it as
        // abstract and every list that exports has to answer for itself.
        $this->assertTrue((new \ReflectionMethod(WithCsvExport::class, 'exportFileName'))->isAbstract());

        foreach ($this->csvExporters() as $class) {
            $this->assertTrue(
                (new \ReflectionClass($class))->hasMethod('exportFileName'),
                "$class exports without naming its download"
            );
        }
    }

    public function test_the_dialogs_no_longer_carry_the_lists_selection_state(): void
    {
        foreach ($this->modalClasses() as $class) {
            $reflection = new \ReflectionClass($class);

            $this->assertFalse(
                $reflection->hasProperty('selectAll'),
                "$class still holds the list's select-all flag"
            );
            $this->assertFalse(
                $reflection->hasMethod('resolveSelectedIds'),
                "$class still resolves the list's selection"
            );
        }
    }

    /**
     * The Blade view a dialog renders, from its own `render()`.
     */
    private function modalView(string $class): string
    {
        preg_match("/return view\('([^']+)'\)/", file_get_contents((new \ReflectionClass($class))->getFileName()), $match);

        return resource_path('views/' . str_replace('.', '/', $match[1]) . '.blade.php');
    }

    /**
     * Every PHP and Blade file the retired names could still hide in.
     *
     * @return array<int, string>
     */
    private function sourceFiles(): array
    {
        $files = [];

        foreach ([app_path(), resource_path('views'), public_path('js')] as $path) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

            foreach ($iterator as $file) {
                if ($file->isFile() && Str::endsWith($file->getFilename(), ['.php', '.js', '.blade.php'])) {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }
}
