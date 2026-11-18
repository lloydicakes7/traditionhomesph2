<?php

namespace App\Queries\Public;

use App\Models\Content;

class ContentQuery
{
    // ===== Queries =====

    public function page(string $slug): ?Content
    {
        return Content::query()->where('slug', $slug)->first();
    }

    public function menu(): array
    {
        $contents = Content::query()
            ->ordered()
            ->get(['id', 'title', 'slug', 'group']);

        $pages = [];
        $groups = [];

        foreach ($contents as $content) {
            $link = ['label' => $content->title, 'href' => $content->url];

            if ($content->group === null || $content->group === '') {
                $pages[] = $link;

                continue;
            }

            $groups[$content->group][] = $link;
        }

        $menus = [];

        foreach ($groups as $label => $links) {
            $menus[] = ['label' => $label, 'links' => $links];
        }

        return ['pages' => $pages, 'groups' => $menus];
    }
}
