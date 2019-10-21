<?php

use Illuminate\Database\Seeder;
use App\Models\NotificationsList;


class NotificationsListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $notificationsCount = NotificationsList::count();
        if ($notificationsCount < count($this->source())) {
            NotificationsList::insert(array_slice($this->source(), $notificationsCount));
        }
    }

    private function source()
    {
        return [

            // Notify me when
            [
                "condition" => "new",
                "reply" => ""
            ],
            [
                "condition" => "assigned_to_me",
                "reply" => ""
            ],
            [
                "condition" => "assigned_to_someone",
                "reply" => ""
            ],
            [
                "condition" => "mentioned",
                "reply" => ""
            ],

            // Notify me when a customer replies
            [
                "condition" => "unassigned_ticket",
                "reply" => "customer"
            ],
            [
                "condition" => "my_ticket",
                "reply" => "customer"
            ],

            // Notify me then another user replies or adds a note
            [
                "condition" => "unassigned_ticket",
                "reply" => "another_user"
            ],
            [
                "condition" => "my_ticket",
                "reply" => "another_user"
            ]
        ];
    }
}
