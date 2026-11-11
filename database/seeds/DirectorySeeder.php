<?php

use App\Models\Directory\DirectoryEntry;
use App\Models\Directory\DirectoryGroup;
use Illuminate\Database\Seeder;

class DirectorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $directory = [
            'Emergency' => [
                [
                    'name'   => 'Emergency Hotline',
                    'role'   => '24/7 Duty Officer',
                    'note'   => 'Call first for fires, medical emergencies, flooding, or any after-hours incident.',
                    'phones' => [
                        ['Hotline', '(02) 8123 4567'],
                        ['Mobile', '0917 123 4567'],
                    ],
                ],
                [
                    'name'   => 'National Emergency Hotline',
                    'role'   => 'Police / Fire / Medical',
                    'note'   => 'For emergencies requiring police, fire, or medical dispatch outside the village.',
                    'phones' => [
                        ['Hotline', '911'],
                    ],
                ],
                [
                    'name'   => 'Ambulance Service',
                    'role'   => 'On-call Paramedic Team',
                    'note'   => 'For medical emergencies requiring immediate transport to a hospital.',
                    'phones' => [
                        ['Hotline', '(02) 8789 1234'],
                        ['Mobile', '0918 789 1234'],
                    ],
                ],
                [
                    'name'   => 'Fire Department',
                    'role'   => 'Local Fire Station',
                    'note'   => 'Report fires or request a standby fire truck for events.',
                    'phones' => [
                        ['Landline', '(02) 8456 1122'],
                    ],
                ],
            ],
            'Security' => [
                [
                    'name'   => 'Ramon Dela Cruz',
                    'role'   => 'Chief Security Officer',
                    'note'   => 'Gate passes, visitor concerns, and lost and found.',
                    'phones' => [
                        ['Mobile', '0918 234 5678'],
                        ['Landline', '(02) 8234 5678'],
                    ],
                ],
                [
                    'name'   => 'Jhon Mark Santos',
                    'role'   => 'Security Guard - Main Entrance',
                    'note'   => 'Handles vehicle entry logs and visitor verification at the main gate.',
                    'phones' => [
                        ['Mobile', '0919 234 5679'],
                    ],
                ],
                [
                    'name'   => 'Michael Reyes',
                    'role'   => 'Security Guard - Back Entrance',
                    'note'   => 'Handles the rear gate, deliveries, and after-hours entry.',
                    'phones' => [
                        ['Mobile', '0920 234 5680'],
                    ],
                ],
            ],
            'IT Support' => [
                [
                    'name'   => 'Karen Villanueva',
                    'role'   => 'IT Coordinator',
                    'email'  => 'itsupport@traditionhomes.ph',
                    'note'   => 'Portal logins, billing portal errors, and CCTV footage requests.',
                    'phones' => [
                        ['Mobile', '0919 345 6789'],
                        ['Trunkline', '(02) 8345 6789'],
                    ],
                ],
                [
                    'name'   => 'Jerome Bautista',
                    'role'   => 'Cybersecurity Specialist',
                    'email'  => 'security.it@traditionhomes.ph',
                    'note'   => 'Handles data breaches, phishing reports, and account security concerns.',
                    'phones' => [
                        ['Mobile', '0921 345 6790'],
                    ],
                ],
                [
                    'name'   => 'Angelica Torres',
                    'role'   => 'Systems Administrator',
                    'email'  => 'sysadmin@traditionhomes.ph',
                    'note'   => 'Manages servers, network infrastructure, and the resident portal backend.',
                    'phones' => [
                        ['Mobile', '0922 345 6791'],
                    ],
                ],
                [
                    'name'   => 'Paolo Mendoza',
                    'role'   => 'IT Support Technician',
                    'note'   => 'On-site repairs, CCTV maintenance, and hardware troubleshooting.',
                    'phones' => [
                        ['Mobile', '0923 345 6792'],
                        ['Landline', '(02) 8345 6793'],
                    ],
                ],
            ],
            'Administration' => [
                [
                    'name'   => 'HOA Office',
                    'role'   => 'Association Secretary',
                    'email'  => 'hoaoffice@traditionhomes.ph',
                    'note'   => 'Mon–Fri, 8:00 AM – 5:00 PM. Dues, clearances, and certificates.',
                    'phones' => [
                        ['Landline', '(02) 8456 7890'],
                    ],
                ],
                [
                    'name'   => 'Board of Directors',
                    'role'   => 'Internal line',
                    'note'   => 'Officers only — meeting schedules and board resolutions.',
                    'phones' => [
                        ['Mobile', '0920 456 7890'],
                    ],
                ],
            ],
        ];

        foreach ($directory as $groupName => $entries) {
            $group = DirectoryGroup::firstOrCreate(['name' => $groupName]);

            foreach ($entries as $position => $entry) {
                $record = DirectoryEntry::firstOrCreate(
                    ['directory_group_id' => $group->id, 'name' => $entry['name']],
                    [
                        'role'       => $entry['role'] ?? null,
                        'email'      => $entry['email'] ?? null,
                        'note'       => $entry['note'] ?? null,
                    ]
                );

                if (! $record->wasRecentlyCreated) {
                    continue;
                }

                foreach ($entry['phones'] as $phone) {
                    $record->phones()->create([
                        'label'  => $phone[0],
                        'number' => $phone[1],
                    ]);
                }
            }
        }

        $this->command->info('Directory seeded successfully.');
    }
}
