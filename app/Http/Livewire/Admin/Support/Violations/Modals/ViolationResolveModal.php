<?php

namespace App\Http\Livewire\Admin\Support\Violations\Modals;

use App\Http\Livewire\AdminModalComponent;
use App\Models\Violation;
use App\Actions\Support\Violations\ResolveViolation;
use App\Queries\Support\Violations\ViolationDetailQuery;
use Illuminate\Validation\ValidationException;

/**
 * "Resolve Violation" modal — the office closing a violation as addressed.
 *
 * Opens instantly through Bootstrap (`#violationResolveModal`, triggered from
 * the row's actions with `data-toggle` + `data-id`); the record is fetched
 * lazily afterwards, when `public/js/modal-loader.js` fires
 * `loadViolationResolveModal`. Emits `violation-saved` after the record is
 * resolved, which refreshes the list.
 *
 * The dialog says what resolving does and, more importantly, what it does not:
 * it takes the violation off the member's current list and leaves it in their
 * history, and nothing is deleted to make that happen. The notes are optional —
 * an officer closing a violation they already spoke to the homeowner about has
 * nothing further to write.
 */
class ViolationResolveModal extends AdminModalComponent
{
    /** @var int|null ID of the violation being resolved */
    public $resolvingViolationId = null;

    /** @var \App\Models\Violation|null */
    public $violation = null;

    /** @var string Optional note on how it was settled */
    public $resolutionNotes = '';

    /** @var array<string, string> Modal load / close events (see public/js/modal-loader.js) */
    protected $listeners = [
        'loadViolationResolveModal'  => 'load',
        'closeViolationResolveModal' => 'closeModal',
    ];

    /** @var ViolationDetailQuery */
    protected $detail;

    /** @var ResolveViolation */
    protected $resolveViolation;

    public function boot(ViolationDetailQuery $detail, ResolveViolation $resolveViolation)
    {
        $this->detail = $detail;
        $this->resolveViolation = $resolveViolation;
    }

    // =====================================================================
    // Validation
    // =====================================================================

    protected function rules(): array
    {
        return [
            'resolutionNotes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('resolve', Violation::class);

        $this->violation = $this->detail->find($id);
        $this->resolvingViolationId = $this->violation->id;
        $this->resolutionNotes = '';

        $this->resetValidation();

        $this->markLoaded();
    }

    /**
     * Drop the record when the shell closes, so the next open never inherits it.
     */
    protected function resetModal(): void
    {
        $this->resolvingViolationId = null;
        $this->violation = null;
        $this->resolutionNotes = '';

        $this->resetValidation();

        parent::resetModal();
    }

    // =====================================================================
    // Resolve
    // =====================================================================

    public function resolve(): void
    {
        if (! $this->loaded || ! $this->resolvingViolationId) {
            return;
        }

        $this->authorize('resolve', Violation::class);
        $this->validate();

        // A refusal from the action is a real failure, not a success: it is a
        // ValidationException, which lands beside the notes field and leaves
        // the dialog open — no toast, no close, nothing hidden.
        try {
            $this->resolveViolation->handle(
                $this->detail->find($this->resolvingViolationId),
                $this->resolutionNotes,
                auth('web')->user()
            );
        } catch (ValidationException $e) {
            // The status is not a form field here, so the message cannot attach
            // itself to one: surface it as the dialog's own error instead.
            $this->addError('resolutionNotes', $e->errors()['resolutionNotes'][0] ?? 'This violation can no longer be resolved.');

            return;
        }

        $this->emit('notify', 'success', 'Violation resolved — it has moved to the homeowner\'s violation history.');
        $this->emit('violation-saved');

        $this->dispatchBrowserEvent('modal-close', ['id' => 'violationResolveModal']);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.support.violations.modals.violation-resolve-modal');
    }
}
