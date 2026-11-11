<?php

use App\Enums\DefaultRoleSlug;
use App\Models\Role;
use App\Utility\PermissionHelper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PermissionSeeder extends Seeder
{
    /**
     * The modules the Visitor records data for: the association's own records,
     * the directory and published data, the amenity bookings, and the
     * violations and complaints it files and answers.
     *
     * @var array<int, string>
     */
    const VISITOR_MODULES = [
        'Addresses',
        'Homeowners',
        'Residents',
        'Properties',
        'Households',
        'Directory',
        'Members',
        'Announcements',
        'Amenities',
        'Amenity Bookings',
        'Requests',
        'Violations',
        'Complaints',
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::where('name', DefaultRoleSlug::ADMINISTRATOR)->update(['permissions' => null]);

        $visitor = Role::where('name', DefaultRoleSlug::VISITOR)->first();

        if ($visitor) {
            $visitor->update(['permissions' => $this->visitorKeys()]);
        }

        $this->command->info('Role permissions seeded successfully.');
    }

    /**
     * What the Visitor may do: the records it keeps, everything except
     * deleting them.
     *
     * @return array<int, string>
     */
    private function visitorKeys(): array
    {
        $keys = [];

        foreach (self::VISITOR_MODULES as $module) {
            foreach (PermissionHelper::keysForModule($module) as $key) {
                if (in_array('View', PermissionHelper::typesFor($module), true)) {
                    $keys[] = PermissionHelper::key($module, 'View');
                }
            }
        }

        return $keys;
    }
}
