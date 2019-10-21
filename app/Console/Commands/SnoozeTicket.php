<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tickets;
use App\Models\TicketSnooze;
use App\Helpers\Helper;
use Carbon\Carbon;
use DB;


class SnoozeTicket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SnoozeTicket:snoozeticket';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Snooze ticket status';

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
        //get all tickets for snoozing

        $tickets = TicketSnooze::get();

        if(!empty($tickets->toArray())){
            foreach ($tickets as $ticket){
                if(Carbon::now()->gt(Carbon::parse($ticket->snooze))){
                    Helper::changeDataBaseConnection($ticket->sub_domain);

                    /**
                     * ToDo
                     *
                     * author
                     *
                     * add to ticket history
                     */

                    Tickets::where('ticket_id_hash',$ticket->ticket_id_hash)->update([
                        'snooze' => null,
                        'status' => 'open'
                    ]);

                    DB::setDefaultConnection('mysql');

                    TicketSnooze::where('id',$ticket->id)->delete();
                }
            }
        }
    }
}
