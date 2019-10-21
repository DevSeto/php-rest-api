<?php
// new_ticket
//* assigned_to_me
//* assigned_to_else
//* mentioned_in_ticket
//
//* replies_to_unassigned
//* replies_to_my_ticket
//
//* user_replies_or_note_to_unassigned
//* user_replies_or_note_to_my_ticket
return [
    'new_ticket' => ['condition_id' => 1, 'type' => 'ticket'],
    'assigned_to_me' => ['condition_id' => 2, 'type' => 'assign_to_me'],
    'assigned_to_else' => ['condition_id' => 3, 'type' => 'assign_to_someone_else'],
    'mentioned_in_ticket' => ['condition_id' => 4, 'type' => 'mentioned_in_ticket'],
//    'mentioned_in_team_ticket' => ['condition_id' => 5],

    'replies_to_unassigned' => ['condition_id' => 6, 'type' => 'customer_replies_to_unassigned_ticket'],
    'replies_to_my_ticket' => ['condition_id' => 7, 'type' => 'customer_replies_to_user_ticket'],
//    'replies_to_team_ticket' => ['condition_id' => 8],


    'user_replies_or_note_to_unassigned' => ['condition_id' => 9, 'type' => 'user_replies_or_note_to_unassigned_ticket'],
    'user_replies_or_note_to_me' => ['condition_id' => 10, 'type' => 'user_replies_or_note_to_my_ticket']
];