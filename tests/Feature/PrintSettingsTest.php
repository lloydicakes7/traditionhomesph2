<?php

namespace Tests\Feature;

use App\Actions\Settings\Print\UpdatePrintSettings;
use App\Models\Admin;
use App\Models\Role;
use App\Queries\Settings\PrintSettingsQuery;
use App\Utility\HtmlHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * The print settings — what the letterhead is made of, and how it is cleaned.
 *
 * The header and footer are rich text written in Summernote, so what the office
 * types is not what may be stored: both are held to `HtmlHelper`, the same rule
 * the Collections pages' bill instructions are held to. These tests pin that
 * rule for the print group specifically, because the sanitizer used to live on
 * this service and now lives in one shared utility.
 */
class PrintSettingsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * An officer with the panel's full access — the settings pages are
     * Administrator-only.
     */
    private function signIn(): Admin
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'print-settings-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'password_changed_at' => now(),
            'is_active'           => true,
            'role_id'             => $role->id,
        ]);

        $this->actingAs($admin, 'web');

        return $admin;
    }

    /**
     * Write a print group the way the settings page does.
     */
    private function savePrint(array $data): void
    {
        app(UpdatePrintSettings::class)->handle($data, $this->signIn());
    }

    // =====================================================================
    // What may be stored
    // =====================================================================

    public function test_the_letterhead_keeps_only_the_formatting_a_document_may_carry(): void
    {
        $this->savePrint([
            'header_html' => '<p style="text-align: center; font-family: Papyrus">Tradition Homes</p>'
                . '<script>alert(1)</script><iframe src="https://example.test"></iframe>'
                . '<section>Subdivision</section>',
            'footer_text' => '<p onclick="steal()">Office hours: 8am–5pm</p>',
        ]);

        $settings = app(PrintSettingsQuery::class)->load();

        // Kept: the allowed tag, the allowed style declaration, and the text a
        // harmless container was wrapping.
        $this->assertStringContainsString('<p style="text-align: center">Tradition Homes</p>', $settings['header_html']);
        $this->assertStringContainsString('Subdivision', $settings['header_html']);

        // Dropped: the script and the frame with their contents, the style
        // property that is never allowed, and the inline handler.
        $this->assertStringNotContainsString('<script', $settings['header_html']);
        $this->assertStringNotContainsString('<iframe', $settings['header_html']);
        $this->assertStringNotContainsString('font-family', $settings['header_html']);
        $this->assertStringNotContainsString('<section', $settings['header_html']);
        $this->assertStringNotContainsString('onclick', $settings['footer_text']);
        $this->assertStringContainsString('Office hours: 8am–5pm', $settings['footer_text']);
    }

    public function test_a_style_the_editor_could_not_have_kept_cannot_smuggle_a_url(): void
    {
        $this->savePrint([
            'header_html' => '<span style="background-image: url(javascript:alert(1))">Hi</span>',
        ]);

        $this->assertStringNotContainsString(
            'url(',
            app(PrintSettingsQuery::class)->load()['header_html']
        );
    }

    /**
     * The images a letterhead prints come from the two upload slots and the
     * footer slot — never from the editor, so every image tag typed into the
     * header goes.
     */
    public function test_an_image_tag_typed_into_the_header_is_dropped(): void
    {
        $this->savePrint([
            'header_html' => '<p>Logo: <img src="/image/logo.png" onerror="steal()"> here</p>',
        ]);

        $header = app(PrintSettingsQuery::class)->load()['header_html'];

        $this->assertStringNotContainsString('<img', $header);
        $this->assertStringContainsString('here', $header);
    }

    public function test_the_flags_the_page_sends_are_stored_as_flags(): void
    {
        $this->savePrint([
            'divider'     => 'on',
            'footer_show' => '',
            'paper'       => 'legal',
        ]);

        $settings = app(PrintSettingsQuery::class)->load();

        $this->assertTrue($settings['divider']);
        $this->assertFalse($settings['footer_show']);
        $this->assertSame('legal', $settings['paper']);
    }

    // =====================================================================
    // What the page serves
    // =====================================================================

    /**
     * Both rich-text fields, and the one shared script that builds them: the
     * print page has no editor script of its own.
     */
    public function test_the_print_page_serves_both_rich_text_fields_and_the_shared_editor(): void
    {
        $this->signIn();

        $response = $this->get(route('settings.print'));

        $response->assertOk();
        $response->assertSee('data-rich-text="printHeaderHtml"', false);
        $response->assertSee('data-rich-text="printFooterText"', false);
        $response->assertSee("getAttribute('data-rich-text')", false);
        $response->assertSee('vendor/summernote/summernote-bs4.min.js', false);
    }

    /**
     * What the office saved last is what the editor opens on.
     */
    public function test_the_editor_is_seeded_with_the_stored_letterhead(): void
    {
        $this->signIn();

        $this->savePrint(['header_html' => '<h1>Tradition Homes</h1>']);

        $this->get(route('settings.print'))
            ->assertOk()
            ->assertSee('<h1>Tradition Homes</h1>', false);
    }

    /**
     * The editor's toolbar can only offer formats the stored document keeps:
     * `style`'s block formats are the tags the sanitizer allows, and the palette
     * is a fixed set of design tokens.
     */
    public function test_the_editor_and_the_sanitizer_agree_on_the_block_formats(): void
    {
        $script = file_get_contents(
            resource_path('views/livewire/admin/settings/partials/summernote.blade.php')
        );

        // The four block formats the toolbar offers are the tags a setting may
        // keep — nothing the officer can select is stripped on the way in.
        $this->assertStringContainsString("styleTags = ['p', 'h1', 'h2', 'h3']", $script);

        foreach (['p', 'h1', 'h2', 'h3'] as $tag) {
            $this->assertContains($tag, HtmlHelper::ALLOWED_TAGS);
        }
    }
}
