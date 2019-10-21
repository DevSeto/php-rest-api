<?php

namespace App\Http\Controllers;

use App\Models\Customers;
use App\Models\Label;
use App\Models\Tickets;
use Illuminate\Http\Request;
use Response;

class SearchController extends Controller
{

    function __construct()
    {
        $this->middleware('check_token');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function searchCustomers(Request $request)
    {
        $key = $request->get('search');
        $defaultEmail = 'hello@' . env('PAGE_URL_DOMAIN');
        $result = Customers::search($key, null, false, false)->where('email', '!=', $defaultEmail)->get();

        return Response::make(json_encode([
            'success' => true,
            'data' => $result
        ]), 200);
    }

    /**
     * Search tickets by ticket customer email
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $key = $request->get('key');
        $result = Tickets::where('customer_email', 'LIKE', $key . '%')
            ->orWhere('subject', 'LIKE', $key . '%')
            ->get();

        return Response::make(json_encode([
            'success' => true,
            'data' => $result
        ]), 200);
    }


    /**
     * Ticket main search
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function mainSearch(Request $request)
    {
        $data = $request->all();
        $tickets = new Tickets();

        if (!empty($data['assigned'])) {
            $assignedUsers = $data['assigned'];
            $dataUsers = explode(',', $assignedUsers);
            if (!empty($dataUsers)) {
                $users = User::where(function ($q) use ($dataUsers) {
                    return $users = array_map(function ($user) use ($q) {
                        return $q->where('email', 'like', '%' . $user . '%')
                            ->orWhere('first_name', 'like', '%' . $user . '%')
                            ->orWhere('last_name', 'like', '%' . $user . '%');
                    }, $dataUsers);
                })->get()->pluck('id');
            }
            if (!empty($users)) {
                $tickets = $tickets->whereIn('assign_agent_id', $users);
            }
        }

        if (!empty($data['customers'])) {
            $dataCustomers = explode(',', $data['customers']);
            if (!empty($dataCustomers)) {
                $users = Customers::where(function ($q) use ($dataCustomers) {
                    return $users = array_map(function ($user) use ($q) {
                        return $q->where('email', 'like', '%' . $user . '%')
                            ->orWhere('first_name', 'like', '%' . $user . '%')
                            ->orWhere('last_name', 'like', '%' . $user . '%');
                    }, $dataCustomers);
                })->get()->pluck('id')->toArray();
            }
            if (!empty($users)) {
                $tickets = $tickets->orWhereIn('customer_id', $users);
            }
        }

        if (!empty($data['mailboxes'])) {
            $dataMailboxes = explode(',', $data['mailboxes']);
            if (!empty($dataMailboxes)) {
                $mailboxes = Mailbox::where(function ($q) use ($dataMailboxes) {
                    array_map(function ($e) use ($q) {
                        return $q->where('name', 'like', '%' . $e . '%');
                    }, $dataMailboxes);
                })->pluck('id');
            }
            if (!empty($mailboxes)) {
                $tickets = $tickets->orWhereIn('mailbox_id', $mailboxes);
            }
        }

        if (!empty($data['status'])) {
            $dataStatus = explode(',', $data['status']);
            if (!empty($dataStatus)) {
                $tickets = $tickets->orWhereIn('status', $dataStatus);
            }
        }

        if (!empty($data['subjects'])) {
            $dataSubjects = explode(',', $data['subjects']);
            if (!empty($dataSubjects)) {
                $tickets = $tickets->orWhere(function ($q) use ($dataSubjects) {
                    array_map(function ($e) use ($q) {
                        $q->orWhere('subject', 'like', '%' . $e . '%');
                    }, $dataSubjects);
                });
            }
        }

        if (!empty($data['tag'])) {
            $dataTags = explode(',', $data['tag']);
            if (!empty($dataTags)) {
                Label::where(function ($q) use ($dataTags) {
                    array_map(function ($e) use ($q) {
                        return $q->where('body', 'like', '%' . $e . '%');
                    }, $dataTags);
                })->get();
                $tackedTicketsId = TicketLabels::whereIn('label_id', $dataTags)->pluck('ticket_id');
                if (!empty($tackedTicketsId)) {
                    $tickets = $tickets->orWhereIn('id', $tackedTicketsId);
                }
            }
        }

        $tickets = $tickets->get()->toArray();
        return Response::make(json_encode([
            'success' => true,
            'data' => $tickets
        ]), 200);
    }

}
