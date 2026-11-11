<?php

namespace Tests\Feature;

use App\Http\Livewire\Admin\Homeowners\Property\Modals\PropertyFormModal;
use App\Models\Address;
use App\Models\Admin;
use App\Models\Property;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The office's own property form records the unit key: a block and lot names
 * one property, and the same pair may not exist twice.
 */
class PropertyKeyTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_form_accepts_a_block_and_lot_no_other_unit_holds(): void
    {
        $this->address();

        $this->saveProperty(7, 12)->assertHasNoErrors();

        $this->assertSame(1, Property::count());
    }

    public function test_the_form_refuses_a_block_and_lot_another_unit_holds(): void
    {
        $this->address();

        Property::create([
            'block'      => 7,
            'lot'        => 12,
            'address_id' => Address::first()->id,
        ]);

        $this->saveProperty(7, 12)->assertHasErrors('form.lot');

        $this->assertSame(1, Property::count());
    }

    /**
     * Open the property dialog and save these values.
     */
    private function saveProperty(int $block, int $lot): \Livewire\Testing\TestableLivewire
    {
        return Livewire::test(PropertyFormModal::class)
            ->call('openCreate')
            ->set('form.block', (string) $block)
            ->set('form.lot', (string) $lot)
            ->call('save');
    }

    private function address(): Address
    {
        return Address::create([
            'label'       => 'Primary',
            'subdivision' => 'Tradition Homes',
            'barangay'    => 'Inocencio',
            'city'        => 'Trece Martires City',
            'province'    => 'Cavite',
            'region'      => 'Region IV-A (CALABARZON)',
            'country'     => 'Philippines',
            'is_primary'  => true,
        ]);
    }

    /**
     * An officer allowed to manage properties.
     */
    private function actingAsPropertyOfficer(): Admin
    {
        $role = Role::create([
            'name'        => 'Property officer',
            'permissions' => ['properties.view', 'properties.create', 'properties.edit'],
        ]);

        $admin = Admin::create([
            'name'      => 'Property Officer',
            'email'     => 'property.officer@example.com',
            'password'  => Hash::make('password'),
            'role_id'   => $role->id,
            'is_active' => true,
        ]);

        $this->actingAs($admin, 'web');

        return $admin;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAsPropertyOfficer();
    }
}
