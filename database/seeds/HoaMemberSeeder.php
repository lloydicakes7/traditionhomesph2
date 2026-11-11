<?php

use App\Models\HoaMember;
use Illuminate\Database\Seeder;

class HoaMemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $members = [
            [
                'name'     => 'Ricardo Delos Santos',
                'position' => 'President',
                'phone'    => '0917 100 0001',
                'email'    => 'president@traditionhomes.ph',
                'note'     => 'Presides over board meetings and represents the association to the developer.',
            ],
            [
                'name'     => 'Maria Elena Cruz',
                'position' => 'Vice President',
                'phone'    => '0917 100 0002',
                'email'    => 'vicepresident@traditionhomes.ph',
                'note'     => 'Oversees the committees and stands in for the President when needed.',
            ],
            [
                'name'     => 'Antonio Reyes',
                'position' => 'Secretary',
                'phone'    => '0917 100 0003',
                'email'    => 'secretary@traditionhomes.ph',
                'note'     => 'Keeps the minutes, the member records and the association correspondence.',
            ],
            [
                'name'     => 'Lourdes Bautista',
                'position' => 'Treasurer',
                'phone'    => '0917 100 0004',
                'email'    => 'treasurer@traditionhomes.ph',
                'note'     => 'Handles dues collection, the association budget and financial reports.',
            ],
            [
                'name'     => 'Gabriel Mendoza',
                'position' => 'Auditor',
                'phone'    => '0917 100 0005',
                'note'     => 'Reviews the association books and reports to the board every quarter.',
            ],
            [
                'name'     => 'Teresa Villanueva',
                'position' => 'Peace and Order Committee Head',
                'phone'    => '0917 100 0006',
                'note'     => 'Coordinates with the security agency on village rules and incident reports.',
            ],
            [
                'name'     => 'Fernando Aquino',
                'position' => 'Maintenance Committee Head',
                'phone'    => '0917 100 0007',
                'note'     => 'Oversees repairs, landscaping and the common-area upkeep schedule.',
            ],
            [
                'name'     => 'Carmelita Santos',
                'position' => 'Ways and Means Committee Head',
                'phone'    => '0917 100 0008',
                'note'     => 'Handles fundraising activities and community events.',
            ],
        ];

        foreach ($members as $member) {
            HoaMember::firstOrCreate(['name' => $member['name']], $member);
        }

        $this->command->info('HOA members seeded successfully.');
    }
}
