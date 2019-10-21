<?php

namespace App\Helpers;

use App\Models\SparkpostSubAccounts;
use GuzzleHttp;

class SparkPostApi
{

    /**
     * create Sparkpost subaccount
     * @param $subDomain
     * @return mixed
     */

    public static function createSubAccount($subDomain)
    {
        $client = new GuzzleHttp\Client();
        return $client->post(
            'https://api.sparkpost.com/api/v1/subaccounts',
            [
                'headers' => ['Authorization' => env('SPARKPOST_SECRET')],
                'body' => ['name' => $subDomain,
                    'key_label' => $subDomain,
                    'key_grants' => [
                        'smtp/inject',
                        'sending_domains/manage',
                        'message_events/view',
                        'suppression_lists/manage',
                        'tracking_domains/view',
                        'tracking_domains/manage',
                        'transmissions/modify',
                        'transmissions/view'
                    ]
                ]
            ])->json();
    }

    /**
     * get subaccount details by id
     * @param $subaccount_id
     * @return mixed
     */

    public static function getSubAccount($subaccount_id)
    {
        $client = new GuzzleHttp\Client();
        return $client->get(
            'https://api.sparkpost.com/api/v1/subaccounts/' . $subaccount_id,
            [
                'headers' => ['Authorization' => env('SPARKPOST_SECRET')],
            ])->json();
    }


    /**
     * create sending domain for subaccount
     * @param $subAccountId
     * @param $subDomain
     * @param bool $trackingDomain
     * @return mixed
     */

    public static function createSendingDomain($subAccountId, $subDomain, $trackingDomain = false)
    {
        $client = new GuzzleHttp\Client();
        $param = ['domain' => $subDomain];

        if ($trackingDomain) {
            $param['tracking_domain'] = $trackingDomain;
        }

        return $client->post(
            'https://api.sparkpost.com/api/v1/sending-domains',
            [
                'headers' => [
                    'Authorization' => env('SPARKPOST_SECRET'),
                    'X-MSYS-SUBACCOUNT' => $subAccountId
                ],
                'body' => json_encode($param)
            ])->json();
    }

    /**
     * Verify Sending domain
     * @param $subDomain
     * @param $token
     * @param $status
     * @return mixed
     */

    public static function verifySendingDomain($subDomain, $token, $status)
    {
        $client = new GuzzleHttp\Client();
        $param = ['abuse_at_verify' => true];
        if ($status == 0 || $status == 1) {
            $param['dkim_verify'] = true;
            return $client->post(
                'https://api.sparkpost.com/api/v1/sending-domains/' . $subDomain . '/verify',
                [
                    'headers' => ['Authorization' => $token],
                    'body' => json_encode($param)
                ])->json();
        }
    }

    /**
     * delete Sending Domain
     * @param $subDomain
     * @param $token
     * @return GuzzleHttp\Message\ResponseInterface
     */

    public static function deleteSendingDomain($subDomain, $token)
    {
        $client = new GuzzleHttp\Client();

        return $client->delete(
            'https://api.sparkpost.com/api/v1/sending-domains/' . $subDomain,
            [
                'headers' => ['Authorization' => $token]
            ]
        );
    }

    /**
     * get Sending domain details
     * @param $subDomain
     * @param $token
     * @param string $domain
     * @return mixed
     */

    public static function getSendingDomain($subDomain, $token, $domain = '')
    {
        $client = new GuzzleHttp\Client();

        return $client->get(
            'https://api.sparkpost.com/api/v1/sending-domains/' . $subDomain . $domain,
            [
                'headers' => ['Authorization' => $token]
            ])->json();
    }

    /**
     * create tracking domain to track emails
     * @param $tracking_domain
     * @param $token
     * @return mixed
     */

    public static function createTrackingDomain($tracking_domain, $token)
    {
        $client = new GuzzleHttp\Client();

        return $client->post(
            'https://api.sparkpost.com/api/v1/tracking-domains',
            [
                'headers' => ['Authorization' => $token],
                'body' => json_encode(['domain' => $tracking_domain])
            ])->json();
    }

    /**
     * delete tracking domain
     * @param $tracking_domain
     * @param $token
     * @return GuzzleHttp\Message\ResponseInterface
     */

    public static function deleteTrackingDomain($tracking_domain, $token)
    {
        $client = new GuzzleHttp\Client();

        return $client->delete(
            'https://api.sparkpost.com/api/v1/tracking-domains/' . $tracking_domain,
            [
                'headers' => ['Authorization' => $token]
            ]);
    }

    /**
     * verify tracking domain
     * @param $tracking_domain
     * @param $token
     * @return mixed
     */

    public static function verifyTrackingDomain($tracking_domain, $token)
    {
        $client = new GuzzleHttp\Client();

        return $client->post(
            'https://api.sparkpost.com/api/v1/tracking-domains/' . $tracking_domain . '/verify',
            [
                'headers' => ['Authorization' => $token]
            ])->json();
    }

    /**
     * update sending domain if necessary
     * @param $sending_domain
     * @param $tracking_domain
     * @param $token
     * @return mixed
     */

    public static function updateSendingDomain($sending_domain, $tracking_domain, $token)
    {
        $client = new GuzzleHttp\Client();

        return $client->put(
            'https://api.sparkpost.com/api/v1/sending-domains/' . $sending_domain,
            [
                'headers' => ['Authorization' => $token],
                'body' => json_encode(['tracking_domain' => $tracking_domain,
                    'dkim' => [
                        'private' => 'MIICXgIBAAKBgQC+W6scd3XWwvC/hPRksfDYFi3ztgyS9OSqnnjtNQeDdTSD1DRx/xFar2wjmzxp2+SnJ5pspaF77VZveN3P/HVmXZVghr3asoV9WBx/uW1nDIUxU35L4juXiTwsMAbgMyh3NqIKTNKyMDy4P8vpEhtH1iv/BrwMdBjHDVCycB8WnwIDAQABAoGBAITb3BCRPBi5lGhHdn+1RgC7cjUQEbSb4eFHm+ULRwQ0UIPWHwiVWtptZ09usHq989fKp1g/PfcNzm8c78uTS6gCxfECweFCRK6EdO6cCCr1cfWvmBdSjzYhODUdQeyWZi2ozqd0FhGWoV4VHseh4iLj36DzleTLtOZj3FhAo1WJAkEA68T+KkGeDyWwvttYtuSiQCCTrXYAWTQnkIUxduCp7Ap6tVeIDn3TaXTj74UbEgaNgLhjG4bX//fdeDW6PaK9YwJBAM6xJmwHLPMgwNVjiz3u/6fhY3kaZTWcxtMkXCjh1QE82KzDwqyrCg7EFjTtFysSHCAZxXZMcivGl4TZLHnydJUCQQCx16+M+mAatuiCnvxlQUMuMiSTNK6Amzm45u9v53nlZeY3weYMYFdHdfe1pebMiwrT7MI9clKebz6svYJVmdtXAkApDAc8VuR3WB7TgdRKNWdyGJGfoD1PO1ZE4iinOcoKV+IT1UCY99Kkgg6C7j62n/8T5OpRBvd5eBPpHxP1F9BNAkEA5Nf2VO9lcTetksHdIeKK+F7sio6UZn0Rv7iUo3ALrN1D1cGfWIh/Y1g==',
                        'public' => 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC+W6scd3XWwvC/hPRksfDYFi3ztgyS9OSqnnjtNQeDdTSD1DRx/xFar2wjmzxp2+SnJ5pspaF77VZveN3P/HVmXZVghr3asoV9WBx/uW1nDIUxU35L4juXiTwsMAbgMyh3NqIKTNKyMDy4P8vpEhtH1iv/BrwMdBjHDVCycB8WnwIDAQAB',
                        'selector' => 'hello_selector',
                        'headers' => 'from:to:subject:date'
                    ]
                ])
            ])->json();
    }

    /**
     * create inbound domain
     * @param $inbound_domain
     * @return mixed
     */

    public static function createInboundDomain($inbound_domain)
    {
        $client = new GuzzleHttp\Client();

        return $client->post(
            'https://api.sparkpost.com/api/v1/inbound-domains',
            [
                'headers' => ['Authorization' => env('SPARKPOST_SECRET')],
                'body' => json_encode(['domain' => $inbound_domain])
            ])->json();
    }

    /**
     * create relay webhook for incoming emails
     * @param $target
     * @param $name
     * @param $inbound_domain
     * @param $token
     * @return mixed
     */

    public static function createRelayWebHook($target, $name, $inbound_domain, $token)
    {
        $client = new GuzzleHttp\Client();

        return $client->post(
            'https://api.sparkpost.com/api/v1/relay-webhooks',
            [
                'headers' => ['Authorization' => env('SPARKPOST_SECRET'), 'Content-Type' => 'application/json'],
                'body' => json_encode([
                    'name' => $name,
                    'target' => $target,
                    'auth_token' => $token,
                    'match' => [
//                        'protocol' => 'SMTP',
                        'domain' => $inbound_domain
                    ]
                ])
            ])->json();
    }

    /**
     * create transmission (send email)
     * @param $options
     * @return mixed
     */

    public static function createTransmission($options,$track = true)
    {
        $attachments = [];
        if (isset($options['attachedFiles']) && !empty($options['attachedFiles'])){
            foreach ($options['attachedFiles'] as $file) {
                $file_path = str_replace(env('APP_URL') . '/', '', $file['path']);

                $fileContents = file_get_contents($file_path);
                $base64 = chunk_split(base64_encode($fileContents));

                $attachments[] = [
                    'name' => $file['origName'],
                    'type' => mime_content_type($file_path),
                    'data' => $base64
                ];
            }
        }

        $param = [
            'options' => [
                'transactional' => $track,
                'open_tracking' => $track,
                'click_tracking' => $track,
            ]
        ];

        if (isset($param['toName'])) {
            $param['recipients'] = [
                [
                    'address' => [
                        'email' => $options['toEmail'],
                        'name' => $options['toName']
                    ]
                ]
            ];
        } else {
            $param['recipients'] = [
                [
                    'address' => ['email' => $options['toEmail']]
                ]
            ];
        }

        if (!empty($options['cc'])) {
            $emails = explode(",", $options['cc']);
            foreach ($emails as $key => $item) {
                array_push($param['recipients'], [
                    'address' => [
                        'email' => $item,
                        'header_to' => $options['toEmail']
                    ]
                ]);
            }
        }

        if (!empty($options['bcc'])) {
            $emails = explode(",", $options['bcc']);

            foreach ($emails as $key => $item) {
                array_push($param['recipients'], [
                    'address' => [
                        'email' => $item,
                        'header_to' => $options['toEmail']
                    ]
                ]);
            }
        }
        $param['content'] = [
            'subject' => $options['subject'],
            'from' => [
                'email' => $options['fromEmail'],
                'name' => $options['fromName']
            ],
            "headers" => [
                "CC" => !empty($options['cc']) ? explode(",", $options['cc']) : ''
            ],
            'html' => $options['commentText'],
            'attachments' => $attachments
        ];

        $param['check'] = !empty($options['cc']);
        if (!empty($options['cc'])) {
            $param['content']['headers'] = [
                "CC" => $options['cc']
            ];
        }

        if (isset($options['messageId']))
            $param['content']['headers'] = [
                'Message-ID' => $options['messageId']
            ];

        if (isset($options['References']) && !empty($options['References'])) {
            $param['content']['headers'] = [
                'References' => $options['References']
            ];
        }

        if (isset($options['reply_to'])) {
            $param['content']['reply_to'] = $options['replyEmail'];
        }

        $client = new GuzzleHttp\Client();
        return $client->post(
            'https://api.sparkpost.com/api/v1/transmissions',
            [
                'headers' => ['Authorization' => $options['token']],
                'body' => json_encode($param)
            ])->json();
    }


    /**
     *get Sparkpost sub_account data from DB
     * @return mixed
     */

    public static function getSparkpostSubAccountsData()
    {
        return SparkpostSubAccounts::where('sub_account_name', Helper::$subDomain)->first()->toArray();
    }

}