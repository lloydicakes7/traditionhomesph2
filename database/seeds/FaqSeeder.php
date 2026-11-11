<?php

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $questions = [
            [
                'question' => 'How do I get an account for the Homeowner Portal?',
                'answer'   => 'Fill out the registration form on the Register page, then wait for the HOA officers to review and accept your registration.',
            ],
            [
                'question' => 'How long does it take for my registration to be accepted?',
                'answer'   => 'The officers review each registration manually, and you can sign in once your account has been approved.',
            ],
            [
                'question' => 'When are the monthly dues due?',
                'answer'   => 'Monthly dues are due on the fifth day of every month and are paid in person at the HOA office, where the officers will record your payment.',
            ],
            [
                'question' => 'How do I request a certification or a permit?',
                'answer'   => 'Visit the HOA office and submit your documents and payment, and the officers will process your request on the spot.',
            ],
            [
                'question' => 'What happens after I receive a violation notice?',
                'answer'   => 'Go to the HOA office to acknowledge the notice, and the officers will record how the matter is resolved.',
            ],
            [
                'question' => 'How do I book an amenity for an event?',
                'answer'   => 'Visit the HOA office to reserve your date and settle the booking fee and any deposit, which is tracked separately until it is returned.',
            ],
        ];

        foreach ($questions as $question) {
            Faq::firstOrCreate(['question' => $question['question']], $question);
        }

        $this->command->info('FAQs seeded successfully.');
    }
}