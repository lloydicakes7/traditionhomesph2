<?php

namespace Tests\Feature\Documents;

use App\Queries\Documents\BusinessPermitDocumentQuery;
use App\Queries\Documents\CertificationDocumentQuery;
use App\Queries\Settings\PrintSettingsQuery;
use App\Queries\Documents\EndorsementLetterDocumentQuery;
use App\Queries\Documents\HoaPermitDocumentQuery;
use App\Queries\Documents\InvitationLetterDocumentQuery;
use App\Queries\Documents\MembershipFormDocumentQuery;
use App\Queries\Documents\SinumpaangSalaysayDocumentQuery;
use App\Utility\DocumentText;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The association's printed documents, rendered blank, full and overflowing.
 *
 * The paper decides the structure: the same sheet printed blank and printed
 * with a household must carry the same number of blanks, rows, boxes and ruled
 * lines, and a missing optional value must not remove a field or shift what
 * follows it.
 */
class DocumentRenderingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string, 1: class-string}>
     */
    public function documents(): array
    {
        return [
            'membership form' => ['membership-form', MembershipFormDocumentQuery::class],
            'hoa permit' => ['hoa-permit', HoaPermitDocumentQuery::class],
            'endorsement letter' => ['endorsement-letter', EndorsementLetterDocumentQuery::class],
            'invitation letter' => ['invitation-letter', InvitationLetterDocumentQuery::class],
            'certification' => ['certification', CertificationDocumentQuery::class],
            'sinumpaang salaysay' => ['sinumpaang-salaysay', SinumpaangSalaysayDocumentQuery::class],
            'business permit' => ['business-permit', BusinessPermitDocumentQuery::class],
        ];
    }

    /**
     * @dataProvider documents
     */
    public function test_a_document_renders_blank_full_and_overflowing(string $view, string $query, ): void
    {
        foreach ($this->datasets($view, $query) as $kind => $data) {
            $html = $this->renderHtml($view, $data);

            $this->assertStringNotContainsString('N/A', $html, "{$view} {$kind} prints N/A");
            $this->assertStringNotContainsString('null', $html, "{$view} {$kind} prints null");
            $this->assertStringNotContainsString('Undefined', $html, "{$view} {$kind} prints Undefined");
            $this->assertStringNotContainsString('Array', $html, "{$view} {$kind} prints Array");
            $this->assertSame(0, preg_match('/_{3,}/', $html), "{$view} {$kind} prints an underscore run");
        }
    }

    /**
     * @dataProvider documents
     */
    public function test_blank_full_and_overflow_share_the_same_structure(string $view, string $query, ): void
    {
        $counts = [];

        foreach ($this->datasets($view, $query) as $kind => $data) {
            $counts[$kind] = $this->structure($this->renderHtml($view, $data));
        }

        $this->assertSame($counts['blank'], $counts['full'], "{$view}: a full sheet differs from a blank one");
        $this->assertSame($counts['full'], $counts['overflow'], "{$view}: an overflowing value changes the sheet");
    }

    /**
     * @dataProvider documents
     */
    public function test_removing_an_optional_value_keeps_the_structure(string $view, string $query, ): void
    {
        $datasets = $this->datasets($view, $query);
        $blank = $datasets['blank'];
        $expected = $this->structure($this->renderHtml($view, $datasets['full']));

        foreach (array_keys($datasets['full']) as $key) {
            unset($datasets['full'][$key]);

            $data = array_merge($blank, $datasets['full']);

            $this->assertSame(
                $expected,
                $this->structure($this->renderHtml($view, $data)),
                "{$view}: removing '{$key}' changed the sheet"
            );

            $datasets['full'] = array_merge($blank, $this->fullValues($view));
        }
    }

    /**
     * @dataProvider documents
     */
    public function test_a_document_is_one_page_and_a_real_pdf(string $view, string $query, ): void
    {
        $data = $this->datasets($view, $query)['full'];

        $builder = Pdf::loadView('pdf.' . $view, $this->viewData($data));
        $output = $builder->output();

        $this->assertStringStartsWith('%PDF', $output);
        $this->assertSame(1, $builder->getDomPDF()->getCanvas()->get_page_count(), "{$view} spills onto a second page");
    }

    /**
     * @dataProvider documents
     */
    public function test_no_blank_is_drawn_twice_or_stretched_across_a_row(string $view, string $query, ): void
    {
        $html = $this->renderHtml($view, $this->datasets($view, $query)['full']);

        preg_match_all('/<span class="doc-line[^>]*>(.*?)<\/span>/s', $html, $matches);

        $this->assertNotEmpty($matches[1]);

        foreach ($matches[1] as $content) {
            $this->assertStringNotContainsString('<', $content, "{$view}: a blank contains another element");
        }

        preg_match_all('/<span class="doc-line[^>]*>/i', $html, $openings);

        foreach ($openings[0] as $tag) {
            $this->assertStringNotContainsString('100%', $tag, "{$view}: an inline blank is stretched to the whole row");
        }
        $this->assertStringNotContainsString('<u>', $html);
        $this->assertStringNotContainsString('text-decoration: underline; border-bottom', $html);
    }

    // =====================================================================
    // Datasets
    // =====================================================================

    /**
     * @return array<string, array<string, mixed>>
     */
    private function datasets(string $view, string $query): array
    {
        $blank = app($query)->blank();

        return [
            'blank' => $blank,
            'full' => array_merge($blank, $this->fullValues($view)),
            'overflow' => array_merge($blank, $this->fullValues($view), $this->overflowValues($view)),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fullValues(string $view): array
    {
        switch ($view) {
            case 'membership-form':
                return [
                    'date' => DocumentText::dateParts('2026-09-25'),
                    'surname' => 'Dela Cruz',
                    'first_name' => 'Juan',
                    'middle_name' => 'Santos',
                    'religion' => 'Roman Catholic',
                    'sex' => 'Male',
                    'citizenship' => 'Filipino',
                    'age' => '42',
                    'birth_date' => DocumentText::dateParts('1984-06-12'),
                    'birth_place' => 'Trece Martires City',
                    'civil_status' => 'Married',
                    'spouse_name' => 'Maria Dela Cruz',
                    'member_type' => 'Owner',
                    'education' => 'College Graduate',
                    'occupation' => 'Teacher',
                    'mobile' => '09171234567',
                    'previous_address' => 'Bacoor, Cavite',
                    'owner_name' => 'Dela Cruz, Juan S.',
                    'block' => '4',
                    'lot' => '12',
                    'fee' => '100.00',
                    'dues' => '50.00',
                    'members' => [[
                        'name' => 'Dela Cruz, Maria',
                        'age' => '39',
                        'birth_date' => 'March 2, 1987',
                        'birth_place' => 'Trece Martires City',
                        'relation' => 'Spouse',
                        'occupation' => 'Nurse',
                    ]],
                ];

            case 'hoa-permit':
                return [
                    'granted_to' => 'JUAN S. DELA CRUZ',
                    'block' => '4',
                    'lot' => '12',
                    'address_line_1' => 'Block 4 Lot 12, Phase 2, Tradition Homes, Barangay Inocencio,',
                    'address_line_2' => 'Trece Martires City, Cavite',
                    'purpose' => 'Roof repair',
                    'purpose_line_1' => 'Roof repair',
                    'purpose_line_2' => '',
                    'location' => 'Block 4 Lot 12',
                    'event_period' => 'October 5 to October 19, 2026',
                    'issued_date' => DocumentText::dateParts('2026-10-01'),
                    'control_no' => 'CN-2026-0001',
                    'or_no' => 'OR-2026-0001',
                ];

            case 'certification':
                $purposes = app(CertificationDocumentQuery::class)->blank()['purposes'];
                $purposes[0]['checked'] = true;

                return [
                    'block' => '4',
                    'lot' => '12',
                    'occupant_name' => 'Dela Cruz, Juan S.',
                    'salutation' => 'MR.',
                    'purposes' => $purposes,
                    'issued_date' => DocumentText::dateParts('2026-10-01'),
                    'control_no' => 'CN-2026-0001',
                    'or_no' => 'OR-2026-0001',
                ];

            case 'sinumpaang-salaysay':
                return [
                    'case_no' => 'THHA-2026-014',
                    'case_title' => 'Noise complaint',
                    'affiant_name' => 'Dela Cruz, Juan S.',
                    'affiant_age' => '42',
                    'affiant_civil_status' => 'may-asawa',
                    'affiant_address' => 'Block 4 Lot 12, Phase 2, Tradition Homes, Barangay Inocencio',
                    'respondent_name' => 'Lim, Anna Marie',
                    'respondent_age' => '35',
                    'respondent_address' => 'Block 6 Lot 3, Phase 2, Tradition Homes, Barangay Inocencio',
                    'incident_date' => DocumentText::dateParts('2026-09-25'),
                    'incident_month' => 'Setyembre',
                    'incident_time' => '9:30',
                    'incident_day_part' => 'gabi',
                    'narrative' => [
                        'Nagpatugtog ng videoke ang kapitbahay nang lampas sa oras ng katahimikan at ayaw itong pahinaan.',
                    ],
                    'execution_date' => DocumentText::dateParts('2026-09-30'),
                    'execution_month' => 'Setyembre',
                    'execution_phrase' => '30 ng Setyembre 2026',
                ];

            case 'business-permit':
                $purposes = app(BusinessPermitDocumentQuery::class)->blank()['purposes'];
                $purposes[0]['checked'] = true;
                $purposes[5]['value'] = 'Car wash';

                return [
                    'applicant_name' => 'Dela Cruz, Juan S.',
                    'gender' => 'Male',
                    'block' => '4',
                    'lot' => '12',
                    'birth_date' => DocumentText::dateParts('1984-06-12'),
                    'age' => '42',
                    'mobile_no' => '09171234567',
                    'purposes' => $purposes,
                    'issued_date' => DocumentText::dateParts('2026-10-01'),
                    'control_no' => 'CN-2026-0001',
                ];

            case 'endorsement-letter':
                return [
                    'complainant_name' => 'Dela Cruz, Juan S.',
                    'complainant_block' => '4',
                    'complainant_lot' => '12',
                    'respondent_name' => 'Lim, Anna Marie',
                    'respondent_block' => '6',
                    'respondent_lot' => '3',
                    'concern' => 'videoke noise beyond quiet hours',
                    'concern_line_1' => 'videoke noise beyond quiet hours',
                    'concern_line_2' => '',
                    'date' => DocumentText::dateParts('2026-09-25'),
                ];

            default:
                return [
                    'recipients' => [[
                        'name' => 'Dela Cruz, Juan S.',
                        'block' => '4',
                        'lot' => '12',
                        'address' => 'Block 4 Lot 12, Phase 2, Tradition Homes',
                        'address_1' => 'Block 4 Lot 12, Phase 2, Tradition Homes,',
                        'address_2' => 'Barangay Inocencio, Trece Martires City',
                    ]],
                    'addressee_name' => 'Dela Cruz, Juan S.',
                    'block' => '4',
                    'lot' => '12',
                    'date' => DocumentText::dateParts('2026-09-30'),
                    'meeting_date' => DocumentText::dateParts('2026-10-05'),
                    'meeting_time' => '3:00 PM',
                    'day_part' => 'afternoon',
                ];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function overflowValues(string $view): array
    {
        $longName = str_repeat('Maximiliano ', 6);

        switch ($view) {
            case 'membership-form':
                return ['surname' => $longName, 'first_name' => $longName];

            case 'hoa-permit':
                return [
                    'granted_to' => $longName,
                    'purpose' => str_repeat('Repainting of the whole exterior and replacement of the gutters ', 4),
                ];

            case 'certification':
                return ['occupant_name' => $longName, 'purposes' => $this->longPurposes(CertificationDocumentQuery::class)];

            case 'sinumpaang-salaysay':
                return ['narrative' => array_fill(0, 14, str_repeat('Nagpatugtog ng videoke ang kapitbahay hanggang madaling araw. ', 3))];

            case 'business-permit':
                return [
                    'applicant_name' => $longName,
                    'mobile_no' => '09171234567 / 09998887777 / 09170000000',
                    'purposes' => $this->longPurposes(BusinessPermitDocumentQuery::class),
                ];

            case 'endorsement-letter':
                return [
                    'complainant_name' => $longName,
                    'concern' => str_repeat('The neighbor plays a videoke machine every night beyond the agreed quiet hours and refuses to lower it. ', 5),
                ];

            default:
                return ['addressee_name' => $longName];
        }
    }

    // =====================================================================
    // Rendering
    // =====================================================================

    /**
     * A purpose list with its written-out option filled in, as the longest
     * value a tick list can be asked to hold.
     *
     * @param  class-string  $query
     * @return array<int, array<string, mixed>>
     */
    private function longPurposes(string $query): array
    {
        $purposes = app($query)->blank()['purposes'];
        $last = count($purposes) - 1;

        $purposes[$last]['value'] = str_repeat('Car wash and vulcanizing shop ', 3);

        return $purposes;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function viewData(array $data): array
    {
        $data['letterhead'] = app(PrintSettingsQuery::class)->letterhead();
        $data['staff'] = $this->staff();

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function renderHtml(string $view, array $data): string
    {
        return view('pdf.' . $view, $this->viewData($data))->render();
    }

    /**
     * @return array<string, mixed>
     */
    private function staff(): array
    {
        return [
            'president' => ['name' => 'RIO B. GENODIA', 'role' => 'President'],
            'vice' => ['name' => 'PEMA U. GALLO', 'role' => 'Vice-President'],
            'secretary' => ['name' => 'LEAH G. ABATAYO', 'role' => 'Secretary'],
            'treasurer' => ['name' => 'WILHELMINA M. CARVAJAL', 'role' => 'Treasurer'],
            'auditor' => ['name' => 'REGINA S. SANTILLANA', 'role' => 'Auditor'],
            'committees' => [['name' => 'REGINALDA A. PAYOT', 'role' => 'Livelihood Committee']],
            'photo' => null,
            'signer' => ['name' => 'RIO B. GENODIA', 'role' => 'THHA-President'],
            'grievance' => ['name' => 'ROWENA L. ECHAVEZ', 'role' => 'Grievance and Adjudication Committee'],
            'checkers' => [
                ['name' => 'DIVINA T. LARONG', 'role' => 'Chairwoman'],
                ['name' => 'LILIA R. BALE', 'role' => 'Chairwoman'],
            ],
            'recipient' => [
                'name' => 'HON. ROSENDO DILIDILI',
                'position' => 'Barangay Chairperson',
                'office' => 'Brgy. Inocencio',
                'city' => 'Trece Martires City, Cavite',
            ],
        ];
    }

    /**
     * @return array<string, int>
     */
    private function structure(string $html): array
    {
        return [
            'lines' => substr_count($html, 'class="doc-line'),
            'rows' => substr_count($html, 'class="doc-row"'),
            'boxes' => substr_count($html, 'class="doc-box'),
            'ruled' => substr_count($html, 'doc-ruled'),
        ];
    }
}
