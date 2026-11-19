<?php

namespace Tests\Feature;

use App\Http\Livewire\Admin\HoaCenter\Directory\Modals\DirectoryFormModal;
use App\Models\Admin;
use App\Models\Directory\DirectoryGroup;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class DirectoryFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_new_group_input_is_visible_and_disabled_unless_a_new_group_is_selected(): void
    {
        $this->signIn();

        $group = DirectoryGroup::create(['name' => 'Security']);

        $component = Livewire::test(DirectoryFormModal::class)->call('openCreate');

        $component->assertSee('New Group');
        $this->assertSame(1, preg_match('/name="form\.new_group_name"[^>]*disabled/', $component->lastRenderedDom));

        $component->set('form.directory_group_id', (string) $group->id);
        $this->assertSame(1, preg_match('/name="form\.new_group_name"[^>]*disabled/', $component->lastRenderedDom));

        $component->set('form.directory_group_id', DirectoryFormModal::NEW_GROUP);
        $this->assertSame(0, preg_match('/name="form\.new_group_name"[^>]*disabled/', $component->lastRenderedDom));
    }

    public function test_an_entry_can_be_added_with_a_new_group(): void
    {
        $this->signIn();

        Livewire::test(DirectoryFormModal::class)
            ->call('openCreate')
            ->set('form.name', 'IT Support')
            ->set('form.directory_group_id', DirectoryFormModal::NEW_GROUP)
            ->set('form.new_group_name', 'Technical')
            ->set('phones.0.number', '09171234567')
            ->call('save')
            ->assertEmitted('directory-saved');

        $group = DirectoryGroup::where('name', 'Technical')->first();

        $this->assertNotNull($group);
        $this->assertDatabaseHas('directory_entries', [
            'name'               => 'IT Support',
            'directory_group_id' => $group->id,
        ]);
    }

    public function test_switching_away_from_a_new_group_clears_its_name(): void
    {
        $this->signIn();

        $group = DirectoryGroup::create(['name' => 'Security']);

        Livewire::test(DirectoryFormModal::class)
            ->call('openCreate')
            ->set('form.directory_group_id', DirectoryFormModal::NEW_GROUP)
            ->set('form.new_group_name', 'Temporary')
            ->set('form.directory_group_id', (string) $group->id)
            ->assertSet('form.new_group_name', '');
    }

    private function signIn(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['description' => 'Administrator', 'permissions' => []]
        );

        $admin = Admin::create([
            'name'                => 'Officer',
            'email'               => 'directory-form-' . uniqid() . '@example.test',
            'password'            => Hash::make('Secret123!'),
            'is_active'           => true,
            'role_id'             => $role->id,
        ]);

        $this->actingAs($admin, 'web');
    }
}
