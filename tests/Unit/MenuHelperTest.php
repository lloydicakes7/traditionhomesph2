<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Utility\MenuHelper;
use Tests\TestCase;

/**
 * The sidebar's sections, as the menu compiler leaves them.
 *
 * The package withholds what an officer's role may not open — and a submenu it
 * emptied along with it — before this helper ever sees the list, so all that is
 * left to decide is whether a title still has something under it.
 *
 * A title is a header item; everything else in the list is a link or a submenu
 * (a bare string is a title too, the way the package reads the menu).
 */
class MenuHelperTest extends TestCase
{
    public function test_a_title_with_nothing_under_it_is_dropped(): void
    {
        $this->assertSame([], MenuHelper::withoutEmptySections([$this->title('FINANCE')]));
        $this->assertSame([], MenuHelper::withoutEmptySections(['FINANCE']));
        $this->assertSame([], MenuHelper::withoutEmptySections([]));
    }

    public function test_a_title_introduces_what_follows_it(): void
    {
        $items = [$this->title('OPERATIONS'), $this->link('Addresses'), $this->link('Properties')];

        $this->assertSame($items, MenuHelper::withoutEmptySections($items));
    }

    public function test_a_section_is_measured_up_to_the_next_title(): void
    {
        $operations = $this->title('OPERATIONS');
        $addresses = $this->link('Addresses');
        $finance = $this->title('FINANCE');

        $this->assertSame(
            [$operations, $addresses],
            MenuHelper::withoutEmptySections([$operations, $addresses, $finance])
        );
    }

    public function test_a_run_of_titles_keeps_only_the_one_an_item_follows(): void
    {
        $operations = $this->title('OPERATIONS');
        $finance = $this->title('FINANCE');
        $addresses = $this->link('Addresses');

        $this->assertSame(
            [$finance, $addresses],
            MenuHelper::withoutEmptySections([$operations, $finance, $addresses])
        );
    }

    public function test_a_title_before_a_submenu_stands(): void
    {
        $operations = $this->title('OPERATIONS');
        $homeowners = ['text' => 'Homeowners', 'submenu' => [$this->link('Addresses')]];

        $this->assertSame(
            [$operations, $homeowners],
            MenuHelper::withoutEmptySections([$operations, $homeowners])
        );
    }

    public function test_a_menu_the_package_left_holes_in_is_still_read_in_order(): void
    {
        // The package filters its sections without renumbering them.
        $operations = $this->title('OPERATIONS');
        $addresses = $this->link('Addresses');
        $finance = $this->title('FINANCE');

        $this->assertSame(
            [$operations, $addresses],
            MenuHelper::withoutEmptySections([3 => $operations, 7 => $addresses, 9 => $finance])
        );
    }

    /**
     * A section title, as the menu config writes one.
     *
     * @return array<string, string>
     */
    private function title(string $text): array
    {
        return ['header' => $text];
    }

    /**
     * A page the sidebar links to, in the shape the compiler leaves it.
     *
     * @return array<string, string>
     */
    private function link(string $text): array
    {
        return ['text' => $text, 'route' => 'admin.home', 'href' => '/admin'];
    }
}
