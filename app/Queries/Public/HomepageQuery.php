<?php

namespace App\Queries\Public;

use App\Models\HoaMember;
use App\Models\Property;
use App\Models\Resident;
use App\Models\User;
use App\Queries\HoaCenter\Announcements\AnnouncementListQuery;
use App\Queries\HoaCenter\Faqs\FaqListQuery;
use App\Queries\HoaCenter\Members\MemberListQuery;
use App\Queries\Settings\GeneralSettingsQuery;
use App\Utility\MapHelper;

class HomepageQuery
{
    private AnnouncementListQuery $announcements;

    private MemberListQuery $members;

    private FaqListQuery $faqs;

    private GeneralSettingsQuery $settings;

    public function __construct(
        AnnouncementListQuery $announcements,
        MemberListQuery $members,
        GeneralSettingsQuery $settings,
        FaqListQuery $faqs
    ) {
        $this->announcements = $announcements;
        $this->members = $members;
        $this->settings = $settings;
        $this->faqs = $faqs;
    }

    // ===== Queries =====

    public function page(): array
    {
        $announcements = $this->announcements->preview();
        $members = $this->members->preview();

        $mapQuery = trim((string) $this->settings->get('address'))
            ?: (string) $this->settings->get('hoa_name');

        return [
            'announcements'        => $announcements['items'],
            'hasMoreAnnouncements' => $announcements['hasMore'],
            'members'              => $members['items'],
            'hasMoreMembers'       => $members['hasMore'],
            'map'                  => $this->map($mapQuery),
            'faqs'                 => $this->faqs->all(),
            'stats'                => $this->summary(),
        ];
    }

    public function summary(): array
    {
        return [
            'homeowners' => User::count(),
            'residents'  => Resident::count(),
            'properties' => Property::count(),
            'officers'   => HoaMember::count(),
        ];
    }

    // ===== Helpers =====

    private function map(string $query): array
    {
        return [
            'query'          => $query,
            'embed_url'      => MapHelper::embedUrl($query),
            'directions_url' => MapHelper::directionsUrl($query),
        ];
    }
}
