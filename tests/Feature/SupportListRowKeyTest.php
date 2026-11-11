<?php

namespace Tests\Feature;

use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use App\Enums\RequestStatus;
use App\Enums\RequestType;
use App\Enums\ViolationStatus;
use App\Enums\ViolationType;
use App\Models\Admin;
use App\Models\Complaint;
use App\Models\Request as RequestModel;
use App\Models\Resident;
use App\Models\Role;
use App\Models\Violation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * A Support list's desktop row is keyed by the record it shows, and the key is
 * the row's own — never the id of a control inside it.
 *
 * A key that doubles as an id inside the row is what kept a deleted row on
 * screen: Livewire's DOM diff reads `wire:key` where it is there and falls back
 * to the node's DOM `id` everywhere else, so a row that answers to the same
 * string as its own checkbox never leaves the table — the server's HTML comes
 * back without the row and the browser goes on showing it until a reload. An
 * edit looks fine either way, because an edit rewrites a row's contents rather
 * than removing the row, which is why the two symptoms do not match.
 *
 * The tests here assert the rule on each Support list, not the symptom, because
 * the symptom is a browser's DOM diff: the server side was always correct.
 */
class SupportListRowKeyTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_requests_list_keys_a_row_apart_from_the_ids_inside_it(): void
    {
        $this->signInAdmin();

        $request = $this->request();

        $this->assertRowsAreKeyedApartFromTheIdsInsideThem(
            $this->get(route('support.requests'))->assertOk()->getContent(),
            'request-row-' . $request->id
        );
    }

    public function test_the_violations_list_keys_a_row_apart_from_the_ids_inside_it(): void
    {
        $this->signInAdmin();

        $violation = $this->violation();

        $this->assertRowsAreKeyedApartFromTheIdsInsideThem(
            $this->get(route('support.violations'))->assertOk()->getContent(),
            'violation-row-' . $violation->id
        );
    }

    public function test_the_complaints_list_keys_a_row_apart_from_the_ids_inside_it(): void
    {
        $this->signInAdmin();

        $complaint = $this->complaint();

        $this->assertRowsAreKeyedApartFromTheIdsInsideThem(
            $this->get(route('support.complaints'))->assertOk()->getContent(),
            'complaint-row-' . $complaint->id
        );
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    /**
     * No keyed row on the page carries a key that is also the id of something
     * inside it — and `$expectedRowKey` is the key the record we filed sits
     * under, so the check above never passes on an empty table.
     */
    private function assertRowsAreKeyedApartFromTheIdsInsideThem(string $html, string $expectedRowKey): void
    {
        preg_match_all('/<tr\b[^>]*wire:key="([^"]+)"[^>]*>(.*?)<\/tr>/s', $html, $rows, PREG_SET_ORDER);

        foreach ($rows as $row) {
            preg_match_all('/id="([^"]+)"/', $row[2], $ids);

            $this->assertNotContains(
                $row[1],
                $ids[1],
                "The row keyed \"{$row[1]}\" is also the id of a control inside it, so the DOM diff cannot drop the row once the record is deleted."
            );
        }

        $this->assertStringContainsString(
            'wire:key="' . $expectedRowKey . '"',
            $html,
            "The record we filed is not on the page under the row key \"{$expectedRowKey}\", so the rows above were not the ones checked."
        );
    }

    private function signInAdmin(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'row-key-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);

        $this->actingAs($admin, 'web');
    }

    private function resident(): Resident
    {
        return factory(Resident::class)->create(['surname' => 'Reyes', 'first_name' => 'Ana']);
    }

    private function request(): RequestModel
    {
        return RequestModel::create([
            'resident_id' => $this->resident()->id,
            'type'        => RequestType::CERTIFICATION,
            'purpose'     => 'residency',
            'status'      => RequestStatus::PENDING,
        ]);
    }

    private function violation(): Violation
    {
        $resident = $this->resident();

        return Violation::create([
            'resident_id'    => $resident->id,
            'resident_name'  => $resident->full_name,
            'violation_type' => ViolationType::EXCESSIVE_NOISE,
            'occurred_on'    => now()->toDateString(),
            'location'       => 'Block 1 Lot 1',
            'description'    => 'Loud music past curfew.',
            'status'         => ViolationStatus::ACTIVE,
        ]);
    }

    private function complaint(): Complaint
    {
        $resident = $this->resident();

        return Complaint::create([
            'resident_id'    => $resident->id,
            'submitter_name' => $resident->full_name,
            'category'       => ComplaintCategory::NOISE,
            'subject'        => 'Loud karaoke next door',
            'description'    => 'It runs past midnight on weekends.',
            'status'         => ComplaintStatus::PENDING,
        ]);
    }
}
