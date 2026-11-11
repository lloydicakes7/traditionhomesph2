<?php

namespace App\Http\Livewire\Public\Contact;

use App\Actions\Support\SendContactMessage;
use App\Http\Livewire\PublicComponent;

class ContactForm extends PublicComponent
{
    public $form = [
        'name'           => '',
        'email'          => '',
        'contact_number' => '',
        'subject'        => '',
        'message'        => '',
    ];

    public $sent = false;

    public $failed = false;

    // ===== Rules =====

    protected function rules(): array
    {
        return [
            'form.name'           => ['required', 'string', 'max:120'],
            'form.email'          => ['required', 'email', 'max:150'],
            'form.contact_number' => ['nullable', 'string', 'max:30'],
            'form.subject'        => ['required', 'string', 'max:150'],
            'form.message'        => ['required', 'string', 'max:3000'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'form.name'           => __('public.contact.name'),
            'form.email'          => __('public.contact.sender_email'),
            'form.contact_number' => __('public.contact.contact_number'),
            'form.subject'        => __('public.contact.subject'),
            'form.message'        => __('public.contact.message'),
        ];
    }

    // ===== Actions =====

    public function send(SendContactMessage $action): void
    {
        $data = $this->validate()['form'];

        $this->sent = $action->handle($data);
        $this->failed = ! $this->sent;

        if (! $this->sent) {
            return;
        }

        $this->form = [
            'name'           => '',
            'email'          => '',
            'contact_number' => '',
            'subject'        => '',
            'message'        => '',
        ];
        $this->resetValidation();
    }

    // ===== Render =====

    public function render()
    {
        return view('livewire.public.contact.contact-form');
    }
}
