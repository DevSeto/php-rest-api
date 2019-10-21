<?php

namespace App\Console\Commands;

use App\Helpers\SparkPostApi;
use App\Models\FailedEmails;
use App\Models\Tickets;
use Carbon\Carbon;
use App\Models\TicketComments;
use DB;
use App\Helpers\Helper;
use Exception;
use Illuminate\Console\Command;

class SendFailedEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SendFailedEmails:sendemails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sending failed emails';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $this->handleFailedEmails();
        } catch (Exception $e) {
            DB::setDefaultConnection('mysql');

            DB::table('error_log_table')->insert(
                ['content' => $e->getMessage()]
            );
        }
    }

    public function handleFailedEmails(){

        //get all pending failed emails

        $emails = FailedEmails::where('attempts','<',10)->where('sent_status','pending')->get();


        foreach ($emails as $email){

            $done = true;
            $ticket_id_hash = str_replace(['<', '>'], '', explode('@', $email->messageId)[0]);

            FailedEmails::where('id',$email->id)->update([
                'attempts' => $email->attempts+1
            ]);
            if (!empty($email->attachedFiles)){
                $email->attachedFiles = json_decode($email->attachedFiles,true);
            }
            //try to send one more time max attempts(10)
            $send_email = false;
            $track = false;
            if ($email->track == 1){
                $track = true;
            }
            try {
                $send_email = SparkPostApi::createTransmission($email,$track);
            } catch (Exception $e) {
                $done = false;
                if($email->attempts <= 10){
                    FailedEmails:: where('id',$email->id)->update([
                        'attempts' => $email->attempts+1
                    ]);
                }else{
                    FailedEmails::where('id',$email->id)->update([
                        'sent_status' => 'undefined_error'
                    ]);
                }

            }

            if($done){
                FailedEmails::where('id',$email->id)->update([
                    'sent_status' => 'done'
                ]);
            }

            FailedEmails::where('created_at','<=',Carbon::now()->addDays(-5)->toDateTimeString())->
            where('status','done')->
            delete();


            if($send_email){
                Helper::changeDataBaseConnection($email->sub_domain);

                $ticket = Tickets::where('ticket_id_hash',$ticket_id_hash)->first();
                if ($ticket)
                    TicketComments::where('ticket_id',$ticket->id)->update(['transmission_id' => $send_email['results']['id']]);
                DB::setDefaultConnection('mysql');

            }

        }
    }
}
