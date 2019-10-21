<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use DB;
use App\Helpers\SparkPostApi;
use App\Models\SparkpostSubAccounts;
use App\Helpers\Cloudflare;
use App\Models\InboundDomains;
use App\Models\Subdomains;
use Log;
use Exception;
use App\Helpers\Helper;

class ConfigurateSparkpostAndCloudflare implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    protected $userdata;

    public function __construct($userdata)
    {
        $this->userdata = $userdata;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->configure($this->userdata);
        } catch (Exception $e) {
            DB::setDefaultConnection('mysql');

            DB::table('error_log_table')->insert(
                ['content' => $e->getMessage()]
            );
        }
    }

    public function configure($user)
    {
        //change db connection to company db
        Helper::changeDataBaseConnection($user['company_url']);

        //creating Sparkpost sub_account for user
        $sparky_sub_account = SparkPostApi::createSubAccount($user['company_url']);
        SparkpostSubAccounts::create([
            'user_id' => $user['id'],
            'sub_account_name' => $sparky_sub_account['results']['label'],
            'sub_account_id' => $sparky_sub_account['results']['subaccount_id'],
            'key' => $sparky_sub_account['results']['key']
        ]);

        //creating sending domain
        $sending_domain = SparkPostApi::createSendingDomain($sparky_sub_account['results']['subaccount_id'], $user['company_url'] . env('PAGE_URL'));

        //add subdomain for sending email
        Cloudflare::addSubdomain($sending_domain['results']['dkim']['selector'] . "._domainkey." . $sending_domain['results']['domain'] . "", "TXT", "v=DKIM1; k=rsa; h=sha256; p=" . $sending_domain['results']['dkim']['public'] . "", env('PAGE_URL_DOMAIN'));

        //TODO will find a better solution
        sleep(8);

        /**
         * TODO url's must come from env
         **/
        SparkPostApi::createInboundDomain($user['company_url'] . env('PAGE_URL'));

        $random = str_random(60);
        $relayWebHook = SparkPostApi::createRelayWebHook('https://' . env('APP_PROD') . env('PAGE_URL') . '/api/receive_email', 'Web Hook ' . $user['company_url'], $user['company_url'] . env('PAGE_URL'), $random);

        InboundDomains::create([
            'user_id' => $user['id'],
            'inbound_domain' => $user['company_url'] . env('PAGE_URL'),
            'token' => $random,
            'webhook_id' => $relayWebHook['results']['id']
        ]);

        //creating Cloudflare subdomains
        Cloudflare::addAllSubDomains($user['company_url'], $user['id']);

        //verify Sparkpost sending domain
        SparkPostApi::verifySendingDomain($sending_domain['results']['domain'], $sparky_sub_account['results']['key'], 1);

        //change database connection to default
        DB::setDefaultConnection('mysql');

        //store webhook id
        Subdomains::where('company_url', $user['company_url'])->update(['webhook_id' => $relayWebHook['results']['id']]);

    }
}
