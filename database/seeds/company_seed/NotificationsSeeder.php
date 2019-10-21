<?php

use Illuminate\Database\Seeder;
use App\Models\Notifications;
use App\Helpers\Helper;

class NotificationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $notificationsCount = Notifications::count();
        if ($notificationsCount < count($this->source())) {
            Notifications::insert(array_slice($this->source(), $notificationsCount));
        }
    }

    private function source()
    {
        return [
            [
                'customer_id' => 1,
                'type' => 'new_ticket',
                'ticket_id' => 1,
                'comment_id' => 1
            ],
            [
                'customer_id' => 1,
                'type' => 'new_ticket',
                'ticket_id' => 2,
                'comment_id' => 2
            ],
            [
                'customer_id' => 1,
                'type' => 'new_ticket',
                'ticket_id' => 3,
                'comment_id' => 3
            ],
            [
                'customer_id' => 1,
                'type' => 'new_ticket',
                'ticket_id' => 4,
                'comment_id' => 4
            ],
            [
                'customer_id' => 1,
                'type' => 'new_ticket',
                'ticket_id' => 5,
                'comment_id' => 5
            ],
            [
                'customer_id' => 1,
                'type' => 'new_ticket',
                'ticket_id' => 6,
                'comment_id' => 6
            ]
        ];
    }
}
