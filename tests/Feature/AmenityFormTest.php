<?php

namespace Tests\Feature;

use App\Enums\AmenityRateType;
use App\Http\Livewire\Admin\AmenitiesBooking\Amenities\Modals\AmenityFormModal;
use App\Models\Admin;
use App\Models\Amenity\Amenity;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The Add / Edit Amenity dialog.
 *
 * Two things the dialog decides: which pricing models the association bills
 * its facilities under (Fixed or Hourly — Per Block is retired), and which
 * days of the week the facility is available on — a day switched off is saved
 * as a closed day, whatever hours that row still carries.
 */
class AmenityFormTest extends TestCase
{
    use RefreshDatabase;

    private function signIn(): Admin
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'amenity-form-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
        ]);

        $this->actingAs($admin, 'web');

        return $admin;
    }

    public function test_the_rate_type_offers_only_fixed_and_hourly(): void
    {
        $this->signIn();

        $this->assertEqualsCanonicalizing(['fixed', 'hourly'], AmenityRateType::values());
        $this->assertNotContains('per_block', AmenityRateType::values());

        Livewire::test(AmenityFormModal::class)
            ->call('openCreate')
            ->assertSee('Fixed')
            ->assertSee('Hourly')
            ->assertDontSee('Per Block')
            // The block length the retired model needed is gone with it.
            ->assertDontSee('block_hours');
    }

    public function test_a_day_switched_off_is_saved_closed_whatever_hours_that_row_carries(): void
    {
        $this->signIn();

        // Every day comes open 8 AM – 5 PM. Saturday is switched off, and its
        // hours are even left pointing the wrong way round: a day the amenity
        // is not available on takes no hours at all, so nothing is asked of it.
        Livewire::test(AmenityFormModal::class)
            ->call('openCreate')
            ->set('form.name', 'Clubhouse')
            ->set('form.rate_type', 'hourly')
            ->set('form.rate', 150)
            ->set('schedule.6.opens_at', '13:00')
            ->set('schedule.6.closes_at', '07:00')
            ->set('schedule.6.enabled', false)
            ->assertSee('id="amenityDay6"', false)
            ->call('save')
            ->assertHasNoErrors();

        $amenity = Amenity::firstOrFail();

        $this->assertSame('hourly', $amenity->rate_type);

        $saturday = $amenity->schedules()->where('day_of_week', 6)->firstOrFail();
        $this->assertNull($saturday->opens_at);
        $this->assertNull($saturday->closes_at);
        $this->assertTrue($saturday->is_closed);

        // The rest of the week keeps the hours it was given.
        $monday = $amenity->schedules()->where('day_of_week', 1)->firstOrFail();
        $this->assertSame('08:00', substr((string) $monday->opens_at, 0, 5));
        $this->assertFalse($monday->is_closed);
    }

    public function test_the_hours_of_an_available_day_are_validated(): void
    {
        $this->signIn();

        Livewire::test(AmenityFormModal::class)
            ->call('openCreate')
            ->set('form.name', 'Clubhouse')
            ->set('schedule.1.closes_at', '07:00')
            ->call('save')
            ->assertHasErrors(['schedule.1.closes_at']);

        $this->assertSame(0, Amenity::count());
    }

    public function test_a_missing_field_is_named_the_way_the_form_names_it(): void
    {
        $this->signIn();

        // The message reads the field, not the key it is validated under.
        Livewire::test(AmenityFormModal::class)
            ->call('openCreate')
            ->call('save')
            ->assertHasErrors(['form.name' => 'required'])
            ->assertSee('The name field is required.');
    }

    public function test_editing_reopens_the_days_the_amenity_is_closed_on_switched_off(): void
    {
        $this->signIn();

        $amenity = Amenity::create([
            'name'        => 'Clubhouse',
            'rate_type'   => 'fixed',
            'rate'        => 1500,
            'is_bookable' => true,
        ]);

        foreach ([1, 2, 3, 4, 5, 6, 7] as $day) {
            $amenity->schedules()->create([
                'day_of_week' => $day,
                'opens_at'    => $day === 7 ? null : '08:00',
                'closes_at'   => $day === 7 ? null : '17:00',
            ]);
        }

        // The loader hands the dialog its record through the modal's own
        // listener, the way `modal-loader.js` fires it.
        Livewire::test(AmenityFormModal::class)
            ->emit('loadAmenityFormModal', $amenity->id)
            ->assertSet('schedule.7.enabled', false)
            ->assertSet('schedule.1.enabled', true);
    }
}
