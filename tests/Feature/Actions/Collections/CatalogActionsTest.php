<?php

namespace Tests\Feature\Actions\Collections;

use App\Actions\Collections\PaymentMethods\CreatePaymentMethod;
use App\Actions\Collections\PaymentMethods\DeletePaymentMethod;
use App\Actions\Collections\PaymentMethods\UpdatePaymentMethod;
use App\Actions\Collections\ProductCategories\CreateProductCategory;
use App\Actions\Collections\ProductCategories\DeleteProductCategories;
use App\Actions\Collections\ProductCategories\ToggleProductCategoryActive;
use App\Actions\Collections\ProductCategories\UpdateProductCategory;
use App\Actions\Collections\Products\CreateProduct;
use App\Actions\Collections\Products\DeleteProducts;
use App\Actions\Collections\Products\ToggleProductActive;
use App\Actions\Collections\Products\UpdateProduct;
use App\Models\Account\Account;
use App\Models\Admin;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CatalogActionsTest extends TestCase
{
    use RefreshDatabase;

    // ===== Products =====

    public function test_creating_a_product_writes_it_and_logs(): void
    {
        $admin = $this->admin();

        $product = app(CreateProduct::class)->handle([
            'name'       => 'Vehicle sticker',
            'type'       => 'fee',
            'price'      => 250,
            'is_active'  => true,
        ], $admin);

        $this->assertSame(250.0, (float) $product->price);
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'created',
            'module'   => 'product',
        ]);
    }

    public function test_updating_a_product_keeps_it_where_the_office_put_it(): void
    {
        $admin = $this->admin();
        $product = $this->product('Old name', 100);

        $updated = app(UpdateProduct::class)->handle($product, [
            'name'      => 'New name',
            'type'      => 'fee',
            'price'     => 150,
            'is_active' => true,
        ], $admin);

        $this->assertSame('New name', $updated->name);
        $this->assertSame(150.0, (float) $updated->price);
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'updated',
            'module'   => 'product',
        ]);
    }

    public function test_toggling_and_deleting_products(): void
    {
        $admin = $this->admin();
        $product = $this->product('Gate pass', 50);

        $this->assertFalse(app(ToggleProductActive::class)->handle($product, $admin));
        $this->assertSame(1, app(DeleteProducts::class)->handle([$product->id], $admin));
        $this->assertNull(Product::find($product->id));
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'deleted',
            'module'   => 'product',
        ]);
    }

    // ===== Categories =====

    public function test_creating_a_category_opens_its_income_account(): void
    {
        $admin = $this->admin();

        $category = app(CreateProductCategory::class)->handle([
            'name'      => 'Assessment',
            'is_active' => true,
        ], $admin);

        $this->assertNotNull($category->account_id);
        $this->assertSame('Assessment', Account::find($category->account_id)->name);
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'created',
            'module'   => 'product_category',
        ]);
    }

    public function test_a_category_can_be_renamed_toggled_and_deleted(): void
    {
        $admin = $this->admin();
        $category = app(CreateProductCategory::class)->handle(['name' => 'Old'], $admin);

        $renamed = app(UpdateProductCategory::class)->handle($category, ['name' => 'Renamed'], $admin);

        $this->assertSame('Renamed', $renamed->name);
        $this->assertSame('Renamed', Account::find($renamed->account_id)->name);

        $this->assertTrue(app(ToggleProductCategoryActive::class)->handle($renamed, $admin));
        $this->assertSame(1, app(DeleteProductCategories::class)->handle([$renamed->id], $admin));
        $this->assertNull(ProductCategory::find($renamed->id));
    }

    // ===== Payment methods =====

    public function test_a_new_payment_method_is_placed_last(): void
    {
        $admin = $this->admin();

        PaymentMethod::create(['name' => 'Cash', 'is_active' => true, 'sort_order' => 3]);

        $method = app(CreatePaymentMethod::class)->handle([
            'name'      => 'GCash',
            'is_active' => true,
        ], null, $admin);

        $this->assertSame(4, (int) $method->sort_order);
        $this->assertDatabaseHas('admin_activities', [
            'admin_id' => $admin->id,
            'action'   => 'created',
            'module'   => 'payment_method',
        ]);
    }

    public function test_renaming_a_payment_method_leaves_its_place_alone(): void
    {
        $admin = $this->admin();
        $method = PaymentMethod::create(['name' => 'Cash', 'is_active' => true, 'sort_order' => 2]);

        $renamed = app(UpdatePaymentMethod::class)->handle($method, [
            'name'      => 'Cash at the office',
            'is_active' => true,
        ], null, false, $admin);

        $this->assertSame(2, (int) $renamed->sort_order);
        $this->assertSame('Cash at the office', $renamed->name);
    }

    public function test_a_payment_method_payments_went_through_is_not_deleted(): void
    {
        $admin = $this->admin();
        $method = PaymentMethod::create(['name' => 'Cash', 'is_active' => true, 'sort_order' => 1]);
        $user = factory(User::class)->create();

        $bill = \App\Models\Bill\Bill::create([
            'reference'      => 'INV-' . uniqid(),
            'user_id'        => $user->id,
            'billed_to_name' => $user->name,
            'type'           => 'invoice',
            'bill_date'      => now()->toDateString(),
            'subtotal'       => 100,
            'total'          => 100,
            'balance'        => 100,
            'status'         => 'unpaid',
        ]);

        Payment::create([
            'bill_id'           => $bill->id,
            'user_id'           => $user->id,
            'payment_method_id' => $method->id,
            'amount'            => 100,
            'paid_at'           => now(),
        ]);

        $this->assertFalse(app(DeletePaymentMethod::class)->handle($method, $admin));
        $this->assertNotNull(PaymentMethod::find($method->id));
    }

    // ===== Helpers =====

    private function admin(): Admin
    {
        return Admin::create([
            'name'                => 'Officer',
            'email'               => 'catalog-action-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'is_active'           => true,
        ]);
    }

    private function product(string $name, float $price): Product
    {
        return Product::create([
            'name'      => $name,
            'type'      => 'fee',
            'price'     => $price,
            'is_active' => true,
        ]);
    }
}
