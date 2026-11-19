<?php

namespace Tests\Feature;

use App\Http\Livewire\Admin\HoaCenter\Contents\Modals\ContentFormModal;
use App\Http\Livewire\Admin\HoaCenter\Faqs\Modals\FaqFormModal;
use App\Models\Admin;
use App\Models\Content;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class HoaCenterContentTest extends TestCase
{
    use RefreshDatabase;

    // ===== FAQs =====

    public function test_an_officer_adds_a_faq_the_home_page_then_reads(): void
    {
        $this->signInAdministrator();

        $this->get(route('hoa-center.faqs'))
            ->assertOk()
            ->assertSee('Frequently Asked Questions');

        Livewire::test(FaqFormModal::class)
            ->call('openCreate')
            ->set('form.question', 'When are dues due?')
            ->set('form.answer', 'On the fifth of every month.')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('faqs', ['question' => 'When are dues due?']);

        $this->get('/')
            ->assertOk()
            ->assertSee('When are dues due?');
    }

    // ===== Content pages =====

    public function test_an_officer_adds_a_content_page_with_cleaned_details(): void
    {
        $this->signInAdministrator();

        $this->get(route('hoa-center.contents'))
            ->assertOk()
            ->assertSee('Contents');

        Livewire::test(ContentFormModal::class)
            ->call('openCreate')
            ->set('form.title', 'Mission and Vision')
            ->set('form.group', 'About Us')
            ->set('form.details', '<p>Our mission.</p><script>alert(1)</script>')
            ->call('save')
            ->assertHasNoErrors();

        $content = Content::firstOrFail();

        $this->assertSame('mission-and-vision', $content->slug);
        $this->assertStringNotContainsString('<script>', $content->details);

        $this->get(route('content.show', $content->slug))
            ->assertOk()
            ->assertSee('Our mission.');
    }

    public function test_content_pages_appear_under_about_us_and_are_readable(): void
    {
        $this->signInAdministrator();

        Livewire::test(ContentFormModal::class)
            ->call('openCreate')
            ->set('form.title', 'Environment')
            ->set('form.group', 'Rules and Regulations')
            ->set('form.details', '<p>Keep the subdivision clean.</p>')
            ->call('save')
            ->assertHasNoErrors();

        Livewire::test(ContentFormModal::class)
            ->call('openCreate')
            ->set('form.title', 'Waste Segregation')
            ->set('form.group', 'Environment')
            ->set('form.details', '<p>Sort your waste.</p>')
            ->call('save')
            ->assertHasNoErrors();

        Livewire::test(ContentFormModal::class)
            ->call('openCreate')
            ->set('form.title', 'Mission and Vision')
            ->set('form.details', '<p>Our mission.</p>')
            ->call('save')
            ->assertHasNoErrors();

        $this->get('/')
            ->assertOk()
            ->assertSee('About Us')
            ->assertSee('Rules and Regulations')
            ->assertSee('Environment')
            ->assertSee('Waste Segregation')
            ->assertSee('Mission and Vision')
            ->assertSee('dropdown-submenu', false);

        $this->get(route('content.show', 'environment'))
            ->assertOk()
            ->assertSee('Keep the subdivision clean.');

        $this->get(route('hoa-center.contents'))
            ->assertOk()
            ->assertDontSee('Draft');
    }

    public function test_a_page_can_be_created_with_a_new_group(): void
    {
        $this->signInAdministrator();

        Livewire::test(ContentFormModal::class)
            ->call('openCreate')
            ->set('form.title', 'Rules and Regulations')
            ->set('form.group', ContentFormModal::NEW_GROUP)
            ->set('form.new_group_name', 'Policies')
            ->set('form.details', '<p>Read the rules.</p>')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('contents', [
            'title' => 'Rules and Regulations',
            'group' => 'Policies',
        ]);
    }

    public function test_the_new_group_input_is_disabled_unless_a_new_group_is_selected(): void
    {
        $this->signInAdministrator();

        $component = Livewire::test(ContentFormModal::class)->call('openCreate');

        $this->assertSame(1, preg_match('/name="form\.new_group_name"[^>]*disabled/', $component->lastRenderedDom));

        $component->set('form.group', ContentFormModal::NEW_GROUP);

        $this->assertSame(0, preg_match('/name="form\.new_group_name"[^>]*disabled/', $component->lastRenderedDom));
    }

    // ===== Helpers =====

    private function signInAdministrator(): void
    {
        $role = Role::create([
            'name'        => 'Administrator',
            'description' => 'Administrator',
            'permissions' => [],
        ]);

        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'hoa-center-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);

        $this->actingAs($admin, 'web');
    }
}
