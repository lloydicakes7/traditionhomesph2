<?php

namespace App\Http\Livewire\Admin\Homeowners\Verification;

use App\Http\Livewire\AdminComponent;
use App\Models\User;
use App\Queries\Homeowners\Profiles\ProfileVerificationListQuery;
use Livewire\WithPagination;

/**
 * The profiles residents submitted, in the order the office works them.
 *
 * The details dialog opens from the row's Review button through the shared
 * modal loader, which carries the account's id.
 */
class ProfileVerificationTableList extends AdminComponent
{
    use WithPagination;

    /** @var string Search query */
    public $search = '';

    /** @var array<string> Query-string persistent state */
    protected $queryString = ['search'];

    /** @var array<string, string> Cross-component listeners */
    protected $listeners = [
        'profile-verification-decided' => 'refreshList',
    ];

    // ===== Boot =====

    /** @var ProfileVerificationListQuery */
    protected $query;

    public function boot(ProfileVerificationListQuery $query): void
    {
        $this->query = $query;
    }

    public function mount(): void
    {
        $this->authorize('viewProfileVerifications', User::class);
    }

    // ===== Actions =====

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function refreshList(): void
    {
        $this->resetPage();
    }

    // ===== Render =====

    public function render()
    {
        return view('livewire.admin.homeowners.verification.profile-verification-table-list', [
            'accounts' => $this->query->paginate($this->search),
        ]);
    }
}
