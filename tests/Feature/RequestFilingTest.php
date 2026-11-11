<?php

namespace Tests\Feature;

use App\Enums\BusinessPermitPurpose;
use App\Enums\HouseholdType;
use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Enums\PrintDocument;
use App\Http\Livewire\Admin\Support\Requests\Modals\RequestFormModal;
use App\Http\Livewire\Portal\Requests\Modals\RequestFileModal;
use App\Queries\Documents\MembershipFormDocumentQuery;
use App\Services\PrintDocuments\PrintDocumentService;
use App\Models\Address;
use App\Models\Household\Household;
use App\Models\Property;
use App\Models\Resident;
use App\Models\Request as RequestModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Filing a request: the five documents the portal offers, and the inputs each
 * one asks for.
 *
 * The five are fixed (RequestType): Membership Form, Business Permit, HOA
 * Permit, Endorsement Letter and Certification. The type states its own shape,
 * so the form, the validation and the office's own filing all read one source —
 * this file pins down what each type actually requires of the homeowner, that
 * the RBI forms are not requests at all, and that a portal filing is written
 * under the account's own resident rather than one named on the form.
 */
class RequestFilingTest extends TestCase
{
    use RefreshDatabase;

    // =====================================================================
    // What the portal offers
    // =====================================================================

    public function test_the_portal_offers_exactly_the_five_documents(): void
    {
        $this->signInHomeowner();

        $form = Livewire::test(RequestFileModal::class)->emit('openRequestFileModal');

        $form->assertSee(RequestType::MEMBERSHIP_FORM);
        $form->assertSee('Membership Form');
        $form->assertSee('Business Permit');
        $form->assertSee('HOA Permit');
        $form->assertSee('Endorsement Letter');
        $form->assertSee('Certification');
        $form->assertDontSee('RBI Form');

        $this->assertSame([
            RequestType::MEMBERSHIP_FORM,
            RequestType::BUSINESS_PERMIT,
            RequestType::HOA_PERMIT,
            RequestType::ENDORSEMENT_LETTER,
            RequestType::CERTIFICATION,
        ], array_keys(RequestType::labels()));
    }

    public function test_the_rbi_form_is_not_a_request_document(): void
    {
        // The RBI forms are the association's own report to the barangay, not a
        // document a homeowner asks the office to issue.
        $this->assertNull(RequestType::tryFrom('rbi_form'));
        $this->assertNotContains('RBI Form', RequestType::labels());
    }

    public function test_the_form_shows_the_inputs_each_type_asks_for(): void
    {
        $this->signInHomeowner();

        $form = function (string $type) {
            return Livewire::test(RequestFileModal::class)
                ->emit('openRequestFileModal')
                ->set('form.type', $type);
        };

        // Printed from the homeowner's own record: nothing to type, and the
        // record is shown back instead.
        $form(RequestType::MEMBERSHIP_FORM)->assertSee('Membership Form details');
        $form(RequestType::MEMBERSHIP_FORM)->assertSee('Household Members');
        $form(RequestType::MEMBERSHIP_FORM)->assertDontSee('Business Name');

        // "Resident since" is gone from the certification form.
        $form(RequestType::CERTIFICATION)
            ->assertSee('Purpose')
            ->assertDontSee('Resident Since');

        $form(RequestType::BUSINESS_PERMIT)
            ->assertSee('Business Name')
            ->assertSee('Nature of Business')
            ->assertSee('Hours of Operation')
            ->assertDontSee('Specify Purpose');

        // "Other" is the one purpose that needs its text written out.
        $form(RequestType::BUSINESS_PERMIT)
            ->set('form.purpose', BusinessPermitPurpose::OTHER)
            ->assertSee('Specify Purpose');

        $form(RequestType::HOA_PERMIT)
            ->assertSee('Permit Type')
            ->assertSee('Location')
            ->assertSee('Scope of Work')
            ->assertDontSee('Business Name');

        // The respondent's unit is read from the household, never typed in, and
        // the letter's date starts at today.
        $form(RequestType::ENDORSEMENT_LETTER)
            ->assertSee('Addressee')
            ->assertSee('Respondent Block')
            ->assertSee('Respondent Lot')
            ->assertSee('Date')
            ->assertSet('form.letter_date', now()->toDateString());
    }

    // =====================================================================
    // The one filled in from the homeowner's own record
    // =====================================================================

    public function test_a_membership_form_is_refused_while_the_record_on_file_has_blanks(): void
    {
        // A resident with no household on file: the form would print without a
        // block and a lot, so the office completes the record first.
        $this->signInHomeowner();

        Livewire::test(RequestFileModal::class)
            ->emit('openRequestFileModal')
            ->set('form.type', RequestType::MEMBERSHIP_FORM)
            ->assertSee('missing information on file')
            ->call('save')
            ->assertHasErrors(['form.type']);

        $this->assertSame(0, RequestModel::count());
    }

    public function test_a_membership_form_files_from_a_complete_household_record(): void
    {
        $account = $this->signInHomeownerWithHousehold();

        Livewire::test(RequestFileModal::class)
            ->emit('openRequestFileModal')
            ->set('form.type', RequestType::MEMBERSHIP_FORM)
            ->assertSee('Dela Cruz')
            ->assertSee('Roman Catholic')
            ->assertSee('Household Members')
            ->assertSee('Name of the Owner', false)
            ->call('save')
            ->assertHasNoErrors()
            ->assertEmitted('request-filed');

        $this->assertDatabaseHas('requests', [
            'resident_id' => $account->resident_id,
            'type'        => RequestType::MEMBERSHIP_FORM,
            'status'      => RequestStatus::PENDING,
        ]);

        // The sheet the office prints is filled in from the same record.
        $form = app(MembershipFormDocumentQuery::class)->build([
            'residentId' => $account->resident_id,
        ]);

        $this->assertSame('Dela Cruz', $form['surname']);
        $this->assertSame('7', $form['block']);
        $this->assertSame('Owner', $form['member_type']);
        $this->assertSame('Dela Cruz, Juan Santos', $form['owner_name']);
    }

    public function test_the_portal_never_asks_the_homeowner_for_the_missing_details(): void
    {
        $account = $this->signInHomeownerWithHousehold();

        // Religion is optional and may stay blank; a birthplace is not, and the
        // record has none.
        $account->resident->update(['religion' => null, 'birth_place' => null]);

        // The form names what is missing and stops there: the office completes
        // the records, so the portal offers no inputs of its own.
        Livewire::test(RequestFileModal::class)
            ->emit('openRequestFileModal')
            ->set('form.type', RequestType::MEMBERSHIP_FORM)
            ->assertSee('missing information on file')
            ->assertDontSee('Missing on the record')
            ->assertDontSee('membership_birth_place')
            ->call('save')
            ->assertHasErrors(['form.type']);

        $this->assertSame(0, RequestModel::count());
    }

    public function test_the_office_supplies_a_blank_detail_while_filing(): void
    {
        $account = $this->signInHomeownerWithHousehold();

        $account->resident->update(['religion' => null, 'birth_place' => null]);

        $this->signInAdmin();

        // The office's own form keeps the optional inputs; what it supplies is
        // used for this one form and never written back to the profile.
        Livewire::test(RequestFormModal::class)
            ->emit('openRequestFormModal')
            ->set('form.resident_id', $account->resident_id)
            ->set('form.type', RequestType::MEMBERSHIP_FORM)
            ->assertSee('Missing on the record')
            ->set('form.membership.birth_place', 'Imus, Cavite')
            ->call('save')
            ->assertHasNoErrors();

        $request = RequestModel::first();

        $this->assertSame(['birth_place' => 'Imus, Cavite'], $request->membership_data);
        $this->assertNull($account->resident->fresh()->birth_place);

        // ...and the sheet the office prints carries it.
        $form = app(MembershipFormDocumentQuery::class)->build($request->documentParams());

        $this->assertSame('Imus, Cavite', $form['birth_place']);
        $this->assertSame('', $form['religion']);
    }

    public function test_a_membership_form_prints_no_attainment_the_record_does_not_have(): void
    {
        $account = $this->signInHomeownerWithHousehold();

        $account->resident->update(['educational_attainment' => null]);

        $form = app(MembershipFormDocumentQuery::class)->build([
            'residentId' => $account->resident_id,
        ]);

        $this->assertSame('', $form['education']);
    }

    public function test_the_office_completes_a_blank_detail_before_printing(): void
    {
        $account = $this->signInHomeownerWithHousehold();

        $account->resident->household->update(['prev_address' => null]);

        $this->signInAdmin();

        // The print page's own values are the document's parameters, so a detail
        // typed there reaches the sheet without touching the record.
        $form = app(PrintDocumentService::class)->build(PrintDocument::MEMBERSHIP_FORM(), [
            'residentId'       => $account->resident_id,
            'previous_address' => 'Bacoor, Cavite',
        ]);

        $this->assertSame('Bacoor, Cavite', $form['previous_address']);
        $this->assertNull($account->resident->household->fresh()->prev_address);
    }

    // =====================================================================
    // Certification
    // =====================================================================

    public function test_a_certification_requires_a_purpose(): void
    {
        $this->signInHomeowner();

        Livewire::test(RequestFileModal::class)
            ->emit('openRequestFileModal')
            ->set('form.type', RequestType::CERTIFICATION)
            ->call('save')
            ->assertHasErrors(['form.purpose' => 'required']);

        Livewire::test(RequestFileModal::class)
            ->emit('openRequestFileModal')
            ->set('form.type', RequestType::CERTIFICATION)
            ->set('form.purpose', 'residency')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('requests', [
            'type'    => RequestType::CERTIFICATION,
            'purpose' => 'residency',
        ]);

        // The form no longer asks when the homeowner moved in.
        $this->assertNull(RequestModel::first()->resident_since);
    }

    // =====================================================================
    // Business permit
    // =====================================================================

    public function test_a_business_permit_requires_the_business_it_is_for(): void
    {
        $this->signInHomeowner();

        Livewire::test(RequestFileModal::class)
            ->emit('openRequestFileModal')
            ->set('form.type', RequestType::BUSINESS_PERMIT)
            ->call('save')
            ->assertHasErrors([
                'form.business_name'      => 'required',
                'form.nature_of_business' => 'required',
                'form.hours_of_operation' => 'required',
                'form.purpose'            => 'required',
            ]);

        $this->assertSame(0, RequestModel::count());
    }

    public function test_a_business_permit_for_another_purpose_requires_its_own_text(): void
    {
        $this->signInHomeowner();

        $form = fn () => Livewire::test(RequestFileModal::class)
            ->emit('openRequestFileModal')
            ->set('form.type', RequestType::BUSINESS_PERMIT)
            ->set('form.business_name', 'Sari-Sari Store')
            ->set('form.nature_of_business', 'Retail')
            ->set('form.hours_of_operation', '8:00 AM – 8:00 PM')
            ->set('form.purpose', 'other');

        $form()->call('save')->assertHasErrors(['form.purpose_other' => 'required']);

        $form()->set('form.purpose_other', 'Delivery hub')->call('save')->assertHasNoErrors();

        $this->assertDatabaseHas('requests', [
            'type'          => RequestType::BUSINESS_PERMIT,
            'purpose'       => 'other',
            'purpose_other' => 'Delivery hub',
        ]);
    }

    // =====================================================================
    // HOA permit and endorsement letter
    // =====================================================================

    public function test_an_hoa_permit_requires_its_type_and_scope_of_work(): void
    {
        $this->signInHomeownerWithHousehold();

        Livewire::test(RequestFileModal::class)
            ->emit('openRequestFileModal')
            ->set('form.type', RequestType::HOA_PERMIT)
            ->call('save')
            ->assertHasErrors([
                'form.permit_type'   => 'required',
                'form.location'      => 'required',
                'form.scope_of_work' => 'required',
            ]);

        Livewire::test(RequestFileModal::class)
            ->emit('openRequestFileModal')
            ->set('form.type', RequestType::HOA_PERMIT)
            ->set('form.permit_type', 'Construction')
            ->set('form.location', 'Blk 7 Lot 12')
            ->set('form.scope_of_work', 'Extension of the carport')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('requests', [
            'type'           => RequestType::HOA_PERMIT,
            'permit_type'    => 'Construction',
            'location'       => 'Blk 7 Lot 12',
            'scope_of_work'  => 'Extension of the carport',
        ]);

        // The permit the office prints carries the same location.
        $request = RequestModel::first();

        $this->assertSame('Blk 7 Lot 12', $request->printParams()['location']);

        $permit = app(PrintDocumentService::class)->build(
            PrintDocument::HOA_PERMIT(),
            $request->documentParams()
        );

        $this->assertSame('Blk 7 Lot 12', $permit['location']);
    }

    public function test_an_endorsement_letter_requires_its_addressee_and_date(): void
    {
        $this->signInHomeowner();

        Livewire::test(RequestFileModal::class)
            ->emit('openRequestFileModal')
            ->set('form.type', RequestType::ENDORSEMENT_LETTER)
            ->call('save')
            ->assertHasErrors(['form.addressee' => 'required']);

        Livewire::test(RequestFileModal::class)
            ->emit('openRequestFileModal')
            ->set('form.type', RequestType::ENDORSEMENT_LETTER)
            ->set('form.addressee', 'Barangay Captain')
            ->set('form.letter_date', '')
            ->call('save')
            ->assertHasErrors(['form.letter_date' => 'required']);

        $this->assertSame(0, RequestModel::count());

        Livewire::test(RequestFileModal::class)
            ->emit('openRequestFileModal')
            ->set('form.type', RequestType::ENDORSEMENT_LETTER)
            ->set('form.addressee', 'Barangay Captain')
            ->call('save')
            ->assertHasNoErrors();

        $request = RequestModel::first();

        $this->assertSame(RequestType::ENDORSEMENT_LETTER, $request->type->value);
        $this->assertSame('Barangay Captain', $request->addressee);
        $this->assertSame(now()->toDateString(), $request->letter_date->toDateString());
    }

    public function test_an_endorsement_letter_carries_the_households_unit_and_its_date(): void
    {
        $this->signInHomeownerWithHousehold();

        Livewire::test(RequestFileModal::class)
            ->emit('openRequestFileModal')
            ->set('form.type', RequestType::ENDORSEMENT_LETTER)
            ->assertSet('form.letter_date', now()->toDateString())
            ->set('form.addressee', 'Barangay Captain')
            ->call('save')
            ->assertHasNoErrors();

        $params = RequestModel::first()->printParams();

        // The respondent's unit is the household's own — never typed in.
        $this->assertSame('7', $params['respondentBlock']);
        $this->assertSame('12', $params['respondentLot']);
        $this->assertSame(now()->toDateString(), $params['letterDate']);

        // The letter prints both the unit and the date the homeowner chose.
        $request = RequestModel::first();

        $letter = app(PrintDocumentService::class)->build(
            PrintDocument::ENDORSEMENT_LETTER(),
            $request->documentParams() + [
                'respondentId'      => factory(Resident::class)->create(['surname' => 'Reyes'])->id,
                'addresseePosition' => 'Barangay Chairperson',
                'addresseeOffice'   => 'Brgy. Inocencio',
                'problem'           => 'Excessive noise beyond quiet hours',
            ]
        );

        $this->assertSame('7', $letter['respondent_block']);
        $this->assertSame('12', $letter['respondent_lot']);
        $this->assertSame(now()->format('F j, Y'), $letter['date']['long']);
    }

    // =====================================================================
    // Ownership
    // =====================================================================

    public function test_a_portal_filing_is_written_under_the_accounts_own_resident(): void
    {
        $user = $this->signInHomeowner();
        $other = factory(Resident::class)->create(['surname' => 'Other', 'first_name' => 'Person']);

        Livewire::test(RequestFileModal::class)
            ->emit('openRequestFileModal')
            ->set('form.type', RequestType::CERTIFICATION)
            ->set('form.purpose', 'residency')
            ->set('form.resident_id', $other->id)
            ->call('save')
            ->assertHasNoErrors();

        // The resident named on the payload is never the one written: a
        // homeowner files under their own household and nobody else's.
        $this->assertDatabaseHas('requests', ['resident_id' => $user->resident_id]);
        $this->assertDatabaseMissing('requests', ['resident_id' => $other->id]);
    }

    public function test_the_office_files_the_same_type_with_the_same_inputs(): void
    {
        $resident = factory(Resident::class)->create(['surname' => 'Bautista', 'first_name' => 'Jun']);

        $this->signInAdmin();

        // The office's own form takes its rules from the same trait, so the
        // same type asks for the same inputs on both sides.
        Livewire::test(RequestFormModal::class)
            ->emit('openRequestFormModal')
            ->set('form.resident_id', $resident->id)
            ->set('form.type', RequestType::HOA_PERMIT)
            ->call('save')
            ->assertHasErrors([
                'form.permit_type'   => 'required',
                'form.scope_of_work' => 'required',
            ]);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    private function signInHomeowner(): User
    {
        $resident = factory(Resident::class)->create();
        $user = factory(User::class)->create(['resident_id' => $resident->id]);

        $this->actingAs($user, 'homeowner');

        return $user;
    }

    /**
     * A homeowner whose resident record and household hold everything the
     * Membership Form prints.
     */
    private function signInHomeownerWithHousehold(): User
    {
        $address = Address::create([
            'label'       => 'Tradition Homes Phase 2',
            'subdivision' => 'Tradition Homes',
            'barangay'    => 'Inocencio',
            'city'        => 'Trece Martires City',
            'province'    => 'Cavite',
            'region'      => 'CALABARZON',
        ]);

        $property = Property::create([
            'block'      => 7,
            'lot'        => 12,
            'address_id' => $address->id,
        ]);

        $resident = factory(Resident::class)->create([
            'surname'     => 'Dela Cruz',
            'first_name'  => 'Juan',
            'middle_name' => 'Santos',
            'religion'    => 'Roman Catholic',
            'birth_place' => 'Trece Martires City',
            'mobile_no'   => '09171234567',
        ]);

        $household = Household::create([
            'property_id'  => $property->id,
            'type'         => HouseholdType::OWNER,
            'prev_address' => 'Imus, Cavite',
        ]);

        $household->placeHead($resident->id, 'head');

        $user = factory(User::class)->create(['resident_id' => $resident->id]);

        $this->actingAs($user, 'homeowner');

        return $user;
    }

    private function signInAdmin(): void
    {
        $role = \App\Models\Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = \App\Models\Admin::create([
            'name'                => 'Officer',
            'email'               => 'request-filing-' . uniqid() . '@example.test',
            'password'            => \Illuminate\Support\Facades\Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);

        $this->actingAs($admin, 'web');
    }
}
