<?php

namespace Tests\Feature;

use App\Http\Livewire\Admin\AmenitiesBooking\Bookings\BookingTableList;
use App\Http\Livewire\Admin\Collections\AdvanceDeposits\AdvanceDepositTableList;
use App\Http\Livewire\Admin\Collections\Invoices\InvoiceTableList;
use App\Http\Livewire\Admin\Collections\MonthlyDues\MonthlyDueTableList;
use App\Http\Livewire\Admin\Collections\Payments\PaymentTableList;
use App\Http\Livewire\Admin\Collections\ProductCategories\ProductCategoryTableList;
use App\Http\Livewire\Admin\Collections\Products\ProductTableList;
use App\Http\Livewire\Admin\Collections\Soa\SoaTableList;
use App\Http\Livewire\Admin\Expenses\CategoryTableList;
use App\Http\Livewire\Admin\Expenses\ExpenseTableList;
use App\Http\Livewire\Admin\Expenses\SupplierTableList;
use App\Http\Livewire\Admin\Homeowners\Homeowner\HomeownerTableList;
use App\Http\Livewire\Admin\Homeowners\Property\PropertyTableList;
use App\Http\Livewire\Admin\Homeowners\Resident\ResidentTableList;
use App\Http\Livewire\Admin\Support\Complaints\ComplaintTableList;
use App\Http\Livewire\Admin\Support\Requests\RequestTableList;
use App\Http\Livewire\Admin\Support\Violations\ViolationTableList;
use App\Models\Admin;
use App\Models\Product\ProductCategory;
use App\Models\Role;
use App\Utility\PermissionHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The mass-delete selection belongs to an officer who may delete.
 *
 * A list's selection column, its rows' checkboxes and its cards' checkboxes are
 * one feature: they exist so the toolbar's bulk delete has something to carry.
 * An officer without the module's Delete permission may still open the page,
 * but is offered no checkbox and no bulk delete — and because the header cell
 * and the row cells come and go together, no officer is ever handed a table
 * whose rows no longer line up with its headings.
 *
 * Both halves are pinned here: what every list view guards, read from the view
 * itself so a new list cannot quietly skip the guard, and what two officers
 * actually receive when the list renders.
 */
class MassSelectPolicyTest extends TestCase
{
    use RefreshDatabase;

    /** The officer who may read every list and delete nothing. */
    const READ_ONLY_ROLE = 'Read Only Officer';

    /**
     * Every list that offers a bulk delete, and the label its toolbar uses.
     *
     * @return array<class-string, string>
     */
    private function lists(): array
    {
        return [
            BookingTableList::class         => 'Delete Selected',
            AdvanceDepositTableList::class  => 'Delete Selected',
            InvoiceTableList::class         => 'Delete Selected',
            MonthlyDueTableList::class      => 'Delete Selected',
            PaymentTableList::class         => 'Delete Selected',
            ProductCategoryTableList::class => 'Delete Selected',
            ProductTableList::class         => 'Delete Selected',
            SoaTableList::class             => 'Delete Selected',
            CategoryTableList::class        => 'Delete Selected',
            ExpenseTableList::class         => 'Delete Selected',
            SupplierTableList::class        => 'Delete Selected',
            HomeownerTableList::class       => 'Delete Selected',
            PropertyTableList::class        => 'Delete Selected',
            ResidentTableList::class        => 'Delete Selected',
            ComplaintTableList::class       => 'Delete Selected',
            RequestTableList::class         => 'Delete Selected',
            ViolationTableList::class       => 'Mass Delete',
        ];
    }

    public function test_an_officer_without_the_delete_permission_is_offered_no_selection(): void
    {
        foreach ($this->lists() as $list => $label) {
            $short = class_basename($list);

            // The officer who may delete: the selection column and the bulk
            // delete are both there, so the guard never hides them from the
            // officer the feature is for.
            $this->signIn();
            $deleter = Livewire::test($list);
            $deleter->assertSee('id="selectAll"', false);
            $deleter->set('selectedIds', [1])->assertSee($label);
            $deleterHtml = Livewire::test($list)->lastRenderedDom;

            // The same page, for an officer who may read it and no more: no
            // select-all, no row binding, and no bulk delete even when a
            // selection is set from outside the UI.
            $this->signIn(self::READ_ONLY_ROLE);
            $reader = Livewire::test($list);
            $reader->assertDontSee('id="selectAll"', false);
            $reader->assertDontSee('wire:model="selectedIds"', false);
            $reader->set('selectedIds', [1])->assertDontSee($label);
            $readerHtml = Livewire::test($list)->lastRenderedDom;

            // The bookings and payments tables fold the checkbox into their
            // first heading so the five columns never change; the other lists
            // give selection a heading of its own and must lose exactly that one.
            if (in_array($list, [BookingTableList::class, PaymentTableList::class], true)) {
                $this->assertSame(
                    substr_count($deleterHtml, '<th'),
                    substr_count($readerHtml, '<th'),
                    "$short changes its five headings when the officer may not delete"
                );

                continue;
            }

            // The heading the officer still sees is the heading they had, less
            // the one selection column — the rows lose the matching cell.
            $this->assertSame(
                substr_count($deleterHtml, '<th') - 1,
                substr_count($readerHtml, '<th'),
                "$short loses more than the selection column when the officer may not delete"
            );
        }
    }

    public function test_the_rows_keep_pace_with_the_headings_an_officer_may_see(): void
    {
        $category = ProductCategory::create(['name' => 'Dues', 'description' => '']);

        $this->signIn();
        $deleter = Livewire::test(ProductCategoryTableList::class)->lastRenderedDom;

        $this->signIn(self::READ_ONLY_ROLE);
        $reader = Livewire::test(ProductCategoryTableList::class)->lastRenderedDom;

        // The row is there for both officers — the page did not change, only
        // what each of them may pick from it.
        $this->assertSame(1, substr_count($deleter, 'id="category-' . $category->id . '"'));
        $this->assertSame(0, substr_count($reader, 'id="category-' . $category->id . '"'));

        // The row lost exactly the cell its heading lost, so the columns still
        // line up under their own headers.
        $this->assertSame(
            substr_count($deleter, '<td') - 1,
            substr_count($reader, '<td'),
            'the product categories row no longer lines up with its headings'
        );
    }

    public function test_every_list_guards_the_whole_selection_with_its_own_delete_policy(): void
    {
        foreach ($this->lists() as $list => $label) {
            $short = class_basename($list);
            $view  = $this->listView($list);
            $audit = $this->guardAudit($view, $label);

            $this->assertSame(
                substr_count(file_get_contents($view), '@can('),
                substr_count(file_get_contents($view), '@endcan'),
                "$short leaves a @can unclosed"
            );

            $this->assertNotNull($audit['trigger'], "$short has no guarded \"$label\" trigger");

            // The header cell, the rows' checkboxes and the cards' checkboxes.
            $this->assertSame(
                3,
                count($audit['controls']),
                "$short no longer has a selection control in the header, the rows and the cards"
            );

            foreach ($audit['controls'] as $number => $guard) {
                $this->assertSame(
                    $audit['trigger'],
                    $guard,
                    "$short picks a record outside the toolbar's own delete policy (line $number)"
                );
            }

            $this->assertSame(0, $audit['unclosed'], "$short leaves a @can unclosed");
        }
    }

    /**
     * What a list view guards, read in one pass: the policy its toolbar trigger
     * sits behind, and the policy guarding each selection control.
     *
     * @return array{trigger: string|null, controls: array<int, string|null>, unclosed: int}
     */
    private function guardAudit(string $view, string $label): array
    {
        $stack   = [];
        $trigger = null;
        $controls = [];
        $line    = 0;

        foreach (file($view) as $source) {
            $line++;

            if (Str::contains($source, '@endcan')) {
                array_pop($stack);
                continue;
            }

            if (preg_match('/@can\(\s*\'([^\']+)\'\s*,\s*([^\)]+)\)/', $source, $match)) {
                $stack[] = trim($match[1]) . '(' . trim($match[2]) . ')';
                continue;
            }

            $guard = $stack ? end($stack) : null;

            if (Str::contains($source, 'label="' . $label . '"')) {
                $trigger = $guard;
            }

            if (Str::contains($source, 'wire:model="selectAll"') || Str::contains($source, 'wire:model="selectedIds"')) {
                $controls[$line] = $guard;
            }
        }

        return ['trigger' => $trigger, 'controls' => $controls, 'unclosed' => count($stack)];
    }

    /**
     * The Blade view a list renders, from its own `render()`.
     */
    private function listView(string $class): string
    {
        preg_match("/return view\('([^']+)'/", file_get_contents((new \ReflectionClass($class))->getFileName()), $match);

        return resource_path('views/' . str_replace('.', '/', $match[1]) . '.blade.php');
    }

    /**
     * Sign in as the Administrator (every permission) or as the read-only role.
     */
    private function signIn(?string $roleName = null): Admin
    {
        $roleName = $roleName ?: 'Administrator';

        $role = Role::firstOrCreate(
            ['name' => $roleName],
            [
                'description' => $roleName,
                'permissions' => $roleName === self::READ_ONLY_ROLE ? $this->readOnlyKeys() : [],
            ]
        );

        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'mass-select-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
        ]);

        $this->actingAs($admin, 'web');

        return $admin;
    }

    /**
     * Every module's View key: enough to open each list, nothing that deletes.
     *
     * @return array<int, string>
     */
    private function readOnlyKeys(): array
    {
        return array_values(array_filter(PermissionHelper::keys(), function ($key) {
            return Str::endsWith($key, '.view');
        }));
    }
}
