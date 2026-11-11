<?php

use App\Models\Announcement;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $posts = [
            [
                'title'        => 'Water Interruption on Maple Street, September 20',
                'body'         => "The subdivision's water supply will be interrupted on Saturday, September 20, from 8:00 AM to 4:00 PM while the main line along Maple Street is repaired.\n\nPlease store enough water for the day. Containers may be filled at the clubhouse pump from 6:00 AM. We apologize for the inconvenience and thank you for your patience.",
                'published_at' => '2026-09-15 09:00:00',
            ],
            [
                'title'        => 'General Assembly: October 5 at the Clubhouse',
                'body'         => "All homeowners are invited to the semi-annual general assembly on Sunday, October 5, at 2:00 PM in the clubhouse.\n\nAgenda: the treasurer's financial report, the upcoming road-repair project, and the election of two board seats. Proxy forms are available at the HOA office until October 3.",
                'published_at' => '2026-09-08 14:30:00',
            ],
            [
                'title'        => 'Reminder: Dues for the Third Quarter',
                'body'         => "Quarterly association dues were due on September 5. Payments are accepted at the HOA office from Monday to Saturday, 9:00 AM to 5:00 PM, or through the association's bank account.\n\nHomeowners with an outstanding balance are encouraged to settle before October 1 to avoid the late fee.",
                'published_at' => '2026-09-01 08:00:00',
            ],
        ];

        foreach ($posts as $post) {
            Announcement::firstOrCreate(['title' => $post['title']], $post);
        }

        $this->command->info('Announcements seeded successfully.');
    }
}
