<?php

namespace App\Helpers;

use GuzzleHttp;

class SendgridApi
{
    public static function sendEmail($data){
        $client = new GuzzleHttp\Client();

        $cc = [];
        $bcc = [];

        if (isset($data['cc']) && !empty($data['cc'])) {
            foreach ($data['cc'] as $email){
                $cc[] = [
                    'email' => $email
                ];
            }
        }

        if (isset($data['bcc']) && !empty($data['bcc'])) {
            foreach ($data['bcc'] as $email){
                $bcc[] = [
                    'email' => $email
                ];
            }
        }
            $attachments = [];
//        dd($data['attachedFiles']);
        if (isset($data['attachedFiles']) && !empty($data['attachedFiles'])){
            foreach ($data['attachedFiles'] as $file) {
//                $file_path = str_replace(env('APP_URL') . '/', '', $file['path']);

                $fileContents = file_get_contents($file['file_full_path']);
                $base64 = base64_encode($fileContents);
                $attachments[] = [
                    'filename' => $file['file_name'],
                    'type' => $file['file_type'],
                    'content' => $base64
                ];
            }
        }
//        dd($attachments);

        $param = array(
            'headers' => array(
                'Message-ID' => isset($data['messageId']) ? $data['messageId'] : ''
            ),
            'personalizations' =>
                array(
                    0 =>
                        array(
                            'to' =>
                                array(
                                    0 =>
                                        array(
                                            'email' => $data['toEmail'],
                                            'name' => $data['toName'],
                                        ),
                                ),
                            'subject' => $data['subject'],
                        ),
                ),
            'custom_args' => array(
                'bird-subdomain' => Helper::$subDomain
            ),
            'from' =>
                array(
                    'email' => $data['fromEmail'],
                    'name' => $data['fromName'],
                ),
            'content' =>
                array(
                    0 =>
                        array(
                            'type' => 'text/html',
                            'value' => $data['commentText'],
                        ),
                ),

            'tracking_settings' => array(
                'open_tracking' => array(
                    'enable' => true,
                ),
                'click_tracking' => array(
                    'enable' => true,
                    'enable_text' => true
                )
            ),

            'reply_to' =>
                array(
                    'email' => $data['reply_to']
                )

        );

        if (!empty($attachments)){
            $param['attachments'] = $attachments;
        }

        if (!empty($cc)){
            $param['personalizations'][0]['cc'] = $cc;
        }

        if (!empty($bcc)){
            $param['personalizations'][0]['bcc'] = $bcc;
        }
//        dd(json_encode($param,true));
        return $client->post(
            config('external_urls.sendgrid_send_email'),
            [
                'headers' => [
                    'Authorization' => "Bearer " . env('SENDGRID_TOKEN'),
                    'Content-Type' => 'application/json',
                    'Bird-Message-Id' => '<8f14e45fceea167a5a36dedd4bea2543@panda.birdtest.nl>'
                ],
                'body' => json_encode($param,true)
            ]);
    }

    public static function createInboundParse($subdomain){
        $client = new GuzzleHttp\Client();

        $params = [
            "hostname" => $subdomain.env('PAGE_URL'),
            "url" => "http://api.birdtest.nl/api/receive_email", // hardcoded need to change
            "spam_check" => false,
            "send_raw" => true
        ];
//        dd($params);
        try{

            $a = $client->post(
                config('external_urls.sendgrid_create_inbound_parse'),
                [
                    'headers' => [
                        'Authorization' => "Bearer " . env('SENDGRID_TOKEN'),
                        'Content-Type' => 'application/json'
                    ],
                    'body' => json_encode($params)
                ]);
        } catch (\Exception $e){
            dd($e->getResponse()->json());
//            dd($e->getMessage());
        }
//        dd($a);
    }

    public static function getInboundParse(){

    }
}