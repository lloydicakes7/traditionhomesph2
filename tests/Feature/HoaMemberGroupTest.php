<?php

namespace Tests\Feature;

use App\Http\Livewire\Admin\HoaCenter\Members\MemberList;
use App\Http\Livewire\Admin\HoaCenter\Members\Modals\MemberDeleteModal;
use App\Http\Livewire\Admin\HoaCenter\Members\Modals\MemberFormModal;
use App\Models\Admin;
use App\Models\HoaMember;
use App\Models\HoaMemberGroup;
use App\Models\Role;
use App\Queries\Documents\DocumentStaffQuery;
use App\Queries\HoaCenter\Members\MemberListQuery;
use App\Utility\SettingsHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class HoaMemberGroupTest extends TestCase
{
    use RefreshDatabase;

    private function signIn(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'member-group-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'is_active'           => true,
            'role_id'             => $role->id,
        ]);

        $this->actingAs($admin, 'web');
    }

    public function test_the_default_groups_exist_without_duplicates(): void
    {
        $this->assertSame(1, HoaMemberGroup::where('name', HoaMemberGroup::STAFF)->count());
        $this->assertSame(1, HoaMemberGroup::where('name', HoaMemberGroup::BOARD)->count());
    }

    public function test_a_member_can_be_added_to_an_existing_group(): void
    {
        $this->signIn();

        $group = HoaMemberGroup::where('name', HoaMemberGroup::STAFF)->firstOrFail();

        Livewire::test(MemberFormModal::class)
            ->call('openCreate')
            ->set('form.name', 'Juana Cruz')
            ->set('form.position', 'Secretary')
            ->set('form.hoa_member_group_id', (string) $group->id)
            ->call('save')
            ->assertEmitted('member-saved');

        $this->assertDatabaseHas('hoa_members', [
            'name'                => 'Juana Cruz',
            'hoa_member_group_id' => $group->id,
        ]);
    }

    public function test_a_member_can_be_added_with_a_new_group(): void
    {
        $this->signIn();

        Livewire::test(MemberFormModal::class)
            ->call('openCreate')
            ->set('form.name', 'Pedro Reyes')
            ->set('form.position', 'Committee Head')
            ->set('form.hoa_member_group_id', MemberFormModal::NEW_GROUP)
            ->set('form.new_group_name', 'Committee Heads')
            ->call('save')
            ->assertEmitted('member-saved');

        $group = HoaMemberGroup::where('name', 'Committee Heads')->first();

        $this->assertNotNull($group);
        $this->assertDatabaseHas('hoa_members', [
            'name'                => 'Pedro Reyes',
            'hoa_member_group_id' => $group->id,
        ]);
    }

    public function test_the_member_list_separates_staff_and_board_into_sections(): void
    {
        $this->signIn();

        $staff = HoaMemberGroup::where('name', HoaMemberGroup::STAFF)->firstOrFail();
        $board = HoaMemberGroup::where('name', HoaMemberGroup::BOARD)->firstOrFail();

        HoaMember::create([
            'name'                => 'Staff Member',
            'position'            => 'Secretary',
            'hoa_member_group_id' => $staff->id,
        ]);

        HoaMember::create([
            'name'                => 'Board Member',
            'position'            => 'Peace and Order Committee Head',
            'hoa_member_group_id' => $board->id,
        ]);

        $sections = app(MemberListQuery::class)->sections();

        $this->assertSame(['Staff Member'], $sections->firstWhere('name', HoaMemberGroup::STAFF)->members->pluck('name')->all());
        $this->assertSame(['Board Member'], $sections->firstWhere('name', HoaMemberGroup::BOARD)->members->pluck('name')->all());

        Livewire::test(MemberList::class)
            ->assertSeeInOrder([
                HoaMemberGroup::STAFF,
                'Staff Member',
                HoaMemberGroup::BOARD,
                'Board Member',
            ]);
    }

    public function test_the_new_group_input_is_visible_and_disabled_unless_new_group_is_selected(): void
    {
        $this->signIn();

        $staff = HoaMemberGroup::where('name', HoaMemberGroup::STAFF)->firstOrFail();

        $component = Livewire::test(MemberFormModal::class)->call('openCreate');

        $component->assertSee('New Group');

        $this->assertSame(1, preg_match('/name="form\.new_group_name"[^>]*disabled/', $component->lastRenderedDom));

        $component->set('form.hoa_member_group_id', (string) $staff->id);

        $this->assertSame(1, preg_match('/name="form\.new_group_name"[^>]*disabled/', $component->lastRenderedDom));

        $component->set('form.hoa_member_group_id', MemberFormModal::NEW_GROUP);

        $this->assertSame(0, preg_match('/name="form\.new_group_name"[^>]*disabled/', $component->lastRenderedDom));
    }

    public function test_switching_from_new_group_to_an_existing_group_clears_the_new_group_name(): void
    {
        $this->signIn();

        $staff = HoaMemberGroup::where('name', HoaMemberGroup::STAFF)->firstOrFail();

        Livewire::test(MemberFormModal::class)
            ->call('openCreate')
            ->set('form.hoa_member_group_id', MemberFormModal::NEW_GROUP)
            ->set('form.new_group_name', 'Temporary Group')
            ->set('form.hoa_member_group_id', (string) $staff->id)
            ->assertSet('form.new_group_name', '')
            ->set('form.name', 'Juana Cruz')
            ->set('form.position', 'Secretary')
            ->call('save')
            ->assertEmitted('member-saved');

        $this->assertDatabaseHas('hoa_members', [
            'name'                => 'Juana Cruz',
            'hoa_member_group_id' => $staff->id,
        ]);
        $this->assertDatabaseMissing('hoa_member_groups', ['name' => 'Temporary Group']);
    }

    public function test_existing_members_can_still_be_edited_and_deleted(): void
    {
        $this->signIn();

        $staff = HoaMemberGroup::where('name', HoaMemberGroup::STAFF)->firstOrFail();
        $board = HoaMemberGroup::where('name', HoaMemberGroup::BOARD)->firstOrFail();

        $member = HoaMember::create([
            'name'                => 'Old Name',
            'position'            => 'Secretary',
            'hoa_member_group_id' => $staff->id,
        ]);

        Livewire::test(MemberFormModal::class)
            ->call('openEdit', $member->id)
            ->set('form.name', 'New Name')
            ->set('form.hoa_member_group_id', (string) $board->id)
            ->call('save')
            ->assertEmitted('member-saved');

        $this->assertDatabaseHas('hoa_members', [
            'id'                  => $member->id,
            'name'                => 'New Name',
            'hoa_member_group_id' => $board->id,
        ]);

        Livewire::test(MemberDeleteModal::class)
            ->call('load', $member->id)
            ->call('delete')
            ->assertEmitted('member-saved');

        $this->assertDatabaseMissing('hoa_members', ['id' => $member->id]);
    }

    public function test_print_forms_read_signatories_from_settings_and_the_board_from_its_group(): void
    {
        SettingsHelper::set('president_name', 'Maria President', 'print');
        SettingsHelper::set('treasurer_name', 'Lourdes Treasurer', 'print');
        SettingsHelper::set('grievance_name', 'Carmen Grievance', 'print');
        SettingsHelper::set('membership_checker_1', 'Ana Checker', 'print');
        SettingsHelper::set('membership_checker_2', 'Bela Checker', 'print');

        $board = HoaMemberGroup::where('name', HoaMemberGroup::BOARD)->firstOrFail();

        HoaMember::create([
            'name'                => 'Board Member',
            'position'            => 'Peace and Order Committee',
            'hoa_member_group_id' => $board->id,
        ]);

        HoaMember::create([
            'name'     => 'Staff President',
            'position' => 'President',
        ]);

        $staff = app(DocumentStaffQuery::class)->build();

        $this->assertSame('Maria President', $staff['signatories']['president']);
        $this->assertSame('Maria President', $staff['signer']['name']);
        $this->assertSame('Lourdes Treasurer', $staff['signatories']['treasurer']);
        $this->assertSame('Carmen Grievance', $staff['grievance']['name']);
        $this->assertSame('Ana Checker', $staff['checkers'][0]['name']);
        $this->assertSame('Bela Checker', $staff['checkers'][1]['name']);
        $this->assertSame(['Board Member'], array_column($staff['board'], 'name'));
        $this->assertSame('Staff President', $staff['president']['name']);
    }
}
