<?php

namespace App\Http\Controllers\Settings\Ticketing;

use App\Http\Controllers\Controller;
use App\Models\Mailbox;
use Illuminate\Http\Request;
use App\Models\CannedReply;
use App\Helpers\Helper;
use Response;
use Validator;
use App\Helpers\Crypto;
use Lang;


class CannedReplyController extends Controller
{
    protected $subDomain;
    protected $company;

    function __construct()
    {
        $this->middleware('check_token');
        $this->subDomain = Helper::getSubDomain();
    }

    /**
     * Get mailbox canned replies
     *
     * @param  integer $mailboxId
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $cannedReplies = CannedReply::with('category')->get();

        return Response::make(json_encode([
            'success' => true,
            'data' => $cannedReplies
        ]), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  integer $mailboxId
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
//        $user = Helper::getUser($request->header('authorization'));
        $user = Helper::$user;
        $data = $request->all();

        $data['user_id'] = $user['id'];
        $success = CannedReply::create($data);

        return Response::make(json_encode([
            'success' => true,
            'data' => $success
        ]), 200);

        return Response::make(json_encode([
            'success' => false,
            'error' => Lang:: get('mailbox.wring_id') . $data['mailbox_id']
        ]), 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  integer $mailboxId
     * @param  int $replyId
     *
     * @return \Illuminate\Http\Response
     */
    public function show($replyId)
    {
        $reply = CannedReply::find($replyId);
        if (empty($reply)) {
            return Helper::send_error_response('wrong_id', Lang::get('replies.wrong_id') . $replyId, 422);
        }

        $cannedReply = CannedReply::where('id', $replyId)->with('category')->first();
        return Response::make(json_encode([
            'success' => true,
            'data' => $cannedReply
        ]), 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  integer $mailboxId
     * @param  \Illuminate\Http\Request $request
     * @param  int $replyId
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $replyId)
    {
        $data = $request->all();
        $reply = CannedReply::find($replyId);
        if (empty($reply)) {
            return Helper::send_error_response('wring_canned_reply_id', Lang::get('mailbox.wring_canned_reply_id'), 422);
        }

        $reply->update($data);
        return Response::make(json_encode(['success' => true]), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $replyId
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($replyId)
    {
        $reply = CannedReply::find($replyId);
        if (empty($reply)) {
            return Helper::send_error_response('wrong_canned_reply_id', Lang::get('mailbox.wrong_canned_reply_id'), 422);
        }
        $reply->delete();
        return Response::make(json_encode(['success' => true]), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $mailboxId
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteAll($mailboxId)
    {
        $cannedReplies = CannedReply::where('mailbox_id', $mailboxId)->get();
        if (!empty($cannedReplies->toArray())) {
            foreach ($cannedReplies as $item) {
                $item->delete();
            }
        }
        return Response::make(json_encode(['success' => true]), 200);
    }
}
