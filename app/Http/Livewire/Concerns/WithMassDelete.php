<?php

namespace App\Http\Livewire\Concerns;

/**
 * Shared mass-delete flow for the admin panel's confirmation dialogs.
 *
 * A list offers "Delete Selected" as a plain Bootstrap trigger: the button
 * carries its selection as the loader's single `data-id`
 * (public/js/modal-loader.js), so the dialog opens at the click, shows its
 * skeleton, and this trait's `load()` turns the ids into the selection it
 * confirms. The list therefore spends no Livewire call opening the dialog —
 * only the dialog loads.
 *
 * The dialog owns the delete and hides itself; the list hears the result
 * through `massDeleteSavedEvent()` and re-reads its rows. Every adopter names
 * the dialog's own DOM id as MODAL_ID, which is the shell the delete dismisses.
 */
trait WithMassDelete
{
    /**
     * Load the selection the list's trigger carried.
     */
    public function load(?string $selected = null): void
    {
        $this->authorize($this->massDeleteAbility(), $this->massDeleteAuthorizable());

        $this->selectedIds = $this->selectedIdsFrom($selected);

        $this->markLoaded();
    }

    /**
     * Delete the selection, announce it, and dismiss the dialog.
     */
    public function deleteMass(): void
    {
        $this->authorize($this->massDeleteAbility(), $this->massDeleteTarget());

        if (empty($this->selectedIds)) {
            return;
        }

        $count = $this->massDeleteIds($this->selectedIds);

        $this->selectedIds = [];

        $this->emit('notify', 'success', "{$count} " . $this->massDeleteLabel() . " deleted successfully.");

        $savedEvent = $this->massDeleteSavedEvent();

        if ($savedEvent !== null) {
            $this->emit($savedEvent);
        }

        $this->afterMassDelete();
    }

    /**
     * Hide the dialog the delete ran in — the adopter's own shell.
     */
    protected function afterMassDelete(): void
    {
        $this->dispatchBrowserEvent('modal-close', ['id' => static::MODAL_ID]);
    }

    /**
     * The ids a `data-id` trigger carried, as integers.
     *
     * The loader reads one attribute off the trigger, so a bulk action's ids
     * arrive as a single comma-separated string rather than as a payload.
     *
     * @param  string|null  $selected
     * @return array<int, int>
     */
    protected function selectedIdsFrom($selected): array
    {
        $ids = array_filter(array_map('intval', explode(',', (string) $selected)));

        return array_values($ids);
    }

    /**
     * A record the delete Policy ability is checked against.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function massDeleteTarget()
    {
        $model = $this->massDeleteAuthorizable();
        $id = collect($this->selectedIds)->first();

        return $id ? $model::findOrNew($id) : new $model;
    }

    /**
     * The Policy ability the delete is checked against.
     *
     * The default is the ordinary `delete`; a dialog whose model is guarded by
     * more than one permission module names its own ability.
     */
    protected function massDeleteAbility(): string
    {
        return 'delete';
    }

    /**
     * Model class the delete Policy ability is checked against.
     */
    abstract protected function massDeleteAuthorizable(): string;

    /**
     * Entity name used in notifications, e.g. "residents".
     */
    abstract protected function massDeleteLabel(): string;

    /**
     * Perform the bulk delete (usually a service call) and return the count.
     *
     * @param  array  $ids
     * @return int
     */
    abstract protected function massDeleteIds(array $ids): int;

    /**
     * The list's refresh event, announced once the selection is gone.
     *
     * The confirmation is a component of its own — a sibling of the list — so
     * this event is the only way the list hears what happened.
     */
    abstract protected function massDeleteSavedEvent(): ?string;
}
