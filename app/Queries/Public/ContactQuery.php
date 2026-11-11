<?php

namespace App\Queries\Public;

use App\Queries\Settings\GeneralSettingsQuery;
use App\Utility\MapHelper;

class ContactQuery
{
    private GeneralSettingsQuery $settings;

    public function __construct(GeneralSettingsQuery $settings)
    {
        $this->settings = $settings;
    }

    // ===== Queries =====

    public function page(): array
    {
        return [
            'map'      => $this->map(),
            'channels' => $this->channels(),
        ];
    }

    // ===== Helpers =====

    private function map(): array
    {
        $query = $this->value('address') ?? $this->value('hoa_name') ?? '';

        return [
            'query'          => $query,
            'embed_url'      => MapHelper::embedUrl($query),
            'directions_url' => MapHelper::directionsUrl($query),
        ];
    }

    private function channels(): array
    {
        $channels = [];

        if ($phone = $this->value('contact_number')) {
            $channels[] = $this->channel(
                'fas fa-fw fa-phone-alt',
                __('public.contact.phone'),
                $phone,
                'tel:' . preg_replace('/[^\d+]/', '', $phone)
            );
        }

        if ($hours = $this->value('working_hours')) {
            $channels[] = $this->channel('far fa-fw fa-clock', __('public.contact.working_hours'), $hours);
        }

        if ($address = $this->value('address')) {
            $channels[] = $this->channel('fas fa-fw fa-map-marker-alt', __('public.contact.address'), $address);
        }

        if ($email = $this->value('email')) {
            $channels[] = $this->channel(
                'fas fa-fw fa-envelope',
                __('public.contact.email'),
                $email,
                'mailto:' . $email
            );
        }

        return $channels;
    }

    private function channel(string $icon, string $label, string $value, ?string $href = null): array
    {
        return [
            'icon'  => $icon,
            'label' => $label,
            'value' => $value,
            'href'  => $href,
        ];
    }

    private function value(string $key): ?string
    {
        $value = trim((string) $this->settings->get($key));

        return $value === '' ? null : $value;
    }
}
