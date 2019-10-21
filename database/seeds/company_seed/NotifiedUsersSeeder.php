<?php

use Illuminate\Database\Seeder;
use App\Models\NotifiedUsers;

class NotifiedUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $notifiedUsersCount = NotifiedUsers::count();
        if ($notifiedUsersCount < count($this->source())) {
            NotifiedUsers::insert(array_slice($this->source(), $notifiedUsersCount));
        }
    }

    private function source()
    {
        return [
            [
                'notification_id' => 1,
                'user_id' => 1,
                'is_viewed' => 0
            ],
            [
                'notification_id' => 2,
                'user_id' => 1,
                'is_viewed' => 0
            ],
            [
                'notification_id' => 3,
                'user_id' => 1,
                'is_viewed' => 0
            ],
            [
                'notification_id' => 4,
                'user_id' => 1,
                'is_viewed' => 0
            ],
            [
                'notification_id' => 5,
                'user_id' => 1,
                'is_viewed' => 0
            ],
            [
                'notification_id' => 6,
                'user_id' => 1,
                'is_viewed' => 0
            ]
        ];
    }
}
