<?php

namespace App\Queries\Portal;

use App\Queries\Concerns\ComposesManualContent;

class ManualQuery
{
    use ComposesManualContent;

    // ===== Queries =====

    public function page(?string $topic): array
    {
        $groups = $this->composeGroups(config('manual.portal.groups', []), 'portal');
        $active = $this->activeTopic($groups, $topic);

        return [
            'side'      => 'portal',
            'title'     => trans('manual::portal.title'),
            'intro'     => trans('manual::portal.intro'),
            'routeName' => 'portal.manual',
            'homeRoute' => 'portal.dashboard',
            'groups'    => $groups,
            'active'    => $active,
            'toc'       => $active['sections'] ?? [],
            'locale'    => app()->getLocale(),
            'locales'   => config('manual.locales', []),
        ];
    }
}
