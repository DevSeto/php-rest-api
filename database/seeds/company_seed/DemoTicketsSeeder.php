<?php

use Illuminate\Database\Seeder;
use App\Models\Tickets;
use Illuminate\Support\Facades\DB;
use App\Helpers\Helper;
use App\Models\TicketComments;

class DemoTicketsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ticketsCount = Tickets::count();
        if ($ticketsCount < count($this->source())) {
            foreach ($this->source() as $item) {
                $ticket = Tickets::create($item);
                TicketComments::create([
                    'ticket_id' => $ticket->id,
                    'from_name' => $ticket->customer_name,
                    'from_email' => $ticket->customer_email,
                    'body' => $ticket->body
                ]);
            }
        }
    }

    private function source()
    {
        $firstUser = Helper::$user;

        return [
            [
                'owner_id' => 0,
                'mailbox_id' => 1,
                'customer_name' => 'Alex Turnbull',
                'customer_email' => 'hello@' . env('PAGE_URL_DOMAIN'),
                'customer_id' => 1,
                'subject' => 'Start Here: Welcome to Birddesk!',
                'body' => "Hi " . $firstUser['first_name'] . "<br>,
                                        I really appreciate you taking the time to try Birddesk.
                                        <br>I know you can grow your business by delivering awesome, 
                                        personal support to every single one of your customers. 
                                        And it’s my goal to help you to do just that!
                                        <br>Learning a new app can be a lot of work, but not with Birddesk. 
                                        We’ve gone ahead and setup a demo inbox for you. 
                                        And you’ve already got five tickets waiting for you from Lesley, our Head of Customer Success.
                                        <br>She’ll show you how things work around here :)
                                        <br>Thanks again,<br>Alex<br>CEO, Birddesk",
                'message_id' => '',
                'ticket_id_hash' => md5(random_int(1, 9999999999999999)),
                'all_email_data' => '',
                'assign_agent_id' => 0,
                'status' => 'open', // (enum)
                'merged' => 0,
                'is_demo' => 1,
                'color' => Helper::getRandomColor()
            ],
            [
                'owner_id' => 0,
                'mailbox_id' => 1,
                'customer_name' => 'Lesley Yarbrough',
                'customer_email' => 'hello@' . env('PAGE_URL_DOMAIN'),
                'customer_id' => 1,
                'subject' => 'Lesley here - Reply to me :-)',
                'body' => "Hey " . $firstUser['first_name'] . "
                                        <br>I’m Lesley, and it’s my job to help you deliver awesome, personal support to your customers. 
                                        I’ll be here with you every step of the way. 
                                        But Birddesk really is as simple as email and there are only a few basics to learn.
                                        <br>Let’s kick the tires a bit, shall we?
                                        <br>Go ahead and type a message below and hit “Send as Closed” to send me a reply. 
                                        That will send your reply to me and close this ticket.
                                        <br>When I respond, this ticket will automatically reopen and appear at the top of your inbox. 
                                        Be on the lookout for a reply from me (a real, live human).
                                        <br>Thanks!<br>Lesley",
                'message_id' => '',
                'ticket_id_hash' => md5(random_int(1, 9999999999999999)),
                'all_email_data' => '',
                'assign_agent_id' => 0,
                'status' => 'open', // (enum)
                'merged' => 0,
                'is_demo' => 1,
                'color' => Helper::getRandomColor()
            ],
            [
                'owner_id' => 0,
                'mailbox_id' => 1,
                'customer_name' => 'Lesley Yarbrough',
                'customer_email' => 'hello@' . env('PAGE_URL_DOMAIN'),
                'customer_id' => 1,
                'subject' => 'Shhhh. Wanna share a private note?',
                'body' => "Sometimes when you’re working on a ticket, you might want to add a note that your customers can’t see.
                                        And we have the answer:
                                        <br>Just click Add Note below and start typing. Your customers will never see it; 
                                        it’ll be your little secret.
                                        <br>Give it a try!,<br>Lesley",
                'message_id' => '',
                'ticket_id_hash' => md5(random_int(1, 9999999999999999)),
                'all_email_data' => '',
                'assign_agent_id' => 0,
                'status' => 'open', // (enum)
                'merged' => 0,
                'is_demo' => 1,
                'color' => Helper::getRandomColor()
            ],
            [
                'owner_id' => 0,
                'mailbox_id' => 1,
                'customer_name' => 'Lesley Yarbrough',
                'customer_email' => 'hello@' . env('PAGE_URL_DOMAIN'),
                'customer_id' => 1,
                'subject' => 'Me again :)',
                'body' => "Your customers should never feel like a number, and that’s why we want Birddesk to be the best option for personal support. Your customers will never have to login to your “support portal” or see that they are #348733030. No they just think you responded to their email from Gmail, and that’s how it should be.<br>To see what we mean, you’ll need to play the role of customer and support agent. Click the button below to send an email to your new Birddesk inbox from your default email client:<br>Your email will show up at the top of your Birddesk inbox and be marked as unread. Open it and have a conversation with yourself. We won’t tell anyone!<br>When you’re done, you’re ready to move on to the final step :)<br>Lesley",
                'message_id' => '',
                'ticket_id_hash' => md5(random_int(1, 9999999999999999)),
                'all_email_data' => '',
                'assign_agent_id' => 0,
                'status' => 'open', // (enum)
                'merged' => 0,
                'is_demo' => 1,
                'color' => Helper::getRandomColor()
            ],
            [
                'owner_id' => 0,
                'mailbox_id' => 1,
                'customer_name' => 'Lesley Yarbrough',
                'customer_email' => 'hello@' . env('PAGE_URL_DOMAIN'),
                'customer_id' => 1,
                'subject' => 'Want a taste of our Apps Menu?',
                'body' => "We like to keep things pretty simple at Birddesk, but when you’re ready to expand your Birddesk Universe beyond basic ticketing, head up to our Apps menu.<br>In Apps, you’ll be able to download and install any add-ons specific to your support needs, all included with Birddesk. Try installing the Knowledge Base, one of our most popular apps. Click on the Knowledge Base App and press Install.<br>ADD A KNOWLEDGE BASE<br>Once Installed, an Articles tab will appear in your top menu bar. In Articles, you’ll be able to create your first Knowledge Base entry and get started helping your customers help themselves. You can consult the Knowledge Base category for tips and tricks on getting started with your own.<br>When you’re done checking out our Apps, you’re ready to move on to the final step :)<br>Cheers, <br>Lesley",
                'message_id' => '',
                'ticket_id_hash' => md5(random_int(1, 9999999999999999)),
                'all_email_data' => '',
                'assign_agent_id' => 0,
                'status' => 'open', // (enum)
                'merged' => 0,
                'is_demo' => 1,
                'color' => Helper::getRandomColor()
            ],
            [
                'owner_id' => 0,
                'mailbox_id' => 1,
                'customer_name' => 'Lesley Yarbrough',
                'customer_email' => 'hello@' . env('PAGE_URL_DOMAIN'),
                'customer_id' => 1,
                'subject' => 'Invite your peeps',
                'body' => "Woohoo! You're now a certified Birddesk master. It’s time to share your gift with the world. Or at least with your team.<br>After you invite the rest your team, you’ll always be on the same page. You’ll see exactly who is working on which ticket and private notes make it easy to collaborate on the hard cases.<br>Now you’re ready to use Birddesk for real at anytime! Just follow the instructions at the bottom of your inbox to start forwarding your emails to Birddesk.<br>And if you need some help, I’m almost always online :)<br>Thanks again,<br>Lesley,<br>p.s. Be sure to checkout our Better Blog to see what we’ve been working on lately. And every Tuesday, I publish another post on delivering great, personal support on our Support Blog<br>p.p.s. Really, I love to help, so don't hesitate to email me at hello@Birddeskhq.com",
                'message_id' => '',
                'ticket_id_hash' => md5(random_int(1, 9999999999999999)),
                'all_email_data' => '',
                'assign_agent_id' => 0,
                'status' => 'open', // (enum)
                'merged' => 0,
                'is_demo' => 1,
                'color' => Helper::getRandomColor()
            ]
        ];
    }
}
