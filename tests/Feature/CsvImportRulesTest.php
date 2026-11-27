<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Address;
use App\Models\Household\Household;
use App\Models\Household\HouseholdMember;
use App\Models\Property;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use App\Services\Csv\HouseholdCsvService;
use App\Services\Csv\PropertyCsvService;
use App\Services\Csv\ResidentCsvService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * A sheet is read only when it is the template, and a row is read only when it
 * has the required values, the right types, and does not repeat a record.
 */
class CsvImportRulesTest extends TestCase
{
    use RefreshDatabase;

    // ===== Residents =====

    public function test_a_residents_sheet_that_is_not_the_template_is_refused(): void
    {
        $file = $this->sheet(['name', 'email'], [['Ana Cruz', 'ana@example.com']]);

        try {
            $this->residents()->import($file, $this->officer());

            $this->fail('A sheet with the wrong columns was accepted.');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('missing', $e->getMessage());
        }

        $this->assertSame(0, Resident::count());
    }

    public function test_a_residents_sheet_with_only_a_header_is_refused(): void
    {
        $file = $this->sheet($this->residents()->getColumnMap(), []);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('no resident rows');

        $this->residents()->import($file, $this->officer());
    }

    public function test_a_repeated_resident_row_is_imported_once(): void
    {
        $row = $this->residentRow();

        $result = $this->residents()->import($this->residentsSheet([$row, $row]), $this->officer());

        $this->assertSame(1, $result['imported']);
        $this->assertCount(1, $result['skipped']);
        $this->assertStringContainsString('earlier in the same file', $result['skipped'][0]);
    }

    public function test_a_resident_that_is_already_saved_is_not_imported_again(): void
    {
        $file = $this->residentsSheet([$this->residentRow()]);

        $this->assertSame(1, $this->residents()->import($file, $this->officer())['imported']);

        $result = $this->residents()->import($file, $this->officer());

        $this->assertSame(0, $result['imported']);
        $this->assertStringContainsString('already saved', $result['skipped'][0]);
        $this->assertSame(1, Resident::count());
    }

    public function test_a_residents_row_with_a_wrong_type_is_skipped(): void
    {
        $file = $this->residentsSheet([
            $this->residentRow(['first_name' => 'Ana', 'email' => 'not-an-email']),
            $this->residentRow(['first_name' => 'Bea', 'birth_date' => '1990-13-01']),
            $this->residentRow(['first_name' => 'Cara', 'philsys_card_no' => '1234']),
            $this->residentRow(['first_name' => 'Dina', 'mobile_no' => 'call me']),
            $this->residentRow(['first_name' => 'Elena', 'sex' => 'Unknown']),
        ]);

        $result = $this->residents()->import($file, $this->officer());

        $this->assertSame(0, $result['imported']);
        $this->assertCount(5, $result['skipped']);
        $this->assertStringContainsString('email', $result['skipped'][0]);
        $this->assertStringContainsString('birth_date', $result['skipped'][1]);
        $this->assertStringContainsString('philsys_card_no', $result['skipped'][2]);
        $this->assertStringContainsString('mobile_no', $result['skipped'][3]);
        $this->assertStringContainsString('sex', $result['skipped'][4]);
        $this->assertSame(0, User::count());
    }

    public function test_a_residents_row_without_a_name_is_skipped(): void
    {
        $file = $this->residentsSheet([$this->residentRow(['surname' => ''])]);

        $result = $this->residents()->import($file, $this->officer());

        $this->assertSame(0, $result['imported']);
        $this->assertStringContainsString('surname and first name', $result['skipped'][0]);
    }

    // ===== Properties =====

    public function test_a_bad_property_row_is_skipped_without_losing_the_rest(): void
    {
        $this->address();

        $file = $this->propertiesSheet([
            $this->propertyRow(['block' => 'A', 'lot' => '1']),
            $this->propertyRow(['block' => '5', 'lot' => '5', 'address' => 'Nowhere']),
            $this->propertyRow(['block' => '6', 'lot' => '6', 'sqm' => 'big']),
            $this->propertyRow(['block' => '7', 'lot' => '7', 'type' => 'Castle']),
            $this->propertyRow(['block' => '8', 'lot' => '8']),
        ]);

        $result = $this->properties()->import($file, $this->officer());

        $this->assertSame(1, $result['imported']);
        $this->assertCount(4, $result['skipped']);
        $this->assertStringContainsString('block must use numbers only', $result['skipped'][0]);
        $this->assertStringContainsString('does not match a saved address', $result['skipped'][1]);
        $this->assertStringContainsString('sqm', $result['skipped'][2]);
        $this->assertStringContainsString('type', $result['skipped'][3]);
        $this->assertSame(1, Property::count());
    }

    public function test_a_repeated_property_is_imported_once(): void
    {
        $this->address();

        $row = $this->propertyRow(['block' => '4', 'lot' => '12']);

        $result = $this->properties()->import($this->propertiesSheet([$row, $row]), $this->officer());

        $this->assertSame(1, $result['imported']);
        $this->assertCount(1, $result['skipped']);
        $this->assertStringContainsString('earlier in the same file', $result['skipped'][0]);

        $again = $this->properties()->import($this->propertiesSheet([$row]), $this->officer());

        $this->assertSame(0, $again['imported']);
        $this->assertStringContainsString('already saved', $again['skipped'][0]);
    }

    public function test_a_properties_sheet_with_the_wrong_columns_is_refused(): void
    {
        $file = $this->sheet(['block', 'lot'], [['4', '12']]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('missing');

        $this->properties()->import($file, $this->officer());
    }

    // ===== Households =====

    public function test_household_rows_with_a_bad_property_type_or_head_value_are_skipped(): void
    {
        $property = $this->property(['block' => '9', 'lot' => '3']);

        $file = $this->householdsSheet([
            $this->householdRow(['property' => '999']),
            $this->householdRow(['property' => (string) $property->id, 'type' => 'Squatter']),
            $this->householdRow(['property' => (string) $property->id, 'is_head' => 'Maybe']),
            $this->householdRow(['property' => (string) $property->id, 'sex' => 'Unknown']),
            $this->householdRow(['property' => (string) $property->id]),
        ]);

        $result = $this->households()->import($file, $this->officer());

        $this->assertSame(1, $result['imported']);
        $this->assertCount(4, $result['skipped']);
        $this->assertStringContainsString('does not match a saved property', $result['skipped'][0]);
        $this->assertStringContainsString('type', $result['skipped'][1]);
        $this->assertStringContainsString('is_head', $result['skipped'][2]);
        $this->assertStringContainsString('sex', $result['skipped'][3]);
        $this->assertSame(1, HouseholdMember::count());
    }

    public function test_a_member_that_already_belongs_to_a_household_is_skipped(): void
    {
        $property = $this->property(['block' => '2', 'lot' => '2']);
        $file = $this->householdsSheet([$this->householdRow(['property' => (string) $property->id])]);

        $this->assertSame(1, $this->households()->import($file, $this->officer())['imported']);

        $result = $this->households()->import($file, $this->officer());

        $this->assertSame(0, $result['imported']);
        $this->assertStringContainsString('already belongs to a household', $result['skipped'][0]);
        $this->assertSame(1, Household::count());
    }

    // ===== Dialog and page =====

    public function test_the_dialog_hands_a_csv_file_to_the_page(): void
    {
        Livewire::actingAs($this->officer(), 'web')
            ->test('admin.modals.csv-import-modal')
            ->call('load', ['title' => 'Import Residents from CSV', 'columns' => ['surname', 'first_name']])
            ->set('importFile', UploadedFile::fake()->createWithContent('rows.csv', "surname,first_name\nCruz,Ana\n"))
            ->call('confirm')
            ->assertHasNoErrors()
            ->assertEmitted('csv-import-confirmed');
    }

    public function test_the_dialog_refuses_a_file_that_is_not_a_csv(): void
    {
        Livewire::actingAs($this->officer(), 'web')
            ->test('admin.modals.csv-import-modal')
            ->call('load')
            ->set('importFile', UploadedFile::fake()->createWithContent('rows.xlsx', 'binary'))
            ->call('confirm')
            ->assertHasErrors('importFile')
            ->assertNotEmitted('csv-import-confirmed');
    }

    public function test_the_page_sends_every_skipped_row_to_the_results_dialog(): void
    {
        $file = $this->residentsSheet([
            $this->residentRow(),
            $this->residentRow(['first_name' => 'Bea', 'email' => 'not-an-email']),
        ]);

        Livewire::actingAs($this->importOfficer(), 'web')
            ->test('admin.homeowners.resident.resident-csv')
            ->call('handleCsvImport', $file)
            ->assertEmitted('notify', 'warning', 'Imported 1 of 2 rows. 1 row skipped.')
            ->assertEmitted('csv-import-report', function (string $event, array $params): bool {
                $report = $params[0];

                return $report['title'] === 'Residents import'
                    && $report['imported'] === 1
                    && $report['error'] === null
                    && count($report['skipped']) === 1
                    && strpos($report['skipped'][0], 'Row 3:') === 0;
            });
    }

    public function test_the_results_dialog_lists_the_skipped_rows_and_the_fix(): void
    {
        Livewire::actingAs($this->officer(), 'web')
            ->test('admin.modals.csv-import-result-modal')
            ->call('show', [
                'title'    => 'Residents import',
                'imported' => 1,
                'skipped'  => ['Row 3: email "not-an-email" is not a valid email address.'],
                'error'    => null,
            ])
            ->assertSet('imported', 1)
            ->assertSee('Rows that were skipped')
            ->assertSee('How to fix it')
            ->assertSee('Row 3');
    }

    public function test_the_results_dialog_explains_a_file_it_refused(): void
    {
        Livewire::actingAs($this->officer(), 'web')
            ->test('admin.modals.csv-import-result-modal')
            ->call('show', ['error' => 'The header row is missing the column "email".'])
            ->assertSee('Nothing was imported.')
            ->assertSee('The header row is missing the column "email".')
            ->assertSee('How to fix it');
    }

    public function test_the_households_page_offers_its_own_csv_buttons(): void
    {
        $admin = $this->makeAdmin(['households.import', 'households.export']);

        Livewire::actingAs($admin, 'web')
            ->test('admin.homeowners.household.household-csv')
            ->assertSee('Download Template')
            ->assertSee('Import CSV')
            ->assertSee('Export CSV');
    }

    // ===== Fixtures =====

    private function residents(): ResidentCsvService
    {
        return app(ResidentCsvService::class);
    }

    private function properties(): PropertyCsvService
    {
        return app(PropertyCsvService::class);
    }

    private function households(): HouseholdCsvService
    {
        return app(HouseholdCsvService::class);
    }

    private function residentsSheet(array $rows): string
    {
        return $this->sheet($this->residents()->getColumnMap(), $rows);
    }

    private function propertiesSheet(array $rows): string
    {
        return $this->sheet($this->properties()->getColumnMap(), $rows);
    }

    private function householdsSheet(array $rows): string
    {
        return $this->sheet($this->households()->getColumnMap(), $rows);
    }

    private function residentRow(array $values = []): array
    {
        return $this->row($this->residents()->getColumnMap(), array_merge([
            'surname'                => 'Cruz',
            'first_name'             => 'Ana',
            'birth_date'             => '1990-01-01',
            'sex'                    => 'Female',
            'civil_status'           => 'Single',
            'citizenship'            => 'Filipino',
            'educational_attainment' => 'College Graduate',
        ], $values));
    }

    private function propertyRow(array $values = []): array
    {
        return $this->row($this->properties()->getColumnMap(), array_merge([
            'block'   => '4',
            'lot'     => '12',
            'address' => 'Primary',
            'sqm'     => '100',
            'type'    => 'house',
        ], $values));
    }

    private function householdRow(array $values = []): array
    {
        return $this->row($this->households()->getColumnMap(), array_merge([
            'is_head'                => 'Yes',
            'surname'                => 'Cruz',
            'first_name'             => 'Ana',
            'birth_date'             => '1990-01-01',
            'sex'                    => 'Female',
            'civil_status'           => 'Single',
            'citizenship'            => 'Filipino',
            'educational_attainment' => 'College Graduate',
        ], $values));
    }

    private function row(array $columns, array $values): array
    {
        return array_map(function (string $column) use ($values) {
            return $values[$column] ?? '';
        }, $columns);
    }

    private function sheet(array $header, array $rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'csv') . '.csv';
        $handle = fopen($path, 'w');

        fputcsv($handle, $header);

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);

        return $path;
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

    private function property(array $values = []): Property
    {
        return Property::create(array_merge([
            'block'      => 4,
            'lot'        => 12,
            'address_id' => $this->address()->id,
        ], $values));
    }

    private function officer(): Admin
    {
        return $this->makeAdmin(['residents.import', 'residents.export']);
    }

    private function importOfficer(): Admin
    {
        return $this->makeAdmin(['residents.import', 'residents.export', 'properties.import', 'households.import']);
    }

    private function makeAdmin(array $permissions): Admin
    {
        $role = Role::create([
            'name'        => uniqid('role'),
            'permissions' => $permissions,
        ]);

        return Admin::create([
            'name'      => 'Officer',
            'email'     => uniqid('officer') . '@example.com',
            'password'  => Hash::make('password'),
            'role_id'   => $role->id,
            'is_active' => true,
        ]);
    }
}
