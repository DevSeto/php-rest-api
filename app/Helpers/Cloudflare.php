<?php

namespace App\Helpers;

use GuzzleHttp;
use App\Models\CloudflareSubdomains;


class Cloudflare
{

    /**
     * adding subdomain to cloudflare
     * @param $name
     * @param $type
     * @param null $content
     * @param null $z
     * @param int $priority
     * @return mixed
     */

    public static function addSubdomain($name, $type, $content = null, $z = null, $priority = -1)
    {
        $client = new GuzzleHttp\Client();

        $param['name'] = $name;
        $param['content'] = $content ? $content : env('CLOUDFLARE_IP');
        $param['type'] = $type;

        if ($priority > 0) {
            $param['priority'] = 10;
        }
        return $client->post(
            env('CLOUDFLARE_API_URL'),
            [
                'headers' => [
                    'X-Auth-Email' => 'onlinetransaction.bv@gmail.com',
                    'X-Auth-Key' => env('CLOUDFLARE_TOKEN'),
                    'Content-Type' => 'application/json'
                ],
                'body' => json_encode($param)
            ])->json();
    }

    /**
     * delete subdomain by id
     * @param $id
     * @return mixed
     */

    public static function destroy($id)
    {
        $client = new GuzzleHttp\Client();

        return $client->post(
            'https://www.cloudflare.com/api_json.html',
            [
                'query' => [
                    'a' => 'rec_delete',
                    'tkn' => env('CLOUDFLARE_TOKEN'),
                    'email' => env('CLOUDFLARE_EMAIL'),
                    'z' => env('CLOUDFLARE_URL'),
                    'id' => $id
                ]
            ])->json();
    }

    /**
     * add some types of records to Cloudflare for Sparkpost
     * @param $company_url
     * @param $user_id
     */

    public static function addAllSubDomains($company_url, $user_id)
    {
        $cloudflare_subdomains = array();

        $cloudflare_subdomains[] = Cloudflare::addSubdomain($company_url, 'A');
        $cloudflare_subdomains[] = Cloudflare::addSubdomain($company_url, 'MX', 'rx1.sparkpostmail.com', null, 10);
        $cloudflare_subdomains[] = Cloudflare::addSubdomain($company_url, 'MX', 'rx2.sparkpostmail.com', null, 10);
        $cloudflare_subdomains[] = Cloudflare::addSubdomain($company_url, 'MX', 'rx3.sparkpostmail.com', null, 10);

        foreach ($cloudflare_subdomains as $subdomain) {
            CloudflareSubdomains::create([
                'user_id' => $user_id,
                'company_url' => $company_url,
                'cloudflare_subdomain_id' => $subdomain['result']['id'],
                'subdomain_details' => json_encode($subdomain)
            ]);
        }
    }
}

