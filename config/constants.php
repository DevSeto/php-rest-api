<?php


return [
    'roles' => [
        'account_owner' => 2,
        'admin' => 3,
        'agent' => 4
    ],

    'mailbox_available_hours' => [
        'Monday' => '{"from":"Sun Dec 31 1899 9:0:0","to":"Sun Dec 31 1899 17:0:0","enable":0}',
        'Tuesday' => '{"from":"Sun Dec 31 1899 9:0:0","to":"Sun Dec 31 1899 17:0:0","enable":0}',
        'Wednesday' => '{"from":"Sun Dec 31 1899 9:0:0","to":"Sun Dec 31 1899 17:0:0","enable":0}',
        'Thursday' => '{"from":"Sun Dec 31 1899 9:0:0","to":"Sun Dec 31 1899 17:0:0","enable":0}',
        'Friday' => '{"from":"Sun Dec 31 1899 9:0:0","to":"Sun Dec 31 1899 17:0:0","enable":0}',
        'Saturday' => '{"from":"Sun Dec 31 1899 9:0:0","to":"Sun Dec 31 1899 17:0:0","enable":0}',
        'Sunday' => '{"from":"Sun Dec 31 1899 9:0:0","to":"Sun Dec 31 1899 17:0:0","enable":0}',
    ],

    'ticket_history' => [
        'create' => "<span class='author_tag'> {author} </span> created ticked",
        'comment' => "<span class='author_tag'> {author} </span> replied",
        'assign' => "<span class='author_tag'> {author} </span> changed ticket assign to <span class='author_tag'> {assignUser} </span>",
        'status' => "<span class='author_tag'> {author} </span> changed status to {toStatus}",
        'status_from_email' => "Changed status to {toStatus} because of customer reply",
        'merge' => "<span class='author_tag'> {author} </span> merged ticket with {mergeTickets} tickets",
        'note' => "<span class='author_tag'> {author} </span> added a note",
    ],

    'notification_messages' => [
        'create' => "<span class='author_tag'> {author} </span> created ticked",
        'assign' => "<span class='author_tag'> {author} </span> changed ticket assign to <span class='author_tag'> {assignUser} </span>",
        'comment' => "<span class='author_tag'> {author} </span> replied",
        'note' => "<span class='author_tag'> {author} </span> added a note",
        'mention' => "<span class='author_tag'> {author} </span> mentioned <span class='author_tag'> {mentionedTo} </span> in note"
    ],

    'notifications' => [
        // events => id

        // notify me when ...
        'new_ticket' => ['id' => 1],
        'assigned_to_me' => ['id' => 2],
        'assigned_to_someone_else' => ['id' => 3],
        'mentioned_in_ticket' => ['id' => 4],
        'team_mentioned_in_ticket' => ['id' => 5],

        // notify me when a customer replies ...
        'customer_replies_to_unassigned_ticket' => ['id' => 6],
        'customer_replies_to_my_ticket' => ['id' => 7],
        'customer_replies_to_team_assigned_ticket' => ['id' => 8],

        // notify me when another user replies or adds a note ...
        'user_replies_to_unassigned_ticket' => ['id' => 9],
        'user_add_note_to_unassigned_ticket' => ['id' => 9],
        'user_replies_to_my_ticket' => ['id' => 10],
        'user_add_note_to_my_ticket' => ['id' => 10]
    ]
];