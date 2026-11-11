<?php

namespace Tests\Feature;

use App\Enums\HouseholdType;
use App\Enums\PrintDocument;
use App\Models\Address;
use App\Models\Admin;
use App\Models\AdminActivity;
use App\Models\Household\Household;
use App\Models\Property;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use App\Services\Csv\HouseholdCsvService;
use App\Services\Csv\PropertyCsvService;
use App\Services\Csv\ResidentCsvService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * What a download is not.
 *
 * Printing a document, exporting a list as CSV and downloading a CSV template
 * are copies of what the office already has: none of them changes a record, so
 * none of them is written to the audit trail — `admin_activities` holds what was
 * created, updated or deleted. A page the office opens a hundred times a day is
 * not a page that fills the trail with lines about opening it.
 *
 * An import is the other half of the same rule and is still logged: it does
 * create the records (`ResidentCsvService::import()` and its siblings log
 * `imported`), which is why the trail is read here as empty before the download
 * and empty after it.
 */
class DownloadActivityLogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * An officer with the panel's full access.
     */
    private function signIn(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'downloads-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);

        $this->actingAs($admin, 'web');
    }

    /**
     * A resident with an account, a unit and the household that holds it — so
     * every export below has a row of its own to write.
     */
    private function household(): void
    {
        $address = Address::create([
            'label'       => 'Unit 7-12',
            'subdivision' => 'Tradition Homes',
            'barangay'    => 'Poblacion',
            'city'        => 'Santa Rosa',
            'province'    => 'Laguna',
            'region'      => 'IV-A',
        ]);

        $property = Property::create([
            'block'      => 7,
            'lot'        => 12,
            'address_id' => $address->id,
        ]);

        $user = factory(User::class)->create([
            'resident_id' => factory(Resident::class)->create()->id,
        ]);

        $household = Household::create([
            'property_id'      => $property->id,
            'type'             => HouseholdType::OWNER,
        ]);

        $household->placeHead($user->resident_id, 'father');
    }

    public function test_printing_a_document_writes_nothing_to_the_audit_trail(): void
    {
        $this->signIn();

        // The sheet in the preview frame, and the same sheet downloaded.
        $this->get(route('print.pdf', ['document' => PrintDocument::MEMBERSHIP_FORM]))->assertOk();
        $this->get(route('print.pdf', ['document' => PrintDocument::MEMBERSHIP_FORM]) . '?download=1')->assertOk();

        $this->assertSame(0, AdminActivity::query()->count());
    }

    public function test_exporting_a_list_writes_nothing_to_the_audit_trail(): void
    {
        $this->signIn();
        $this->household();

        // Every export still hands back its CSV — the download itself is
        // untouched, it is only the line about it that is gone.
        $this->assertNotSame('', app(ResidentCsvService::class)->export());
        $this->assertNotSame('', app(PropertyCsvService::class)->export());
        $this->assertNotSame('', app(HouseholdCsvService::class)->export());

        $this->assertSame(0, AdminActivity::query()->count());
    }
}
