<?php

namespace Tests\Feature;

use App\Http\Livewire\Admin\HoaCenter\Members\MemberList as AdminMemberList;
use App\Http\Livewire\Public\Members\MemberList as PublicMemberList;
use App\Models\Admin;
use App\Models\HoaMember;
use App\Models\HoaMemberGroup;
use App\Models\Role;
use App\Queries\HoaCenter\Members\MemberListQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class HoaMemberOrderingPaginationTest extends TestCase
{
    use RefreshDatabase;

    private function signIn(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name'        => 'Officer',
            'email'       => 'member-order-' . uniqid() . '@example.test',
            'password'    => Hash::make('Secret123!'),
            'is_active'   => true,
            'role_id'     => $role->id,
        ]);

        $this->actingAs($admin, 'web');
    }

    private function group(string $name): HoaMemberGroup
    {
        return HoaMemberGroup::firstOrCreate(['name' => $name]);
    }

    private function member(HoaMemberGroup $group, string $name, string $position): HoaMember
    {
        return HoaMember::create([
            'name'                => $name,
            'position'            => $position,
            'hoa_member_group_id' => $group->id,
        ]);
    }

    private function sectionNames(string $groupName): array
    {
        return app(MemberListQuery::class)->sections()
            ->firstWhere('name', $groupName)
            ->members
            ->pluck('name')
            ->all();
    }

    public function test_member_groups_follow_the_required_order_regardless_of_insertion_order(): void
    {
        $committee = $this->group(HoaMemberGroup::COMMITTEE);
        $board = $this->group(HoaMemberGroup::BOARD);
        $executive = $this->group(HoaMemberGroup::EXECUTIVE);

        $this->member($committee, 'Committee Person', 'Election Committee');
        $this->member($board, 'Board Person', 'Maintenance and Beautification');
        $this->member($executive, 'Executive Person', 'President');

        $sections = app(MemberListQuery::class)->sections();

        $this->assertSame(
            [HoaMemberGroup::EXECUTIVE, HoaMemberGroup::BOARD, HoaMemberGroup::COMMITTEE],
            $sections->pluck('name')->all()
        );
    }

    public function test_a_group_named_executive_officer_sorts_first_and_keeps_the_position_order(): void
    {
        $committee = $this->group(HoaMemberGroup::COMMITTEE);
        $board = $this->group(HoaMemberGroup::BOARD);
        $executive = $this->group('Executive Officer');

        $this->member($committee, 'Committee Person', 'Election Committee');
        $this->member($board, 'Board Person', 'Member');
        $this->member($executive, 'Zoe Zulu', 'Auditor');
        $this->member($executive, 'Amy Alpha', 'President');

        $sections = app(MemberListQuery::class)->sections();

        $this->assertSame(
            ['Executive Officer', HoaMemberGroup::BOARD, HoaMemberGroup::COMMITTEE],
            $sections->pluck('name')->all()
        );
        $this->assertSame(['Amy Alpha', 'Zoe Zulu'], $sections->first()->members->pluck('name')->all());
    }

    public function test_executive_officers_follow_the_position_hierarchy_not_their_names(): void
    {
        $executive = $this->group(HoaMemberGroup::EXECUTIVE);

        $this->member($executive, 'Zoe Zulu', 'Auditor');
        $this->member($executive, 'Yuri Yankee', 'Treasurer');
        $this->member($executive, 'Xena Xray', 'Secretary');
        $this->member($executive, 'Wendy Whiskey', 'Vice President');
        $this->member($executive, 'Amy Alpha', 'President');

        $positions = app(MemberListQuery::class)->sections()
            ->firstWhere('name', HoaMemberGroup::EXECUTIVE)
            ->members
            ->pluck('position')
            ->all();

        $this->assertSame(
            ['President', 'Vice President', 'Secretary', 'Treasurer', 'Auditor'],
            $positions
        );
    }

    public function test_unassigned_positions_do_not_disturb_the_officer_order(): void
    {
        $executive = $this->group(HoaMemberGroup::EXECUTIVE);

        $this->member($executive, 'Tess Treasurer', 'Treasurer');
        $this->member($executive, 'Sam Secretary', 'Secretary');
        $this->member($executive, 'Pat President', 'President');

        $positions = app(MemberListQuery::class)->sections()
            ->firstWhere('name', HoaMemberGroup::EXECUTIVE)
            ->members
            ->pluck('position')
            ->all();

        $this->assertSame(['President', 'Secretary', 'Treasurer'], $positions);
    }

    public function test_changing_an_officer_assignment_keeps_the_position_order_dynamic(): void
    {
        $executive = $this->group(HoaMemberGroup::EXECUTIVE);

        $president = $this->member($executive, 'Zoe Zulu', 'President');
        $auditor = $this->member($executive, 'Amy Alpha', 'Auditor');

        $president->update(['position' => 'Auditor']);
        $auditor->update(['position' => 'President']);

        $names = app(MemberListQuery::class)->sections()
            ->firstWhere('name', HoaMemberGroup::EXECUTIVE)
            ->members
            ->pluck('name')
            ->all();

        $this->assertSame(['Amy Alpha', 'Zoe Zulu'], $names);
    }

    public function test_board_members_are_listed_in_the_order_they_were_added(): void
    {
        $board = $this->group(HoaMemberGroup::BOARD);

        $names = ['Zoe Zulu', 'Rizza U. Sayre', 'Amy Alpha'];

        foreach ($names as $name) {
            $this->member($board, $name, 'Member');
        }

        $this->assertSame($names, $this->sectionNames(HoaMemberGroup::BOARD));
    }

    public function test_committee_members_are_listed_in_the_order_they_were_added(): void
    {
        $committee = $this->group(HoaMemberGroup::COMMITTEE);

        $names = ['Zoe Zulu', 'Amy Alpha', 'Merlina H. Acosta'];

        foreach ($names as $name) {
            $this->member($committee, $name, 'Member');
        }

        $this->assertSame($names, $this->sectionNames(HoaMemberGroup::COMMITTEE));
    }

    public function test_editing_a_member_keeps_their_original_position(): void
    {
        $board = $this->group(HoaMemberGroup::BOARD);

        $first = $this->member($board, 'First Added', 'Member');
        $this->member($board, 'Second Added', 'Member');
        $this->member($board, 'Third Added', 'Member');

        $first->update(['name' => 'Zzzz Renamed']);

        $this->assertSame(
            ['Zzzz Renamed', 'Second Added', 'Third Added'],
            $this->sectionNames(HoaMemberGroup::BOARD)
        );
    }

    public function test_groups_outside_the_fixed_three_keep_their_creation_order_below_them(): void
    {
        $zulu = HoaMemberGroup::create(['name' => 'Zulu Group']);
        $alpha = HoaMemberGroup::create(['name' => 'Alpha Group']);
        $committee = $this->group(HoaMemberGroup::COMMITTEE);
        $board = $this->group(HoaMemberGroup::BOARD);
        $executive = $this->group(HoaMemberGroup::EXECUTIVE);

        foreach ([$zulu, $alpha, $committee, $board, $executive] as $index => $group) {
            $this->member($group, 'Member ' . $index, 'Member');
        }

        $this->assertSame(
            [
                HoaMemberGroup::EXECUTIVE,
                HoaMemberGroup::BOARD,
                HoaMemberGroup::COMMITTEE,
                'Zulu Group',
                'Alpha Group',
            ],
            app(MemberListQuery::class)->sections()->pluck('name')->all()
        );
    }

    public function test_each_group_paginates_independently_at_thirty_members_per_page(): void
    {
        $this->signIn();

        $board = $this->group(HoaMemberGroup::BOARD);
        $committee = $this->group(HoaMemberGroup::COMMITTEE);

        for ($i = 1; $i <= 31; $i++) {
            $this->member($board, sprintf('Board Member %02d', $i), 'Member');
        }

        $this->member($committee, 'Committee Member', 'Member');

        $sections = app(MemberListQuery::class)->sections()->keyBy('name');

        $this->assertSame(31, $sections->get(HoaMemberGroup::BOARD)->paginator->total());
        $this->assertSame(2, $sections->get(HoaMemberGroup::BOARD)->paginator->lastPage());
        $this->assertCount(30, $sections->get(HoaMemberGroup::BOARD)->members);
        $this->assertFalse($sections->get(HoaMemberGroup::COMMITTEE)->paginator->hasPages());

        Livewire::test(AdminMemberList::class)
            ->call('gotoPage', 2, 'members_page_' . $board->id)
            ->assertSee('Board Member 31')
            ->assertDontSee('Board Member 01')
            ->assertSee('Committee Member');
    }

    public function test_search_filters_members_and_resets_the_group_page(): void
    {
        $this->signIn();

        $board = $this->group(HoaMemberGroup::BOARD);

        for ($i = 1; $i <= 31; $i++) {
            $this->member($board, sprintf('Board Member %02d', $i), 'Member');
        }

        Livewire::test(AdminMemberList::class)
            ->call('gotoPage', 2, 'members_page_' . $board->id)
            ->assertSee('Board Member 31')
            ->set('search', 'Board Member 01')
            ->assertSee('Board Member 01')
            ->assertDontSee('Board Member 31');
    }

    public function test_the_public_members_page_lists_members_in_creation_order_without_groups(): void
    {
        $executive = $this->group(HoaMemberGroup::EXECUTIVE);
        $board = $this->group(HoaMemberGroup::BOARD);

        $this->member($executive, 'Zoe Zulu', 'President');
        $this->member($board, 'Rolando M. Mirano', 'Member');
        $this->member($executive, 'Amy Alpha', 'Auditor');

        Livewire::test(PublicMemberList::class)
            ->assertSeeInOrder(['Zoe Zulu', 'Rolando M. Mirano', 'Amy Alpha'])
            ->assertDontSee(HoaMemberGroup::EXECUTIVE)
            ->assertDontSee(HoaMemberGroup::BOARD);
    }

    public function test_the_public_members_page_paginates_at_forty_members(): void
    {
        $board = $this->group(HoaMemberGroup::BOARD);

        for ($i = 1; $i <= 41; $i++) {
            $this->member($board, sprintf('Board Member %02d', $i), 'Member');
        }

        Livewire::test(PublicMemberList::class)
            ->assertSee('Board Member 01')
            ->assertDontSee('Board Member 41')
            ->call('gotoPage', 2)
            ->assertSee('Board Member 41')
            ->assertDontSee('Board Member 01');
    }
}
