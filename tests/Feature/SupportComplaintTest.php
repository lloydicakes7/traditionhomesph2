<?php

namespace Tests\Feature;

use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use App\Http\Livewire\Admin\Support\Complaints\Modals\ComplaintFormModal;
use App\Http\Livewire\Admin\Support\Complaints\Modals\ComplaintViewModal;
use App\Http\Livewire\Portal\Complaints\Modals\ComplaintRequestModal;
use App\Models\Admin;
use App\Models\Complaint;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class SupportComplaintTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_homeowner_files_a_complaint_with_evidence_and_an_other_description(): void
    {
        Storage::fake('local');

        $user = factory(User::class)->create();
        $image = $this->png('evidence.png');
        $pdf = UploadedFile::fake()->create('report.pdf', 100, 'application/pdf');
        $notes = UploadedFile::fake()->create('notes.txt', 1, 'text/plain');

        $component = Livewire::actingAs($user, 'homeowner')
            ->test(ComplaintRequestModal::class)
            ->call('open');

        $component->set('attachments', [$image, $pdf, $notes])
            ->assertSee('Attachments')
            ->assertSee('evidence.png')
            ->assertSee('report.pdf')
            ->assertDontSee('Image Evidence')
            ->assertDontSee('Supporting Files')
            ->call('removeAttachment', 2);

        $component->set('form.category', ComplaintCategory::OTHER)
            ->set('form.other_description', 'Construction noise')
            ->set('form.subject', 'Noise after hours')
            ->set('form.description', 'Loud drilling at 11 PM.')
            ->call('save')
            ->assertHasNoErrors()
            ->assertEmitted('complaint-filed');

        $complaint = Complaint::firstOrFail();

        $this->assertSame($user->resident_id, $complaint->resident_id);
        $this->assertSame('Construction noise', $complaint->other_description);
        $this->assertCount(2, $complaint->attachments);
        $this->assertTrue($complaint->attachments->firstWhere('is_image', true)->is_image);
        $this->assertSame('report.pdf', $complaint->attachments->firstWhere('is_image', false)->original_name);

        foreach ($complaint->attachments as $attachment) {
            Storage::disk('local')->assertExists($attachment->path);
        }
    }

    public function test_other_description_is_required_only_for_the_other_category(): void
    {
        $user = factory(User::class)->create();

        $component = Livewire::actingAs($user, 'homeowner')
            ->test(ComplaintRequestModal::class)
            ->call('open')
            ->set('form.category', ComplaintCategory::OTHER)
            ->set('form.subject', 'Other issue')
            ->set('form.description', 'Something that does not fit a listed category.')
            ->call('save')
            ->assertHasErrors(['form.other_description' => 'required']);

        $this->assertSame(0, Complaint::count());

        $component->set('form.category', ComplaintCategory::NOISE)
            ->set('form.other_description', 'This must not be saved')
            ->call('save')
            ->assertHasNoErrors();

        $complaint = Complaint::firstOrFail();

        $this->assertSame(ComplaintCategory::NOISE, $complaint->category->value);
        $this->assertNull($complaint->other_description);
    }

    public function test_an_admin_records_an_other_complaint_with_its_description(): void
    {
        Storage::fake('local');

        $user = factory(User::class)->create();
        $admin = $this->createAdministrator();
        $attachment = UploadedFile::fake()->create('office-report.pdf', 100, 'application/pdf');

        Livewire::actingAs($admin, 'web')
            ->test(ComplaintFormModal::class)
            ->call('openCreate')
            ->assertSee('Attachments')
            ->assertDontSee('Image Evidence')
            ->assertDontSee('Supporting Files')
            ->set('form.resident_id', (string) $user->resident_id)
            ->set('form.category', ComplaintCategory::OTHER)
            ->set('form.other_description', 'Unlisted concern')
            ->set('form.subject', 'Other concern')
            ->set('form.description', 'A concern outside the listed categories.')
            ->set('attachments', [$attachment])
            ->call('save')
            ->assertHasNoErrors();

        $complaint = Complaint::firstOrFail();

        $this->assertSame($admin->id, $complaint->filed_by_admin_id);
        $this->assertSame('Unlisted concern', $complaint->other_description);
        $this->assertCount(1, $complaint->attachments);
        $this->assertSame('office-report.pdf', $complaint->attachments->first()->original_name);
        Storage::disk('local')->assertExists($complaint->attachments->first()->path);
    }

    public function test_an_admin_can_view_and_download_complaint_attachments(): void
    {
        Storage::fake('local');

        $user = factory(User::class)->create();
        $admin = $this->createAdministrator();
        $complaint = $this->createComplaint($user, [
            'filed_by_admin_id' => $admin->id,
            'category'          => ComplaintCategory::OTHER,
            'other_description' => 'Gate damage',
            'subject'           => 'Broken gate',
            'description'       => 'The gate latch is broken.',
        ]);

        Storage::disk('local')->put('complaints/' . $complaint->id . '/evidence.png', 'image-bytes');

        $attachment = $complaint->attachments()->create([
            'path'          => 'complaints/' . $complaint->id . '/evidence.png',
            'original_name' => 'evidence.png',
            'mime_type'     => 'image/png',
            'size'          => 11,
            'is_image'      => true,
        ]);

        Livewire::actingAs($admin, 'web')
            ->test(ComplaintViewModal::class)
            ->call('load', $complaint->id)
            ->assertSee('Gate damage')
            ->assertSee('evidence.png')
            ->assertSee(route('support.complaints.attachments.show', [$complaint, $attachment]));

        $this->actingAs($admin, 'web')
            ->get(route('support.complaints.attachments.show', [$complaint, $attachment]))
            ->assertOk();

        $this->actingAs($admin, 'web')
            ->get(route('support.complaints.attachments.show', [$complaint, $attachment]) . '?inline=1')
            ->assertOk();
    }

    public function test_a_homeowner_cannot_open_an_admin_complaint_attachment(): void
    {
        Storage::fake('local');

        $user = factory(User::class)->create();
        $complaint = $this->createComplaint($user);

        Storage::disk('local')->put('complaints/' . $complaint->id . '/evidence.png', 'image-bytes');

        $attachment = $complaint->attachments()->create([
            'path'          => 'complaints/' . $complaint->id . '/evidence.png',
            'original_name' => 'evidence.png',
            'mime_type'     => 'image/png',
            'size'          => 11,
            'is_image'      => true,
        ]);

        $this->actingAs($user, 'homeowner')
            ->get(route('support.complaints.attachments.show', [$complaint, $attachment]))
            ->assertStatus(302);
    }

    private function png(string $name): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
        );
    }

    private function createComplaint(User $user, array $overrides = []): Complaint
    {
        return Complaint::create(array_merge([
            'resident_id'    => $user->resident_id,
            'submitter_name' => $user->resident->full_name,
            'category'       => ComplaintCategory::NOISE,
            'subject'        => 'Noise complaint',
            'description'    => 'Loud music after hours.',
            'status'         => ComplaintStatus::PENDING,
        ], $overrides));
    }

    private function createAdministrator(): Admin
    {
        $role = Role::create([
            'name'        => 'Administrator',
            'description' => 'Administrator',
            'permissions' => [],
        ]);

        return Admin::create([
            'name'                => 'Officer',
            'email'               => 'complaints-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'role_id'             => $role->id,
            'is_active'           => true,
        ]);
    }
}
