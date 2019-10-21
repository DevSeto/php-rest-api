<?php

namespace App\Http\Controllers\Tickets;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Settings\Ticketing\MailboxController;
use App\Models\Tickets;
use App\Services\TicketHistoryService;
use Illuminate\Http\Request;
use App\Models\TicketsHistory;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Lang;
use Response;
use App\Models\MailboxUserPermissions;
use Validator;
use App\Helpers\Crypto;
use Carbon\Carbon;

class TicketsHistoryController extends Controller
{
    private $ticketHistoryService;

    function __construct(TicketHistoryService $ticketHistoryService)
    {
        $this->middleware('check_token');
        $this->ticketHistoryService = $ticketHistoryService;
    }

    /**
     * Display the specified resource.
     *
     * @param  int $ticketId
     *
     * @return \Illuminate\Http\Response
     */
    public function show($ticketId)
    {
        $history = $this->ticketHistoryService->getTicketHistoryByTicketId($ticketId);

        return Response::make(json_encode([
            'success' => true,
            'data' => $history
        ]), 200);
    }

}
