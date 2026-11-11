<?php

namespace App\Http\Livewire\Admin\Settings\Modals;

use App\Actions\Collections\PaymentMethods\CreatePaymentMethod;
use App\Actions\Collections\PaymentMethods\UpdatePaymentMethod;
use App\Http\Livewire\AdminModalComponent;
use App\Models\Setting;
use App\Queries\Collections\PaymentMethods\PaymentMethodQuery;
use Livewire\WithFileUploads;


class PaymentMethodFormModal extends AdminModalComponent
{
    use WithFileUploads;

    public $methodId = null;

    public $form = [
        'name'      => '',
        'details'   => '',
        'is_active' => true,
    ];

    public $qr;

    public $qr_path = null;

    public $removeQr = false;

    // =====================================================================
    // Boot
    // =====================================================================

    protected $listeners = [
        'openPaymentMethodFormModal'  => 'openCreate',
        'loadPaymentMethodFormModal'  => 'openEdit',
        'closePaymentMethodFormModal' => 'closeModal',
    ];

    /** @var PaymentMethodQuery */
    protected $query;

    /** @var CreatePaymentMethod */
    protected $createMethod;

    /** @var UpdatePaymentMethod */
    protected $updateMethod;

    public function boot(
        PaymentMethodQuery $query,
        CreatePaymentMethod $createMethod,
        UpdatePaymentMethod $updateMethod
    ) {
        $this->query = $query;
        $this->createMethod = $createMethod;
        $this->updateMethod = $updateMethod;
    }

    // =====================================================================
    // Lazy load
    // =====================================================================

    public function openCreate(): void
    {
        $this->authorize('update', Setting::class);

        $this->resetForm();
        $this->resetValidation();

        $this->markLoaded();
    }

    public function openEdit(?int $id = null): void
    {
        if (! $id) {
            return;
        }

        $this->authorize('update', Setting::class);

        $this->resetForm();
        $this->resetValidation();

        $this->methodId = (int) $id;

        $method = $this->query->find($this->methodId);

        $this->form = [
            'name'      => (string) $method->name,
            'details'   => (string) $method->details,
            'is_active' => (bool) $method->is_active,
        ];

        $this->qr_path = $method->qr_path;

        $this->markLoaded();
    }

    protected function resetModal(): void
    {
        $this->resetForm();

        parent::resetModal();
    }

    // =====================================================================
    // QR
    // =====================================================================

    public function removeQr(): void
    {
        $this->qr = null;
        $this->removeQr = true;
    }

    // =====================================================================
    // Save
    // =====================================================================

    public function confirm(): void
    {
        if (! $this->loaded) {
            return;
        }

        $this->authorize('update', Setting::class);

        $this->validate([
            'form.name'      => ['required', 'string', 'max:255'],
            'form.details'   => ['nullable', 'string', 'max:1000'],
            'form.is_active' => ['boolean'],
            'qr'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:2048'],
        ], [
            'form.name.required' => 'Give the payment method a name.',
            'qr.image'           => 'The QR code must be an image.',
            'qr.max'             => 'The QR code must be 2MB or smaller.',
        ], [
            'form.name'    => 'name',
            'form.details' => 'details',
            'qr'           => 'QR code',
        ]);

        $payload = [
            'name'      => $this->form['name'],
            'details'   => $this->form['details'] ?: null,
            'is_active' => (bool) $this->form['is_active'],
        ];

        if ($this->methodId) {
            $method = $this->updateMethod->handle(
                $this->query->find($this->methodId),
                $payload,
                $this->qr,
                $this->removeQr,
                auth('web')->user()
            );

            $this->emit('notify', 'success', '"' . $method->name . '" updated.');
        } else {
            $method = $this->createMethod->handle($payload, $this->qr, auth('web')->user());

            $this->emit('notify', 'success', '"' . $method->name . '" added.');
        }

        $this->emit('payment-method-saved');
        $this->dispatchBrowserEvent('modal-close', ['id' => 'paymentMethodFormModal']);
    }

    // =====================================================================
    // Helpers
    // =====================================================================

    private function resetForm(): void
    {
        $this->methodId = null;
        $this->qr = null;
        $this->qr_path = null;
        $this->removeQr = false;
        $this->form = ['name' => '', 'details' => '', 'is_active' => true];
    }

    // =====================================================================
    // Render
    // =====================================================================

    public function render()
    {
        return view('livewire.admin.settings.modals.payment-method-form-modal');
    }
}
