<?php

namespace App\Queries\Concerns;

/**
 * Fuse the User Manual's catalogue with its copy.
 *
 * `config/manual.php` names the groups and topics (their keys, icons and — on
 * the admin side — the abilities that open them); the words live in the
 * `manual` translation namespace. Both sides turn the one into the other the
 * same way, so the composition lives here rather than in each query: a
 * group's header key is translated, and each topic's key is merged with that
 * topic's translated `title` and `sections`.
 *
 * The output is the shape the manual's Blades already read: groups of
 * `['title', 'topics']`, each topic carrying its `key`, `icon` and sections.
 */
trait ComposesManualContent
{
    /**
     * Translate each group's header and merge each topic with its copy.
     *
     * @param  array<int, array<string, mixed>>  $groups  The catalogue's groups
     * @param  string  $side  Which half of the catalogue (`admin` or `portal`)
     * @return array<int, array<string, mixed>>
     */
    protected function composeGroups(array $groups, string $side): array
    {
        $composed = [];

        foreach ($groups as $group) {
            $topics = [];

            foreach ($group['topics'] as $topic) {
                $topics[] = array_merge($topic, (array) trans("manual::$side.topics.{$topic['key']}"));
            }

            if ($topics === []) {
                continue;
            }

            $composed[] = [
                'title'  => $group['title'] === null ? null : trans("manual::$side.groups.{$group['title']}"),
                'topics' => $topics,
            ];
        }

        return $composed;
    }

    /**
     * The topic to show: the named one, or the first when none is named. Null
     * when no topic carries that name.
     *
     * @param  array<int, array<string, mixed>>  $groups
     * @return array<string, mixed>|null
     */
    protected function activeTopic(array $groups, ?string $topic): ?array
    {
        $topics = [];

        foreach ($groups as $group) {
            foreach ($group['topics'] as $item) {
                $topics[] = $item;
            }
        }

        if ($topic === null) {
            return $topics[0] ?? null;
        }

        foreach ($topics as $item) {
            if ($item['key'] === $topic) {
                return $item;
            }
        }

        return null;
    }
}
