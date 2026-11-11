<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Admin;
use App\Models\Household\Household;
use App\Models\Household\HouseholdMember;
use App\Models\Property;
use App\Models\Resident;
use App\Models\Role;
use App\Services\Csv\HouseholdCsvService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * The household sheet names a relationship once: a listed relationship is
 * written as it is, and an "Others" relationship is written in the member's own
 * words and read back the same way.
 */
class HouseholdCsvTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_export_keeps_one_relationship_column(): void
    {
        $household = $this->household();

        $household->placeHead($this->resident('Cruz', 'Ana')->id, 'mother');

        HouseholdMember::create([
            'household_id' => $household->id,
            'resident_id'  => $this->resident('Cruz', 'Lito')->id,
            'relationship' => 'Uncle',
        ]);

        $rows = array_map('str_getcsv', array_filter(explode("\n", trim($this->service()->export()))));
        $header = $rows[0];
        $column = array_search('relationship', $header, true);

        $this->assertNotContains('relationship_other', $header);
        $this->assertEqualsCanonicalizing(['mother', 'Uncle'], array_column(array_slice($rows, 1), $column));
    }

    public function test_an_import_reads_a_listed_relationship_and_the_members_own_words(): void
    {
        $this->actingAs($this->officer(), 'web');

        $property = $this->property();
        $sheet = $this->sheet($property->id);

        $result = $this->service()->import($sheet);

        $this->assertSame(2, $result['imported']);

        $members = HouseholdMember::with('resident')->get()->keyBy('resident.first_name');

        $this->assertSame('mother', $members['Ana']->relationship);
        $this->assertSame('Uncle', $members['Lito']->relationship);

        @unlink($sheet);
    }

    // ===== Fixtures =====

    private function service(): HouseholdCsvService
    {
        return app(HouseholdCsvService::class);
    }

    private function household(): Household
    {
        return Household::create([
            'property_id'      => $this->property()->id,
            'type'             => 'owner',
        ]);
    }

    private function property(): Property
    {
        return Property::create([
            'block'      => 4,
            'lot'        => 12,
            'address_id' => $this->address()->id,
        ]);
    }

    private function address(): Address
    {
        return Address::firstOrCreate(['label' => 'Primary'], [
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
     * The officer importing the sheet.
     */
    private function officer(): Admin
    {
        $role = Role::create([
            'name'        => 'Homeowner officer',
            'permissions' => ['homeowners.view', 'homeowners.create'],
        ]);

        return Admin::create([
            'name'      => 'Homeowner Officer',
            'email'     => 'officer@example.com',
            'password'  => Hash::make('password'),
            'role_id'   => $role->id,
            'is_active' => true,
        ]);
    }

    private function resident(string $surname, string $firstName): Resident
    {
        return Resident::create([
            'surname'                => $surname,
            'first_name'             => $firstName,
            'birth_date'             => '1990-01-01',
            'sex'                    => 'female',
            'civil_status'           => 'single',
            'citizenship'            => 'Filipino',
            'educational_attainment' => 'college_graduate',
        ]);
    }

    /**
     * A two-row sheet: one listed relationship and one the member described.
     */
    private function sheet(int $propertyId): string
    {
        $path = tempnam(sys_get_temp_dir(), 'household') . '.csv';

        $handle = fopen($path, 'w');

        $columns = $this->service()->getColumnMap();

        fputcsv($handle, $columns);

        foreach ([
            ['Ana', 'Mother', 'Yes'],
            ['Lito', 'Uncle', 'No'],
        ] as [$firstName, $relationship, $isHead]) {
            fputcsv($handle, array_map(function ($column) use ($propertyId, $firstName, $relationship, $isHead) {
                return [
                    'property'               => $propertyId,
                    'type'                   => 'owner',
                    'is_head'                => $isHead,
                    'relationship'           => $relationship,
                    'surname'                => 'Cruz',
                    'first_name'             => $firstName,
                    'birth_date'             => '1990-01-01',
                    'sex'                    => 'female',
                    'civil_status'           => 'single',
                    'citizenship'            => 'Filipino',
                    'educational_attainment' => 'college_graduate',
                ][$column] ?? '';
            }, $columns));
        }

        fclose($handle);

        return $path;
    }
}
