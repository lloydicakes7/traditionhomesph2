<?php

namespace App\Queries\Admin;

use App\Models\Admin;
use App\Queries\Concerns\ComposesManualContent;

/**
 * The admin panel's User Manual.
 *
 * `config/manual.php` carries the structure; the copy is read from the `manual`
 * translation namespace. This service narrows the catalogue to what the
 * signed-in officer may actually open: a topic declares the abilities it needs
 * — the same Policies the panel's own pages answer from — and is offered only
 * when the officer passes one of them. A group left with nothing is dropped
 * with its topics, so the manual never lists a tool its reader cannot reach.
 */
class ManualQuery
{
    use ComposesManualContent;

    /**
     * Everything a manual page needs: the title, the officer's own topics and
     * the active one. Returns a null `active` when no such topic is offered,
     * which the controller answers as a 404.
     *
     * @return array<string, mixed>
     */
    public function page(?string $topic): array
    {
        $groups = $this->groups();
        $active = $this->activeTopic($groups, $topic);

        return [
            'side'      => 'admin',
            'title'     => trans('manual::admin.title'),
            'intro'     => trans('manual::admin.intro'),
            'routeName' => 'admin.manual',
            'homeRoute' => 'admin.home',
            'groups'    => $groups,
            'active'    => $active,
            'toc'       => $active['sections'] ?? [],
            'locale'    => app()->getLocale(),
            'locales'   => config('manual.locales', []),
        ];
    }

    /**
     * The catalogue's groups, each narrowed to the topics this officer may open
     * and dropped entirely when nothing under it survives, with the copy of the
     * survivors merged in.
     *
     * @return array<int, array<string, mixed>>
     */
    private function groups(): array
    {
        $admin = auth()->user();
        $groups = [];

        foreach (config('manual.admin.groups', []) as $group) {
            $topics = [];

            foreach ($group['topics'] as $topic) {
                if ($this->allows($admin, $topic)) {
                    $topics[] = $topic;
                }
            }

            if ($topics === []) {
                continue;
            }

            $groups[] = [
                'title'  => $group['title'] ?? null,
                'topics' => $topics,
            ];
        }

        return $this->composeGroups($groups, 'admin');
    }

    /**
     * Whether the officer may open a topic — one allowed ability is enough.
     * A topic that declares none is offered to everyone (the Dashboard).
     *
     * @param  array<string, mixed>  $topic
     */
    private function allows(Admin $admin, array $topic): bool
    {
        if (empty($topic['abilities'])) {
            return true;
        }

        foreach ($topic['abilities'] as $ability) {
            $model = $ability[1] ?? null;

            if ($admin->can($ability[0], $model ? [$model] : [])) {
                return true;
            }
        }

        return false;
    }
}
