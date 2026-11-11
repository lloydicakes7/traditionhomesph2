<?php

namespace App\Queries\Public;

use App\Enums\RequestType;

class ServicesQuery
{
    // ===== Queries =====

    public function page(): array
    {
        return [
            'documents' => $this->documents(),
            'steps'     => __('public.services.steps'),
        ];
    }

    // ===== Helpers =====

    private function documents(): array
    {
        $documents = [];

        foreach (RequestType::cases() as $type) {
            $documents[] = [
                'label'       => $type->label(),
                'icon'        => $type->icon(),
                'description' => __('public.services.documents.' . $type->value),
            ];
        }

        return $documents;
    }
}
