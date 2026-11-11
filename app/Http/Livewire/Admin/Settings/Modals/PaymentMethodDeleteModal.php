<?php

namespace App\Http\Livewire\Admin\Settings\Modals;

use App\Http\Livewire\AdminModalComponent;
use App\Models\Setting;
use App\Actions\Collections\PaymentMethods\DeletePaymentMethod;
use App\Queries\Collections\PaymentMethods\PaymentMethodQuery;

/**
 * The confirmation that removes a payment method.
 *
 * It is a sibling of the settings page rather than a block inside the
 * collections component, so the page keeps only the trigger (a `data-toggle`
 * button carrying the method's id) and this dialog does the loading, the
 * Policy check and the delete — the panel-wide
 * `App\Livewire\ModalComponent` + `public/js/modal-loader.js` contract.
 */
class PaymentMethodDeleteModal extends AdminModalComponent
{
    public $methodId = null;

    public $methodName = '';

    protected $listeners = [
        'loadPaymentMethodDeleteModal'  => 'load',
        'closePaymentMethodDeleteModal' => 'closeModal',
    ];

    /** @var PaymentMethodQuery */
    protected $query;

    /** @var DeletePaymentMethod */
    protected $deleteMethod;

    public function boot(PaymentMethodQuery $query, DeletePaymentMethod $deleteMethod)
    {
        $this->query = $query;
        $this->deleteMethod = $deleteMethod;
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function load(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('update', Setting::class);

        $method = $this->query->find($id);

        $this->methodId = $method->id;
        $this->methodName = (string) $method->name;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->methodId = null;
        $this->methodName = '';

        parent::resetModal();
    }

    // =====================================================================
    // Delete
    // =====================================================================

    public function delete(): void
    {
        if (! $this->loaded || ! $this->methodId) {
            return;
        }

        $this->authorize('update', Setting::class);

        if ($this->deleteMethod->handle($this->query->find($this->methodId), auth('web')->user())) {
            $this->emit('notify', 'success', 'Payment method deleted.');
        } else {
            $this->emit('notify', 'warning', 'Payments have been recorded through that method, so it cannot be deleted. Switch it off instead.');
        }

        $this->emit('payment-method-deleted');
        $this->dispatchBrowserEvent('modal-close', ['id' => 'paymentMethodDeleteModal']);
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.settings.modals.payment-method-delete-modal');
    }
}
