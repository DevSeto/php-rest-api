<?php

namespace App\Helpers;

use App\Models\Drafts;
use App\Models\MailboxUserPermissions;
use App\Models\ResetPasswords;
use App\Models\Subdomains;
use App\Models\Tickets;
use App\Models\TicketsHistory;
use App\Models\UserApiTokens;
use Illuminate\Support\Facades\Request;
use App\Models\User;
use DB;
use Response;
use Lang;
use Route;
use Illuminate\Support\Facades\Config;
use GuzzleHttp;
use Exception;
use Validator;
use A6digital\Image\DefaultProfileImage;

use Carbon\Carbon;

class Helper
{
    public $userId;
    public static $user;

    /**
     * @param mixed $user
     */
    public static function setUser($user)
    {
        self::$user = $user;
    }

    public static $subDomain;

    /**
     * @return int
     */
    public static function getDemoTicketsCount()
    {
        return Tickets::where('is_demo', 1)->count();
    }

    public static function getRandomColor()
    {
        $colors = config('colors');
        $randomIndex = random_int(1, count($colors) - 1);
        $color = $colors[$randomIndex];
        return $color;
    }

    /**
     * Get ticket color
     *
     * @param int $ticketId
     *
     * @return string
     */
    public static function getTicketColor($ticketId)
    {
        return (!empty(Tickets::find($ticketId))) ? Tickets::find($ticketId)->color : "";
    }

    /**
     * Get current subDomain
     *
     * @param string $header
     *
     * @return string $subDomain
     */
    public static function getHeaders($header = null)
    {
        return !empty($header) ? getallheaders()[$header] : getallheaders();
    }

    /**
     * Update table by different params
     *
     * @param string $table
     * @param array $values
     * @param string $by
     * @param string $changedValue
     *
     * @return void
     */
    public static function updateValues($table, $values, $by, $changedValue)
    {
        // $values = [1 => 'open', 2 => 'spam', 3 => 'open'];
        /*
                $values = [
                    value($by) => value($changedValue)
                ];
        */
        $cases = [];
        $ids = [];
        $params = [];

        foreach ($values as $id => $value) {
            $id = (int)$id;
            $cases[] = "WHEN {$id} then ?";
            $params[] = $value;
            $ids[] = $id;
        }

        $ids = implode(',', $ids);
        $cases = implode(' ', $cases);
        $params[] = Carbon::now();

//        DB::update("UPDATE `notifications_datas`
//                            SET `type` = CASE `id` {$cases} END,
//                            `updated_at` = ? WHERE `id` in ({$ids})",
//            $params);
        \DB::update("UPDATE `{$table}` 
                            SET `{$changedValue}` = CASE `{$by}` {$cases} END,
                            `updated_at` = ? WHERE `{$by}` in ({$ids})",
            $params);
    }

    /**
     * Insert data to DB table
     *
     * @param string $table
     * @param array $data
     *
     * @return void
     */
    public static function insertTo($table, $data)
    {
        DB::table($table)->insert($data);
    }

    /**
     * To Add event the company history
     *
     * @param string $historyMessage
     * @param string $individualHistory
     * @param integer $ticketId
     * @param integer $authorId
     * @param integer $mailbox_id
     * @param string $subdomain
     * @param $merge_data
     *
     * @return void
     */
    public static function addToTicketHistory($historyMessage, $individualHistory, $authorId, $ticketId, $mailbox_id, $subdomain = null, $merge_data = null)
    {
        // if is demo ticket
        $demoTicketsCount = self::getDemoTicketsCount();
        if ($ticketId < $demoTicketsCount) {
            return;
        }

        $addHistory = TicketsHistory::create([
            'history' => $historyMessage,
            'individual' => $individualHistory,
            'author_id' => $authorId,
            'ticket_id' => $ticketId,
            'mailbox_id' => $mailbox_id
        ]);

        $addToHistory = TicketsHistory::where('id', $addHistory->id)->with(['author', 'customer', 'ticket'])->first();

        $addToHistory->userRoom = (!empty($subdomain)) ? $subdomain : self::getSubDomain();
        $addToHistory->merge_data = (!empty($merge_data)) ? $merge_data : [];
        $addToHistory->users = array_values(MailboxUserPermissions::where('mailbox_id', $mailbox_id)->pluck('user_id')->toArray());
        $addToHistory->type = 'ticket_history';
        $addToHistory->author = User::find($authorId);

        try {
            self::sendNotification($addToHistory->toArray());
        } catch (Exception $e) {
        }

    }

    /**
     * generate random message id
     * @return string
     */

    public static function generateMessageID()
    {
        return sprintf(
            "<%s.%s@%s>",
            base_convert(microtime(), 10, 36),
            base_convert(bin2hex(openssl_random_pseudo_bytes(8)), 16, 36),
            $_SERVER['SERVER_NAME']
        );
    }


    /**
     * Get user data by header Authorization token
     *
     * @param string $apiToken
     *
     * @return array $user
     */
    public static function getUser($apiToken)
    {
        //get user data
        $api_token = UserApiTokens::where('api_token', $apiToken)->first();
        $user = User::where('id', $api_token->user_id)->first();
        self::$user = $user;

        return !empty($user) ? $user->toArray() : [];
    }

    /**
     * Get current subDomain
     *
     * @return string $subDomain
     */
    public static function setSubDomain()
    {
        if (env("APP_ENV") == "production") {
            $subDomain = explode('.', explode('//', Request::capture()->server("HTTP_ORIGIN"))[1])[0]; // live
        } elseif (env("APP_ENV") == "local") {
            $subDomain = explode('.', Request::capture()->server("HTTP_HOST"))[0];  // local
        } else {
            $subDomain = explode('.', explode('//', Request::capture()->server("HTTP_ORIGIN"))[1])[0]; // live
        }
        self::$subDomain = $subDomain;
        return $subDomain;
    }

    /**
     * return current subDomain
     * @return mixed
     */

    public static function getSubDomain()
    {
        return self::$subDomain;
    }

    /**
     * Response message
     *
     * @param array $data
     * @param string $title
     *
     * @return array
     */
    public static function successResponse($data = null, $title = null)
    {
        return Response::make(json_encode([
            'success' => true,
            'title' => $title,
            'data' => $data
        ]), 200);
    }

    /**
     * Error message generator
     *
     * @param string $message
     *
     * @return string
     */
    public static function generateErrorMessage($message)
    {
        $result = Crypto::encrypt(json_encode(['message' => $message]));
        return $result;
    }

    /**
     * Get current subDomain
     *
     * @param string $subDomain
     *
     * @return object
     */
    public static function getCompanyData($subDomain)
    {
        return Subdomains::where('company_url', $subDomain)->first();
    }

    /**
     * Remove draft
     *
     * @param integer $draftId
     *
     * @return bool
     */
    public static function removeDraft($draftId)
    {
        $draft = Drafts::find($draftId);
        if (!empty($draftId)) {
            if (empty($draft->reply) && empty($draft->note) && empty($draft->forward)) {
                $draft->delete();
                return true;
            }
        }
        return false;
    }

    /**
     * generate api token
     * @return string
     */
    public static function generateUserApiToken()
    {
        $token = uniqid(str_random(45));
        if (UserApiTokens::where('api_token', $token)->first()) {
            self::generateUserApiToken();
        } else {
            return $token;
        }
    }

    /**
     * Generate token for reset password
     *
     * @return string
     */
    public static function generateResetPasswordToken()
    {
        $token = uniqid(str_random(45));
        $data = ResetPasswords::where('token', $token)->first();
        if (!empty($data->token)) {
            self::generateResetPasswordToken();
        } else {
            return $token;
        }
    }

    /**
     * creating new database
     * @param $db_name
     * @return bool|\mysqli_result
     */
    public static function createNewDataBase($db_name)
    {
        $sql = 'CREATE DATABASE ' . $db_name . ' DEFAULT CHARACTER SET utf8mb4 DEFAULT COLLATE utf8mb4_bin';
        $mysqli = new \mysqli(env('DB_HOST'), env('DB_USERNAME'), env('DB_PASSWORD'));
        $success = $mysqli->query($sql);
        return $success;
    }

    /**
     * changing database connection by db name
     * @param $account
     * @return mixed
     */
    public static function changeDataBaseConnection($account)
    {
        if (self::checkIfDatabaseExists($account)) {
            Config::set('database.connections.' . $account, [
                'driver' => env('DB_CONNECTION'),
                'host' => env('DB_HOST'),
                'database' => $account,
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_bin',
                'prefix' => ''
            ]);
            return DB::setDefaultConnection($account);
        } else {
            return Helper::send_error_response('company_url', Lang::get('auth.subdomain'), 422);
        }
    }

    /**checking if DB exists
     * @param $db_name
     * @return bool
     */
    public static function checkIfDatabaseExists($db_name)
    {
        $mysqli = new \mysqli(env('DB_HOST'), env('DB_USERNAME'), env('DB_PASSWORD'));
        return mysqli_select_db($mysqli, $db_name);
    }

    /**
     *
     * @param $key
     * @param $message
     * @param $error_code
     * @param string $title
     *
     * @return mixed
     */
    public static function send_error_response($key, $message, $error_code)
    {
        return Response::make([
            'success' => false,
            'errors' => [$key => $message]
        ], $error_code);
    }

    /*************************************     Variables     *********************************************/
    public static function useVariables()
    {
        $variables = [
            //  Agent variables
            'agent_first_name' => '{%agent.first_name%}',
            'agent_last_name' => '{%agent.last_name%}',
            'agent_full_name' => '{%agent.full_name%}',
            'agent_email' => '{%agent.email%}',

            //  Mailbox variables
            'mailbox_name' => '{%mailbox.name%}',
            'mailbox_email' => '{%mailbox.email%}',

            //  User variables
            'user_first_name' => '{%user.first_name%}',
            'user_last_name' => '{%user.last_name%}',
            'user_full_name' => '{%user.full_name%}',

            //  Company variables
            'company_name' => '{%company.name%}'
        ];

        return $variables;
    }


    /***************************************     * * *     ***********************************************/
    /**
     * checking if Sparkpost verified sending domain
     * @param $subdomain
     * @param $token
     * @return bool|mixed
     */
    public static function checkIfSendingDomainIsReady($subdomain, $token)
    {
        if (SparkPostApi::getSendingDomain($subdomain, $token, env('PAGE_URL'))['results']['status']['dkim_status'] == 'valid') {
            return true;
        }
        return Helper::send_error_response('sending_domain', Lang::get('mailbox.creating_domain'), 490);
    }

    /**
     * send push notification to node
     * @param $data
     * @param null $type
     */

    public static function sendNotification($data, $type = null)
    {
        $client = new GuzzleHttp\Client();
        $client->post(
            'https://' . env('PAGE_URL_DOMAIN') . ':3000/notification/' . $type,
            [
                'headers' => ['content-type' => 'application/json', 'Accept: application/json'],
                'body' => json_encode($data)
            ])->json();
    }

    /**
     * get subdomain from incoming email
     * @param $email
     * @return bool|string
     */

    public static function getDomainNameFromEmail($email)
    {
        return substr(strrchr($email, "@"), 1);
    }

    /**
     * generate user avatar
     * @param $userId
     * @param $subDomain
     * @return mixed
     */
    public static function generateUserProfileDefaultImage($userId, $subDomain)
    {
        $user = User::find($userId);
        $firstName = preg_replace('/[^\p{L}\p{N}\s]/u', '', $user->first_name);
        $lastName = preg_replace('/[^\p{L}\p{N}\s]/u', '', $user->last_name);
        $color = self::getRandomColor();

        $destinationPath = $subDomain . '/user/avatar';
        $avatarName = date('mdYHis') . uniqid();

        $img = DefaultProfileImage::create("$firstName $lastName", 256, $color);
        $uploadFile = \Storage::disk('users_images')->put("$destinationPath/$avatarName.png", $img->encode());
        $imageFullPath = "https://" . env('APP_PROD') . env('PAGE_URL') . "/uploads/$destinationPath/$avatarName.png";
        $user->update([
            "avatar_full_path" => "/uploads/$destinationPath/$avatarName.png",
            "avatar_url" => $imageFullPath,
            "avatar" => "$avatarName.png"
        ]);
        return $uploadFile;
    }

    /**
     * @param $user_id
     */

    public static function addUserDefaultNotifications($user_id)
    {
        DB::table('user_notifications')->insert([
            [
                'user_id' => $user_id,
                'condition_id' => 1,
                'email' => 0,
                'browser' => 1
            ],
            [
                'user_id' => $user_id,
                'condition_id' => 2,
                'email' => 0,
                'browser' => 1
            ],
            [
                'user_id' => $user_id,
                'condition_id' => 3,
                'email' => 0,
                'browser' => 1
            ],
            [
                'user_id' => $user_id,
                'condition_id' => 4,
                'email' => 0,
                'browser' => 1
            ],
            [
                'user_id' => $user_id,
                'condition_id' => 5,
                'email' => 0,
                'browser' => 1
            ],
            [
                'user_id' => $user_id,
                'condition_id' => 6,
                'email' => 0,
                'browser' => 1
            ],
            [
                'user_id' => $user_id,
                'condition_id' => 7,
                'email' => 0,
                'browser' => 1
            ],
            [
                'user_id' => $user_id,
                'condition_id' => 8,
                'email' => 0,
                'browser' => 1
            ],
            [
                'user_id' => $user_id,
                'condition_id' => 9,
                'email' => 0,
                'browser' => 1
            ],
            [
                'user_id' => $user_id,
                'condition_id' => 10,
                'email' => 0,
                'browser' => 1
            ],
            [
                'user_id' => $user_id,
                'condition_id' => 11,
                'email' => 0,
                'browser' => 1
            ]
        ]);
    }

    public static function trimData($array){
        foreach ($array as $key => $value){
            $array[$key] = trim($value);
        }
        return $array;
    }
}
