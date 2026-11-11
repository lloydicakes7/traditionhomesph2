<?php

namespace Tests\Feature\Rbi;

use App\Enums\RbiSemester;
use App\Models\Address;
use App\Models\Admin;
use App\Models\Household\Household;
use App\Models\Household\HouseholdMember;
use App\Models\Property;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use App\Queries\Rbi\FormAQuery;
use App\Queries\Rbi\FormBQuery;
use App\Queries\Rbi\FormCQuery;
use App\Queries\Rbi\SheetQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RbiFormsTest extends TestCase
{
    use RefreshDatabase;

    /** @var \App\Models\Household\Household */
    private $household;

    /** @var \App\Models\Household\Household */
    private $otherHousehold;

    /** @var \App\Models\Resident */
    private $head;

    /** @var \App\Models\Resident */
    private $spouse;

    /** @var \App\Models\Resident */
    private $stranger;

    /** @var \App\Models\User */
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->signInAdmin();

        $address = Address::create([
            'label' => 'TH 2',
            'subdivision' => 'Tradition Homes Ph-2',
            'barangay' => 'Inocencio',
            'city' => 'Trece Martires City',
            'province' => 'Cavite',
            'region' => 'Region IV-A (CALABARZON)',
            'zip_code' => '4107',
        ]);

        $unit = Property::create([
            'block' => 4,
            'lot' => 12,
            'address_id' => $address->id,
            'sqm' => 120,
            'type' => 'house',
        ]);

        $otherUnit = Property::create([
            'block' => 6,
            'lot' => 3,
            'address_id' => $address->id,
            'sqm' => 90,
            'type' => 'townhouse',
        ]);

        $this->head = $this->resident([
            'surname' => 'Dela Cruz',
            'first_name' => 'Juan',
            'middle_name' => 'Santos',
            'birth_date' => now()->subYears(41)->format('Y-m-d'),
            'birth_place' => 'Imus, Cavite',
            'sex' => 'male',
            'civil_status' => 'married',
            'occupation' => 'Engineer',
            'mobile_no' => '09181234567',
            'citizenship' => 'Filipino',
            'educational_attainment' => 'college_graduate',
        ]);

        $this->spouse = $this->resident([
            'surname' => 'Dela Cruz',
            'first_name' => 'Rosario',
            'middle_name' => 'Bautista',
            'birth_date' => now()->subYears(38)->format('Y-m-d'),
            'sex' => 'female',
            'civil_status' => 'married',
            'citizenship' => 'Filipino',
            'educational_attainment' => 'high_school_graduate',
        ]);

        $this->spouse->update(['sectors' => ['solo_parent']]);

        $this->user = User::create([
            'resident_id' => $this->head->id,
            'email' => 'juan.delacruz@example.test',
            'password' => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'is_active' => true,
        ]);

        $this->household = Household::create([
            'property_id' => $unit->id,
            'type' => 'owner',
        ]);

        $this->household->placeHead($this->head->id, 'father');

        HouseholdMember::create([
            'household_id' => $this->household->id,
            'resident_id' => $this->spouse->id,
            'relationship' => 'mother',
        ]);

        $this->stranger = $this->resident([
            'surname' => 'Lim',
            'first_name' => 'Anna',
            'middle_name' => 'Marie',
            'birth_date' => now()->subYears(30)->format('Y-m-d'),
            'sex' => 'female',
            'civil_status' => 'single',
            'citizenship' => 'Filipino',
            'educational_attainment' => 'college_graduate',
        ]);

        User::create([
            'resident_id' => $this->stranger->id,
            'email' => 'anna.lim@example.test',
            'password' => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'is_active' => true,
        ]);

        $this->otherHousehold = Household::create([
            'property_id' => $otherUnit->id,
            'type' => 'owner',
        ]);

        $this->otherHousehold->placeHead($this->stranger->id, 'mother');
    }

    // =====================================================================
    // Admin pages
    // =====================================================================

    public function test_the_admin_pages_pick_the_households_and_residents_they_print(): void
    {
        $this->get(route('rbi.form-a'))
            ->assertOk()
            ->assertSee('Dela Cruz Family');

        $this->get(route('rbi.form-b'))
            ->assertOk()
            ->assertSee('Rosario Bautista Dela Cruz');
    }

    public function test_the_print_documents_menu_links_the_three_forms(): void
    {
        $menu = collect(config('adminlte.menu'));

        $this->assertNull($menu->firstWhere('text', 'RBI Forms'));

        $print = $menu->firstWhere('text', 'Print Documents');
        $rbi = collect($print['submenu'])->firstWhere('text', 'RBI Forms');

        $this->assertSame(
            ['RBI Form A', 'RBI Form B', 'RBI Form C'],
            collect($rbi['submenu'])->pluck('text')->all()
        );

        $this->assertFalse(Route::has('rbi.form-d'));
        $this->assertFalse(Route::has('rbi.form-d.sheet'));
        $this->assertFalse(Route::has('rbi.form-d.download'));
    }

    public function test_an_officer_without_the_rbi_permission_is_refused(): void
    {
        $role = Role::create([
            'name' => 'Visitor',
            'description' => 'Read-only visitor',
            'permissions' => [],
        ]);

        $officer = Admin::create([
            'name' => 'Visitor Officer',
            'email' => 'rbi-visitor-' . uniqid() . '@example.test',
            'password' => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'is_active' => true,
            'role_id' => $role->id,
        ]);

        $this->actingAs($officer, 'web');

        $this->get(route('rbi.form-a'))->assertForbidden();
        $this->get(route('rbi.form-a', ['household' => $this->household->id]))->assertForbidden();
        $this->get(route('rbi.form-b'))->assertForbidden();
        $this->get(route('rbi.form-b', ['resident' => $this->head->id]))->assertForbidden();
        $this->get(route('rbi.form-c'))->assertForbidden();
    }

    // =====================================================================
    // Form A
    // =====================================================================

    public function test_form_a_previews_and_downloads_the_household_sheet(): void
    {
        $this->get(route('rbi.form-a', ['household' => $this->household->id]))
            ->assertOk()
            ->assertSee('Download PDF');

        $this->assertPdf($this->get(route('rbi.form-a.sheet', $this->household))->getContent());
        $this->assertPdf($this->get(route('rbi.form-a.download', $this->household))->getContent());
    }

    public function test_form_a_is_the_revised_household_sheet(): void
    {
        $sheet = $this->sheetHtml('pdf.rbi-form-a', app(FormAQuery::class)->sheet($this->household));

        $this->assertStringContainsString('RBI FORM A (Revised 2024)', $sheet);
        $this->assertStringContainsString('RECORDS OF BARANGAY INHABITANTS BY HOUSEHOLD', $sheet);
        $this->assertStringContainsString('NO. OF HOUSEHOLD MEMBERS:', $sheet);
        $this->assertStringContainsString('PLACE OF BIRTH', $sheet);
        $this->assertStringContainsString('Indicate if Labor/employed', $sheet);
        $this->assertStringContainsString('Name of Household/Head Member', $sheet);
        $this->assertStringContainsString('Punong Barangay', $sheet);
        $this->assertStringNotContainsString('Monthly Income', $sheet);
        $this->assertStringNotContainsString('Migrant', $sheet);
    }

    public function test_form_a_prints_every_member_and_their_indicators(): void
    {
        $data = app(FormAQuery::class)->sheet($this->household);

        $this->assertSame('2', $data['no_of_household_members']);
        $this->assertCount(FormAQuery::MEMBER_ROWS, $data['members']);
        $this->assertSame('Dela Cruz', $data['members'][0]['last_name']);
        $this->assertSame('Juan', $data['members'][0]['first_name']);
        $this->assertSame('Male', $data['members'][0]['sex']);
        $this->assertSame('', $data['members'][0]['indicators']);
        $this->assertSame('Solo Parent', $data['members'][1]['indicators']);
        $this->assertSame('', $data['members'][2]['last_name']);
    }

    // =====================================================================
    // Form B
    // =====================================================================

    public function test_form_b_previews_and_downloads_the_individual_sheet(): void
    {
        $this->get(route('rbi.form-b', ['resident' => $this->head->id]))
            ->assertOk()
            ->assertSee('Download PDF');

        $this->assertPdf($this->get(route('rbi.form-b.sheet', $this->head))->getContent());
        $this->assertPdf($this->get(route('rbi.form-b.download', $this->head))->getContent());
    }

    public function test_form_b_is_the_revised_individual_sheet(): void
    {
        $sheet = $this->sheetHtml('pdf.rbi-form-b', app(FormBQuery::class)->sheet($this->head));

        $this->assertStringContainsString('RBI Form B (Revised 2024)', $sheet);
        $this->assertStringContainsString('INDIVIDUAL RECORDS OF BARANGAY INHABITANT', $sheet);
        $this->assertStringContainsString('PERSONAL INFORMATION', $sheet);
        $this->assertStringContainsString('(PhilSys Card No.)', $sheet);
        $this->assertStringContainsString('HIGHEST EDUCATIONAL ATTAINMENT:', $sheet);
        $this->assertStringContainsString('Please specify:', $sheet);
        $this->assertStringContainsString('Attested By:', $sheet);
        $this->assertStringContainsString('Left Thumbmark', $sheet);
        $this->assertStringContainsString('Household Number:', $sheet);
        $this->assertStringNotContainsString('Blood Type', $sheet);
        $this->assertStringNotContainsString('Sectoral Information', $sheet);
        $this->assertStringNotContainsString('Mother&#039;s Maiden Name', $sheet);
    }

    public function test_form_b_reads_the_residents_record_and_the_circulars_education_boxes(): void
    {
        $data = app(FormBQuery::class)->sheet($this->spouse);

        $this->assertSame('Dela Cruz', $data['last_name']);
        $this->assertSame('Rosario', $data['first_name']);
        $this->assertSame('Female', $data['sex']);
        $this->assertSame('Married', $data['civil_status']);
        $this->assertSame('high_school', $data['education']['level']);
        $this->assertTrue($data['education']['graduate']);
        $this->assertFalse($data['education']['undergraduate']);
        $this->assertStringContainsString('Barangay Inocencio', $data['residence_address']);

        $head = app(FormBQuery::class)->sheet($this->head);

        $this->assertSame('juan.delacruz@example.test', $head['email']);
    }

    // =====================================================================
    // Form C
    // =====================================================================

    public function test_form_c_previews_and_downloads_the_monitoring_report(): void
    {
        $this->get(route('rbi.form-c', ['year' => 2026, 'semester' => 'first']))->assertOk();

        $this->assertPdf($this->get(route('rbi.form-c.sheet', ['year' => 2026, 'semester' => 'first']))->getContent());
        $this->assertPdf($this->get(route('rbi.form-c.download', ['year' => 2026, 'semester' => 'first']))->getContent());
    }

    public function test_form_c_is_the_revised_monitoring_report(): void
    {
        $sheet = $this->sheetHtml('pdf.rbi-form-c', app(FormCQuery::class)->sheet(2026, RbiSemester::FIRST()));

        $this->assertStringContainsString('RBI FORM C (Revised 2024)', $sheet);
        $this->assertStringContainsString('MONITORING REPORT', $sheet);
        $this->assertStringContainsString('for First Semester of CY 2026', $sheet);
        $this->assertStringContainsString('Population by Age Bracket:', $sheet);
        $this->assertStringContainsString('80 years old and over', $sheet);
        $this->assertStringContainsString('Person with Disabilities (PWDs)', $sheet);
        $this->assertStringContainsString('Civil Status : Single', $sheet);
        $this->assertStringContainsString('Citizenship : Filipino', $sheet);
        $this->assertStringContainsString('Barangay Secretary', $sheet);
        $this->assertStringContainsString('Date Accomplished:', $sheet);
        $this->assertStringNotContainsString('Senior Citizen', $sheet);
    }

    public function test_the_report_preview_points_its_frame_at_a_plain_semester(): void
    {
        $page = $this->get(route('rbi.form-c', ['year' => 2026, 'semester' => 'second']));

        $page->assertOk();
        $page->assertSee('year=2026&amp;semester=second', false);
        $page->assertDontSee('semester%5B', false);
    }

    // =====================================================================
    // Counts
    // =====================================================================

    public function test_age_brackets_close_on_their_boundaries(): void
    {
        $report = app(FormCQuery::class);

        foreach ([4, 5, 14, 15, 59, 60, 79, 80] as $age) {
            $resident = $this->resident([
                'surname' => 'Boundary',
                'first_name' => 'Age' . $age,
                'birth_date' => now()->subYears($age)->subMonths(2)->format('Y-m-d'),
                'sex' => $age % 2 === 0 ? 'female' : 'male',
                'civil_status' => 'single',
                'citizenship' => 'Filipino',
                'educational_attainment' => 'college_graduate',
            ]);

            HouseholdMember::create([
                'household_id' => $this->household->id,
                'resident_id' => $resident->id,
                'relationship' => 'son',
            ]);
        }

        $data = $report->sheet(2026, RbiSemester::FIRST());

        $this->assertSame(1, $data['age_brackets']['under_5']['total']);
        $this->assertSame(1, $data['age_brackets']['5_9']['total']);
        $this->assertSame(1, $data['age_brackets']['10_14']['total']);
        $this->assertSame(1, $data['age_brackets']['15_19']['total']);
        $this->assertSame(1, $data['age_brackets']['55_59']['total']);
        $this->assertSame(1, $data['age_brackets']['60_64']['total']);
        $this->assertSame(1, $data['age_brackets']['75_79']['total']);
        $this->assertSame(1, $data['age_brackets']['80_over']['total']);
    }

    public function test_sectors_and_citizenship_are_counted_per_sex(): void
    {
        $data = app(FormCQuery::class)->sheet(2026, RbiSemester::FIRST());

        $this->assertSame(1, $data['sectors']['solo_parent']['female']);
        $this->assertSame(0, $data['sectors']['solo_parent']['male']);
        $this->assertSame(2, $data['civil_status']['married']['total']);
        $this->assertSame(3, $data['citizenship']['filipino']['total']);
        $this->assertSame(0, $data['citizenship']['foreigner']['total']);
        $this->assertSame('3', $data['total_inhabitants']);
        $this->assertSame('2', $data['total_households']);
    }

    // =====================================================================
    // Portal
    // =====================================================================

    public function test_a_homeowner_prints_their_own_forms(): void
    {
        $this->actingAs($this->user, 'homeowner');

        $this->get(route('portal.rbi.index'))->assertOk();
        $this->get(route('portal.rbi.form-a', $this->household))->assertOk();
        $this->get(route('portal.rbi.form-b', $this->spouse))->assertOk();

        $this->assertPdf($this->get(route('portal.rbi.form-a.download', $this->household))->getContent());
    }

    public function test_a_homeowner_cannot_print_another_households_forms(): void
    {
        $this->actingAs($this->user, 'homeowner');

        $this->get(route('portal.rbi.form-a', $this->otherHousehold))->assertForbidden();
        $this->get(route('portal.rbi.form-b', $this->stranger))->assertForbidden();
        $this->get(route('portal.rbi.form-a.download', $this->otherHousehold))->assertForbidden();
    }

    public function test_the_portal_does_not_serve_the_semestral_report(): void
    {
        $this->actingAs($this->user, 'homeowner');

        $this->get('/portal/rbi/form-c')->assertNotFound();
    }

    // =====================================================================
    // Fixtures
    // =====================================================================

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function resident(array $attributes): Resident
    {
        return Resident::create(array_merge([
            'citizenship' => 'Filipino',
            'educational_attainment' => 'college_graduate',
        ], $attributes));
    }

    private function signInAdmin(): Admin
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name' => 'Maria Clara Villanueva',
            'email' => 'rbi-' . uniqid() . '@example.test',
            'password' => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'is_active' => true,
            'role_id' => $role->id,
        ]);

        $this->actingAs($admin, 'web');

        return $admin;
    }

    /**
     * The sheet's HTML, as the PDF builder renders it — the sheet routes stream
     * a PDF, whose text is compressed, so content is asserted on the page.
     *
     * @param  array<string, mixed>  $data
     */
    private function sheetHtml(string $view, array $data): string
    {
        return view($view, app(SheetQuery::class)->payload('', $data))->render();
    }

    private function assertPdf(string $content): void
    {
        $this->assertStringStartsWith('%PDF', $content);
        $this->assertGreaterThan(1000, strlen($content));
    }
}
