<?php

namespace App\Helpers;

use App\Helpers\Helper;
use  App\Http\Controllers\Tickets\NoteController;
use App\Http\Requests\CreateCommentRequest;
use App\Http\Requests\CreateNoteRequest;
use App\Models\Mailbox;
use App\Models\SparkpostSubAccounts;
use App\Models\TicketComments;
use App\Models\Tickets;
use App\Models\UserApiTokens;
use App\Services\NoteService;
use App\Services\NotificationsService;
use App\Services\TicketCommentsService;
//use App\Services\UserNotificationsService;
use DB;
use GuzzleHttp;
use Exception;
use App\Models\MailboxAvailableHours;
use App\Models\CompanySetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\TicketFiles;
use DOMDocument;
use PhpMimeMailParser as Parser;
use DOMXPath;
use DOMNode;
use App\Models\Subdomains;
use App\Models\User;

class IncomingEmailService
{

    public function notForProcessingEmail($data)
    {
        if (empty($data['email'])) {
            return true;
        }

        $to = $data[0]['msys']['relay_message']['rcpt_to'];
        if ($this->fromSparkpost($data[0]['msys']['relay_message']['friendly_from']) || $this->isNoReply($to)) {
            return true;
        }

        return false;
    }

    /**
     * checks if key isset in multidimensional array
     * @param $key
     * @param $array
     * @return mixed
     *
     */
    public function searchInMultidimensionalArray($key, $array)
    {
        foreach ($array as $val) {
            if (isset($val[$key])) {
                return $val;
            }
        }
    }

    /**
     * filter gmail email and get body
     * @param $html
     * @return string
     */
    public static function getMessageGmail($html)
    {
        $dom = new DOMDocument();
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

        $xpath = new DOMXpath($dom);
        foreach ($xpath->query('//div[contains(attribute::class, "gmail_quote")]') as $e) {
            // Delete this node
            $e->parentNode->removeChild($e);
        }
        $result = $xpath->query('//div[@dir="ltr"]');
        if ($result->length > 0) {
            $result = $dom->saveHTML($result->item(0));
        } else {
            $result = $xpath->query('//div[@dir="auto"]');
            if ($result->length > 0) {
                $result = $dom->saveHTML($result->item(0));
            }
        }

        return $result;
    }

    /**
     * filter outlook and get body
     * @param $html
     * @return string
     */
    public static function getMessageOutlook($html)
    {
        $html = str_replace("\r\n", " ", $html);
        $doc = new DOMDocument();
        @$doc->loadHTML($html);
        $element = $doc->getElementById('divtagdefaultwrapper');

        $new_doc = new DOMDocument();
        @$new_doc->loadHTML(self::DOMinnerHTML($element));

        $selector = new DOMXPath($new_doc);
        foreach ($selector->query('//div[contains(attribute::id, "Signature")]') as $e) {
            $e->parentNode->removeChild($e);
        }
        return strip_tags(trim(preg_replace('/\s+/', ' ', $new_doc->saveHTML($new_doc->documentElement))));
    }

    /**
     * filter yahoo and get body
     * @param $html
     * @return string
     */
    public static function getMessageYahoo($html)
    {
        $doc = new DOMDocument();
        @$doc->loadHTML($html);
        $selector = new DOMXPath($doc);

        foreach ($selector->query('//div[contains(attribute::class, "yahoo_quoted")]') as $e) {
            $e->parentNode->removeChild($e);
        }
        return strip_tags(trim(preg_replace('/\s+/', ' ', $doc->saveHTML($doc->documentElement))));
    }


    public static function DOMinnerHTML(DOMNode $element)
    {
        $innerHTML = "";
        $children = $element->childNodes;

        foreach ($children as $child) {
            $innerHTML .= $element->ownerDocument->saveHTML($child);
        }
        return $innerHTML;
    }

    /**
     * checking if email is forwarded
     * @param $url
     * @return bool
     */
    public function isForwarded($url)
    {
        $pattern = '/^forward_/';
        preg_match($pattern, substr($url, 0), $matches, PREG_OFFSET_CAPTURE);
        return !empty($matches) ? true : false;
    }

    /**
     * get mailbox id by decrypting from url hash
     * @param $url
     * @return string
     */
    public function getMailboxId($url)
    {
        return explode('@', explode('_', $url)[2])[0];
    }

    public function getPrettyBody($data, $customer)
    {
        $email_service = explode('.', explode('@', $customer['email'])[1])[0];
        $reg_exUrl = "/ (http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)? /";
        if (preg_match($reg_exUrl, $data, $url)) {
            // make the urls hyper links
            $email['body'] = preg_replace($reg_exUrl, "<a href=" . $url[0] . " target='_blank'>$url[0]</a> ", $data);
        }

        $comment['body'] = str_replace(chr(194), " ", $data);
        //filter incoming email bodies
        switch ($email_service) {
            case 'gmail':
                $email['body'] = IncomingEmailService::getMessageGmail($comment['body']);
                break;
            case 'yahoo':
                $email['body'] = IncomingEmailService::getMessageYahoo($comment['body']);
                break;
            case 'outlook':
                $email['body'] = IncomingEmailService::getMessageOutlook($comment['body']);
                break;
            default :
                $comment['body'] = strip_tags($comment['body'], "<style>,<a>");
                $substring = substr($comment['body'], strpos($comment['body'], "<style"), strpos($comment['body'], "</style>"));
                $comment['body'] = str_replace($substring, "", $comment['body']);
                $comment['body'] = str_replace(array("\t", "\r", "\n"), "", $comment['body']);
                $comment['body'] = trim($comment['body']);
                $email['body'] = $comment['body'];
                break;
        }

        return $email['body'];
    }

    /**
     * parsing email by php mail parser class
     * @param $text
     * @return Parser\Parser
     */
    public static function parseMail($text)
    {
        $parser = new Parser\Parser();
        $parser->setText($text);
        return $parser;
    }


    /**
     * checking registration step
     * @param $subdomain
     */
    public static function checkStep($subdomain)
    {
        if (json_decode(User::where('id', 1)->first()->step)->step == 1) {
            User::where('id', 1)->update([
                'step' => json_encode([
                    'step' => 2
                ])
            ]);

            $first_email['userRoom'] = $subdomain;
            $first_email['users'] = [1];
            $first_email['type'] = 'step_first_email';

            try {
                Helper::sendNotification($first_email);
            } catch (Exception $e) {
            }
        }
    }

    /**
     * saving file properties to our company db
     * @param $properties
     */
    public static function saveFileProperties($properties)
    {
        $ticketFilesData = [];

        foreach ($properties as $property) {
            $file = TicketFiles::create([
                "file_name" => $property['fileName'],
                "file_full_path" => $property['full_path'],
                "file_type" => $property['extension'],
                "main_type" => $property['type'],
                "disposition" => $property['disposition'],
                "ticket_id" => $property['ticket_id'],
                "comment_id" => $property['comment_id'],
                "cid" => $property['cid']
            ]);
            array_push($ticketFilesData, $file);
        }
        return $ticketFilesData;
    }

    /**
     * check if email from sparkpost
     */
    public function fromSparkpost($from)
    {
        return (explode('@', $from)[1] == 'sparkpost.com') ? true : false;
    }

    /**
     * check if auto reply is on
     * @param $mailbox_data
     * @return bool
     */
    public function checkAutoReply($mailbox_data)
    {
        $on = true;
        //if 0 than turned off
        //if 1 turned on every time
        //if 2 than turned on only in available hours

        if ($mailbox_data->auto_reply == 3) {
            $on = false;
        } elseif ($mailbox_data->auto_reply == 2) {
            $mailbox_availabal_hours = MailboxAvailableHours::where('mailbox_id', $mailbox_data->id)->first()->toArray();
            $week_day = Carbon::now()->format('l');
            $week_day_hours = json_decode($mailbox_availabal_hours[$week_day], true);
            if ($week_day_hours['enable'] == 0) {
                $on = false;
            } else {
                $timezone_offset = CompanySetting::first()->timezone_offset;
                if (date('H i', strtotime($week_day_hours['from'])) <= Carbon::now()->addHours($timezone_offset)->format('H i')
                    && Carbon::now()->addHours($timezone_offset)->format('H i') <= date('H i', strtotime($week_day_hours['to']))
                ) {
                    $on = true;
                } else {
                    $on = false;
                }
            }
        }
        return $on;
    }

    public function getSubDomainByWebhookId($webhookId)
    {
        $subdomain = Subdomains::where('webhook_id', $webhookId)->first();
        if (!empty($subdomain)) {
            return $subdomain->company_url;
        }
        return false;
    }

    public function handleAutoReply($mailboxData, $customer, $message_id)
    {
        $agentId = $mailboxData['creator_user_id'];
        $autoReplyBody = $mailboxData['auto_reply_body'];
        $agent = User::find($agentId);
        $companyData = SparkpostSubAccounts::first();

        // to change message variables to origin data
        $variables = [ // Variables

            //  Agent variables
            'agent_first_name' => ['{%agent.first_name%}', $agent->first_name],
            'agent_last_name' => ['{%agent.last_name%}', $agent->last_name],
            'agent_full_name' => ['{%agent.full_name%}', $agent->first_name . " " . $agent->last_name],
            'agent_email' => ['{%agent.email%}', $agent->email],

            //  Mailbox variables
            'mailbox_name' => ['{%mailbox.name%}', $mailboxData['name']],
            'mailbox_email' => ['{%mailbox.email%}', $mailboxData['email']],

            //  User variables
            'user_first_name' => ['{%user.first_name%}', $customer['first_name']],
            'user_last_name' => ['{%user.last_name%}', $customer['last_name']],
            'user_full_name' => ['{%user.full_name%}', $customer['first_name'] . ' ' . $customer['last_name']],

            //  Company variables
            'company_name' => ['{%company.name%}', $companyData->sub_account_name]
        ];

        foreach ($variables as $variable) {
            if (strpos($autoReplyBody, $variable[0]) === true) {
                $autoReplyBody = str_replace($variable[0], $variable[1], $autoReplyBody);
            }
        }

        // send auto reply
        $options = [
            'toEmail' => $customer['email'],
            'toName' => $customer['first_name'] . ' ' . $customer['last_name'],
            'fromEmail' => $mailboxData['email'],
            'fromName' => $mailboxData['name'],
            'token' => $companyData->key,
            'subject' => $mailboxData['auto_reply_subject'],
            'messageId' => $message_id,
            'commentText' => $autoReplyBody,
            'replyEmail' => "no-reply@" . Helper::getDomainNameFromEmail($mailboxData['email']),
            'reply_to' => $mailboxData['name']
        ];
        SparkPostApi::createTransmission($options);
    }


    /**
     * if email is from Agent(Admin)
     * @param $url
     * @return bool
     */

    public static function isFromAgent($url, $body, $subdomain, $email_commands = "")
    {
        $pattern = '/^35f90c33eaa7cbe39be15f40316c2ab6_/';
        preg_match($pattern, substr($url, 0), $matches, PREG_OFFSET_CAPTURE);
        if (!empty($matches)) {
            $ticket_id_hash = explode('_', explode('@', $url)[0])[1];
            $agent_id = explode('_', explode('@', $url)[0])[2];
            $last_comment_id = explode('_', explode('@', $url)[0])[3];
            $ticket_comments = Tickets::where('ticket_id_hash', $ticket_id_hash)->with('commentsLimited')->first()->toArray();

            if ($ticket_comments['comments_limited'][0]['id'] > $last_comment_id && $ticket_comments['comments_limited'][0]['author_id'] == $agent_id) {
                //to draft node redis
                $ticketForDraft = Tickets::where('ticket_id_hash', $ticket_id_hash)->with(['files', 'comments'])->first()->toArray();
                $ticketForDraft['isDraft'] = true;
                $ticketForDraft['ticketDraft'] = [
                    "reply" => strip_tags($body),
                    "forward" => "",
                    "customer_email" => "",
                    "subject" => "",
                    "body" => "",
                    "customer_name" => "",
                    "forwarding_emails" => [],
                    "comment_files" => [],
                    "forward_files" => [],
                    "note_files" => [],
                    "note" => "",
                    "created_at" => null,
                    "deleted_at" => null,
                    "ticket_id" => $ticketForDraft['id'],
                    "id" => null,
                    "updated_at" => "",
                    "subdomain" => $subdomain,
                    "forward_emails" => []
                ];

                $draft_object = [
                    "data" => $ticketForDraft,
                    "key" => "draft:" . $subdomain . ":" . $agent_id . ":" . $ticket_comments['mailbox_id']
                ];


                //need to send to node
                $client = new GuzzleHttp\Client();

                $client->post('https://birdtest.nl:3000/ticket_draft',
                    [
                        'headers' => ['content-type' => 'application/json', 'Accept: application/json'],
                        'body' => json_encode($draft_object)
                    ]);
                self::sayAgentAboutDraft($agent_id, $ticketForDraft);
            } else {
                //create comment
                $user_api_token = UserApiTokens::where('user_id', $agent_id)->where('expires_at', '>', date("Y-m-d h:i:s", time()))->first();
                if (empty($user_api_token)) {
                    $user_api_token = Helper::generateUserApiToken();
                    $expires_at = Carbon::now()->addHours(24)->toDateTimeString();
                    $create = UserApiTokens::create([
                        'user_id' => $agent_id,
                        'api_token' => $user_api_token,
                        'expires_at' => $expires_at
                    ]);
                    $user_api_token = $create->api_token;
                } else {
                    $user_api_token = $user_api_token->api_token;
                }

                $user = User::where('id', $agent_id)->first();
                Helper::setUser($user->toArray());

                if (!empty($email_commands)){
                    EmailCommandsService::toRealizeCommands($subdomain,$ticket_id_hash,$email_commands,$user_api_token);
                }

                if (!empty($body)) {
                    $req_obj = new CreateCommentRequest([], [], [], [], [], [], json_encode(['ticket_id_hash' => $ticket_id_hash, 'body' => $body]));
                    $req_obj->headers->add([
                        'Authorization' => $user_api_token,
                        "Content-Type" => "application/json"
                    ]);

                    $_SERVER['HTTP_ORIGIN'] = "https://" . Helper::$subDomain . env("PAGE_URL");
                    $tco = new TicketCommentsService();
                    $comments_obj = new \App\Http\Controllers\Tickets\TicketComments($tco);
                    $comments_obj->create($req_obj);
                }
            }
        }
        return !empty($matches) ? explode('_', explode('@', $url)[0])[2] : false;
    }

    /**
     * if is mentioned reply
     * @param $url
     * @return array|bool
     */

    public static function isMention($url)
    {
        $pattern = '/^ac5362cd5e10431cd60b04720b75450b_/';
        preg_match($pattern, substr($url, 0), $matches, PREG_OFFSET_CAPTURE);
        return !empty($matches) ? explode('_', explode('@', $url)[0]) : false;
    }

    /**
     * check if email is forwarded
     * @param $data
     * @return bool
     */
    public function isForwardCheck($from,$subject)
    {
        if (explode('@', $from)[0] == 'check_forward') {
            Helper::changeDataBaseConnection(Helper::$subDomain);
            $mailbox_id = explode('_', $subject)[1];
            $user_id = explode('_', $subject)[2];
            Mailbox::where('id', $mailbox_id)->update(['forwarding_verified' => '1']);
            //send push notification to user

            $notification_data['userRoom'] = Helper::$subDomain;
            $notification_data['user_id'] = $user_id;
            $notification_data['mailbox_id'] = $mailbox_id;

            try {
                Helper::sendNotification($notification_data, 'checkForwarding');
            } catch (Exception $e) {
            }
            return true;
        }
        return false;
    }

    /**
     * add to netes if replying to Add Note email notification
     * @param $mention
     * @param $subdomain
     * @param $body
     */

    public function addNote($mention, $subdomain, $body)
    {
        Helper::changeDataBaseConnection($subdomain);
        if ($ticket = Tickets::where('ticket_id_hash', $mention[1])->first()) {
            $user_api_token = UserApiTokens::where('user_id', $mention[2])->where('expires_at', '>', date("Y-m-d h:i:s", time()))->first();
            if (empty($user_api_token)) {
                $user_api_token = Helper::generateUserApiToken();
                $expires_at = Carbon::now()->addHours(24)->toDateTimeString();
                $create = UserApiTokens::create([
                    'user_id' => $mention[2],
                    'api_token' => $user_api_token,
                    'expires_at' => $expires_at
                ]);
                $user_api_token = $create->api_token;
            } else {
                $user_api_token = $user_api_token->api_token;
            }

            $user = User::where('id', $mention[2])->first();
            Helper::setUser($user->toArray());

            $req_obj = new CreateNoteRequest([], [], [], [], [], [], json_encode(['ticket_id' => $ticket['id'], 'author_id' => $mention[2], 'note' => $body]));
            $req_obj->headers->add([
                'Authorization' => $user_api_token,
                "Content-Type" => "application/json"
            ]);
            $_SERVER['HTTP_ORIGIN'] = "https://" . $subdomain . env("PAGE_URL");
            $noteServiceObj = new NoteService();
            $userNotificationServiceObj = new NotificationsService();
            $note_obj = new NoteController($noteServiceObj, $userNotificationServiceObj);
            $note_obj->create($req_obj, $ticket['ticket_id_hash']);
        }
    }


    /**
     * send notification to agent that his comment saved as draft
     * @param $agent_id
     * @param $ticket
     */

    public static function sayAgentAboutDraft($agent_id, $ticket)
    {
        $agent = User::where('id', $agent_id)->first();
        $mailbox = Mailbox::where('id', $ticket['mailbox_id'])->first();
        $options = [
            'toEmail' => $agent['email'],
            'toName' => $agent['first_name'] . ' ' . $agent['last_name'],
            'fromEmail' => $mailbox['email'],
            'fromName' => $mailbox['name'],
            'subject' => 'Notification for ticket #' . $ticket['id'],
            'messageId' => '<' . md5(rand(999, 9999999)) . '@mail' . env('PAGE_URL') . '>',
            'commentText' => "Your comment saved as draft" . "
                                </br>
                                <a href='https://" . Helper::$subDomain . env('PAGE_URL') . "/ticket/'" . $ticket['id'] . " style='text-decoration: none'>
                                    <button type='submit' style='
                                                                    padding: 12px 38px;
                                                                    border-radius: 15px; 
                                                                    border: none; 
                                                                    background-color: #4dd0e1; 
                                                                    color: white; 
                                                                    margin: 0 auto; 
                                                                    display: block;
                                                                    text-transform: uppercase;'
                                                                    >
                                                                    See ticket
                                    </button>
                                </a>",
            'sub_domain' => Helper::$subDomain,
            'reply_to' => "no-reply@" . Helper::$subDomain . env('PAGE_URL')
        ];

        SendEmailService::sendEmail($options);
    }

    /**
     * check if email is marked as no reply
     * @param $url
     * @return bool
     */

    public function isNoReply($url)
    {
        return (explode('@', $url)[0] == 'no-reply') ? true : false;
    }
}