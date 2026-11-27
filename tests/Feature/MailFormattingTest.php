<?php

namespace Tests\Feature;

use App\Mail\ContactMessage;
use App\Models\Admin;
use App\Models\Role;
use App\Models\Setting;
use App\Models\SoaEmail;
use App\Notifications\PendingProfileVerificationsDigest;
use App\Notifications\StatementOfAccountNotification;
use App\Utility\SettingsHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Markdown;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MailFormattingTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_logo_url_serves_the_logo_saved_in_general_settings(): void
    {
        $logoUrl = $this->useLogo();

        $response = $this->get($logoUrl);

        $response->assertOk();
        $response->assertHeader('content-type', 'image/jpeg');
        $this->assertGreaterThan(0, $response->baseResponse->getFile()->getSize());
    }

    public function test_the_logo_url_serves_the_bundled_mark_when_no_logo_is_saved(): void
    {
        $this->setHoaInfo(['logo_path' => '']);

        $response = $this->get(route('branding.logo'));

        $response->assertOk();
        $response->assertHeader('content-type', 'image/jpeg');
    }

    protected function tearDown(): void
    {
        Storage::disk('public')->delete('hoa/logo.jpg');

        parent::tearDown();
    }

    public function test_notification_mails_follow_the_association_mail_layout(): void
    {
        $admin = $this->officer();

        $mail = (new PendingProfileVerificationsDigest(3))->toMail($admin);

        $this->assertSame('emails.notification', $mail->markdown);
        $this->assertNull($mail->view);
        $this->assertSame('Greetings, ' . $admin->name . ',', $mail->greeting);
        $this->assertStringNotContainsString('—', $mail->subject);

        $html = $mail->render();

        $this->assertStringNotContainsString('image/logo.jpg', $html);
        $this->assertStringContainsString('Greetings, ' . $admin->name . ',', $html);
        $this->assertStringContainsString('3 homeowner accounts', $html);
        $this->assertStringNotContainsString('Hello!', $html);

        $text = app(Markdown::class)->renderText('emails.notification', $mail->data());

        $this->assertStringContainsString('Greetings, ' . $admin->name . ',', $text);
        $this->assertStringNotContainsString('#', $text);
    }

    public function test_the_statement_mail_is_branded_and_reads_formally(): void
    {
        $admin = $this->officer();

        $summary = [
            'owner'           => 'Juan dela Cruz',
            'property'        => 'Block 4 Lot 12',
            'address'         => 'Unit 12, Tradition Homes',
            'period'          => 'January 2026',
            'groups'          => [
                [
                    'key'           => 'dues',
                    'label'         => 'Monthly Dues',
                    'bills'         => collect([(object) [
                        'reference'           => 'BILL-2026-0001',
                        'type_label'          => 'Monthly Dues',
                        'billing_period'      => null,
                        'bill_date_display'   => 'January 5, 2026',
                        'total_display'       => 'PHP 500.00',
                        'balance_display'     => 'PHP 500.00',
                    ]]),
                    'total'         => 500.0,
                    'total_display' => 'PHP 500.00',
                ],
                [
                    'key'           => 'invoices',
                    'label'         => 'Invoices',
                    'bills'         => collect(),
                    'total'         => 0.0,
                    'total_display' => 'PHP 0.00',
                ],
            ],
            'balance_display' => 'PHP 500.00',
            'instructions'    => '<p>Kindly settle the amount at the association office.</p>',
        ];

        $mail = (new StatementOfAccountNotification(new SoaEmail(), $summary))->toMail($admin);

        $this->assertSame('emails.statement-of-account', $mail->markdown);
        $this->assertSame('Statement of Account for January 2026', $mail->subject);

        $html = $mail->render();

        $this->assertStringContainsString('Greetings, Juan dela Cruz,', $html);
        $this->assertStringNotContainsString('image/logo.jpg', $html);
        $this->assertStringContainsString('<div class="table"', $html);
        $this->assertStringContainsString('BILL-2026-0001', $html);
        $this->assertStringContainsString('Kindly settle the amount at the association office.', $html);
        $this->assertRegExp('/<td class="panel-content"[^>]*style="[^"]*padding: 16px/', $html);
        $this->assertStringNotContainsString('Hello!', $html);

        $text = app(Markdown::class)->renderText('emails.statement-of-account', $mail->data());

        $this->assertStringContainsString('BILL-2026-0001', $text);
        $this->assertStringContainsString('Monthly Dues total', $text);
        $this->assertStringContainsString('Total outstanding: PHP 500.00', $text);
        $this->assertStringNotContainsString('#', $text);
        $this->assertStringNotContainsString('|', $text);
    }

    public function test_the_website_inquiry_is_branded_and_replies_to_the_resident(): void
    {
        $mail = new ContactMessage([
            'name'           => 'Juan dela Cruz',
            'email'          => 'juan@example.com',
            'contact_number' => '',
            'subject'        => 'Dues inquiry',
            'message'        => 'How much are my monthly dues?',
        ]);

        $html = $mail->render();

        $this->assertTrue($mail->hasReplyTo('juan@example.com'));
        $this->assertStringContainsString('<div class="table"', $html);
        $this->assertStringNotContainsString('image/logo.jpg', $html);
        $this->assertStringContainsString('How much are my monthly dues?', $html);
        $this->assertStringContainsString('Not provided', $html);
        $this->assertRegExp('/<td class="panel-content"[^>]*style="[^"]*padding: 16px/', $html);
        $this->assertStringNotContainsString('Hello!', $html);
    }

    private function officer(): Admin
    {
        $role = Role::create([
            'name'        => 'Administrator',
            'description' => 'Administrator',
            'permissions' => [],
        ]);

        return Admin::create([
            'name'                => 'Officer',
            'email'               => 'mail-format-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);
    }

    private function useLogo(): string
    {
        Storage::disk('public')->put('hoa/logo.jpg', file_get_contents(public_path('image/logo.jpg')));

        $this->setHoaInfo([
            'logo_path' => 'hoa/logo.jpg',
            'hoa_name'  => 'Tradition Homes Phase 2 Homeowners Association, Inc.',
        ]);

        return route('branding.logo');
    }

    private function setHoaInfo(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::create(['key' => $key, 'value' => $value, 'group' => 'hoa_info']);
        }

        SettingsHelper::flush('hoa_info');
    }
}
