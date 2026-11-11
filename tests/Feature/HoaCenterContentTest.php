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
            ->assertSee('Home Page Questions');

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

    public function test_an_officer_publishes_a_content_page_with_cleaned_details(): void
    {
        $this->signInAdministrator();

        $this->get(route('hoa-center.contents'))
            ->assertOk()
            ->assertSee('Public Pages');

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
            'password_changed_at' => now(),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);

        $this->actingAs($admin, 'web');
    }
}
