<?php

namespace Tests\Feature\Public;

use App\Http\Livewire\Public\Contact\ContactForm;
use App\Mail\ContactMessage;
use App\Models\Content;
use App\Models\Faq;
use App\Models\Setting;
use App\Utility\SettingsHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    // ===== Home =====

    public function test_home_page_shows_the_officer_edited_faqs(): void
    {
        Faq::create([
            'question' => 'How do I pay my dues?',
            'answer'   => 'Through the homeowner portal.',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('How do I pay my dues?')
            ->assertSee('Through the homeowner portal.');
    }

    public function test_home_page_maps_the_address_from_general_settings(): void
    {
        $this->setHoaInfo(['address' => 'Block 20, Tradition Homes']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Block+20%2C+Tradition+Homes', false);
    }

    // ===== Services =====

    public function test_services_page_lists_the_documents_steps_and_portal_link(): void
    {
        $this->get('/services')
            ->assertOk()
            ->assertSee('Membership Form')
            ->assertSee('Paano humiling ng dokumento')
            ->assertSee('Mag-sign in sa Homeowner Portal')
            ->assertSee(route('portal.requests'), false);
    }

    // ===== Contact =====

    public function test_contact_page_shows_the_office_details(): void
    {
        $this->setHoaInfo([
            'email'         => 'office@thha.test',
            'working_hours' => 'Monday to Friday, 8:00 AM to 5:00 PM',
        ]);

        $this->get('/contact')
            ->assertOk()
            ->assertSee('office@thha.test')
            ->assertSee('Monday to Friday, 8:00 AM to 5:00 PM');
    }

    public function test_contact_form_emails_the_association_office(): void
    {
        Mail::fake();

        $this->setHoaInfo(['email' => 'office@thha.test']);

        Livewire::test(ContactForm::class)
            ->set('form.name', 'Juan dela Cruz')
            ->set('form.email', 'juan@example.com')
            ->set('form.contact_number', '0917 123 4567')
            ->set('form.subject', 'Dues inquiry')
            ->set('form.message', 'How much are my monthly dues?')
            ->call('send')
            ->assertSet('sent', true);

        Mail::assertSent(ContactMessage::class, function (ContactMessage $mail) {
            return $mail->hasTo('office@thha.test')
                && $mail->senderContact === '0917 123 4567';
        });
    }

    // ===== Content pages =====

    public function test_a_content_page_is_served_from_the_contents_table(): void
    {
        Content::create([
            'title'   => 'Mission and Vision',
            'slug'    => 'mission-and-vision',
            'group'   => 'About Us',
            'details' => '<p>Our mission.</p><script>alert(1)</script>',
        ]);

        $this->get('/content/mission-and-vision')
            ->assertOk()
            ->assertSee('Our mission.')
            ->assertDontSee('<script>alert(1)</script>', false);

        $this->get('/')->assertOk()->assertSee('About Us');

        $this->get('/content/missing')->assertNotFound();
    }

    public function test_every_content_group_is_its_own_nav_dropdown(): void
    {
        Content::create([
            'title'   => 'Our Story',
            'slug'    => 'our-story',
            'details' => '<p>Ours.</p>',
        ]);

        Content::create([
            'title'   => 'By-Laws',
            'slug'    => 'by-laws',
            'group'   => 'Governance',
            'details' => '<p>Rules.</p>',
        ]);

        // The ungrouped page stays under About Us; the group names its own menu.
        $this->get('/')
            ->assertOk()
            ->assertSee('About Us')
            ->assertSee('Our Story')
            ->assertSee('Governance')
            ->assertSee('By-Laws')
            ->assertDontSee('dropdown-header', false);
    }

    // ===== Helpers =====

    private function setHoaInfo(array $values): void
    {
        foreach ($values as $key => $value) {
            Setting::create(['key' => $key, 'value' => $value, 'group' => 'hoa_info']);
        }

        SettingsHelper::flush('hoa_info');
        SettingsHelper::flush('security');
        SettingsHelper::flush('auth');
    }
}
