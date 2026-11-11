<?php

namespace App\Http\Livewire\Admin\HoaCenter\Directory\Modals;

use App\Actions\HoaCenter\Directory\CreateDirectoryEntry;
use App\Actions\HoaCenter\Directory\UpdateDirectoryEntry;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Directory\DirectoryEntry;
use App\Queries\HoaCenter\Directory\DirectoryListQuery;

class DirectoryFormModal extends AdminModalComponent
{
    const NEW_GROUP = 'new';

    public $isEditing = false;

    public $editingEntryId = null;

    public $phones = [];

    protected $listeners = [
        'openDirectoryFormModal'  => 'openCreate',
        'loadDirectoryFormModal'  => 'openEdit',
        'closeDirectoryFormModal' => 'closeModal',
    ];

    // =====================================================================
    // Form
    // =====================================================================

    public $form = [
        'name'               => '',
        'role'               => '',
        'email'              => '',
        'note'               => '',
        'directory_group_id' => '',
        'new_group_name'     => '',
    ];

    // =====================================================================
    // Boot
    // =====================================================================

    /** @var DirectoryListQuery */
    protected $query;

    /** @var CreateDirectoryEntry */
    protected $createEntry;

    /** @var UpdateDirectoryEntry */
    protected $updateEntry;

    public function boot(
        DirectoryListQuery $query,
        CreateDirectoryEntry $createEntry,
        UpdateDirectoryEntry $updateEntry
    ) {
        $this->query = $query;
        $this->createEntry = $createEntry;
        $this->updateEntry = $updateEntry;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    protected function rules(): array
    {
        $rules = [
            'form.name'           => ['required', 'string', 'max:150'],
            'form.role'           => ['nullable', 'string', 'max:100'],
            'form.email'          => ['nullable', 'email', 'max:150'],
            'form.note'           => ['nullable', 'string', 'max:1000'],
            'form.new_group_name' => ['nullable', 'string', 'max:100', 'required_if:form.directory_group_id,' . self::NEW_GROUP],
            'phones'              => ['required', 'array', 'min:1'],
            'phones.*.label'      => ['nullable', 'string', 'max:100'],
            'phones.*.number'     => ['required', 'string', 'max:50'],
        ];

        $rules['form.directory_group_id'] = $this->form['directory_group_id'] === self::NEW_GROUP
            ? ['required', 'string']
            : ['required', 'integer', 'exists:directory_groups,id'];

        return $rules;
    }

    // =====================================================================
    // Phone methods
    // =====================================================================

    public function addPhone(): void
    {
        $this->phones[] = $this->blankPhone();
    }

    public function removePhone($index): void
    {
        $index = (int) $index;

        if (count($this->phones) <= 1) {
            $this->phones = [$this->blankPhone()];

            return;
        }

        unset($this->phones[$index]);
        $this->phones = array_values($this->phones);
    }

    // =====================================================================
    // Open / close
    // =====================================================================

    public function openCreate(): void
    {
        $this->authorize('create', DirectoryEntry::class);

        $this->isEditing = false;
        $this->editingEntryId = null;
        $this->form = [
            'name'               => '',
            'role'               => '',
            'email'              => '',
            'note'               => '',
            'directory_group_id' => '',
            'new_group_name'     => '',
        ];
        $this->phones = [$this->blankPhone()];

        $this->resetValidation();

        $this->markLoaded();
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function openEdit(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('update', DirectoryEntry::class);

        $entry = $this->query->find((int) $id);

        $this->isEditing = true;
        $this->editingEntryId = $entry->id;
        $this->form = [
            'name'               => $entry->name,
            'role'               => $entry->role ?? '',
            'email'              => $entry->email ?? '',
            'note'               => $entry->note ?? '',
            'directory_group_id' => (string) $entry->directory_group_id,
            'new_group_name'     => '',
        ];

        $this->phones = $entry->phones->map(function ($phone) {
            return [
                'label'  => $phone->label ?? '',
                'number' => $phone->number,
            ];
        })->values()->all();

        if (empty($this->phones)) {
            $this->emptyPhones();
        }

        $this->resetValidation();

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->isEditing = false;
        $this->editingEntryId = null;
        $this->phones = [];
        $this->resetValidation();

        parent::resetModal();
    }

    // =====================================================================
    // Save
    // =====================================================================

    public function save(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->authorize($this->isEditing ? 'update' : 'create', DirectoryEntry::class);
        $this->validate();

        $payload = $this->form;
        $payload['phones'] = $this->phones;

        if ($this->isEditing) {
            $this->updateEntry->handle(
                $this->query->find($this->editingEntryId),
                $payload,
                auth('web')->user()
            );
        } else {
            $this->createEntry->handle($payload, auth('web')->user());
        }

        $this->emit('notify', 'success',
            $this->isEditing
                ? 'Directory entry updated successfully.'
                : 'Directory entry added successfully.');

        $this->emit('directory-saved');
        $this->dispatchBrowserEvent('modal-close', ['id' => 'directoryFormModal']);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    /**
     * @return array<string, string>
     */
    private function blankPhone(): array
    {
        return [
            'label'  => '',
            'number' => '',
        ];
    }

    private function emptyPhones(): void
    {
        $this->phones = [$this->blankPhone()];
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.hoa-center.directory.modals.directory-form-modal', [
            'groups' => $this->query->groupsWithCounts(),
        ]);
    }
}
