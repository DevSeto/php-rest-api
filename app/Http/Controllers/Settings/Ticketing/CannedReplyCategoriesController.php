<?php

namespace App\Http\Controllers\Settings\Ticketing;

use App\Models\CannedReply;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use App\Models\CannedReplyCategories;
use Response;
use Validator;
use App\Helpers\Crypto;
use Lang;

class CannedReplyCategoriesController extends Controller
{

    function __construct()
    {
        $this->middleware('check_token');
    }

    /**
     * Display a listing of the resource.
     *
     * @param integer $mailboxId
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = CannedReplyCategories::withCount(['cannedReplies'])->get();

        return Response::make(json_encode([
            'success' => true,
            'data' => $categories
        ]), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \Illuminate\Http\Request $request
     * @param integer $mailboxId
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $data = $request->all();
        $create = CannedReplyCategories::create($data);

        return Response::make(json_encode([
            'success' => true,
            'data' => $create
        ]), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $mailboxId
     * @param  int $categoryId
     * @return \Illuminate\Http\Response
     */
    public function show($categoryId)
    {
        $cannedReplyCategory = CannedReplyCategories::where('id', $categoryId)->with(['cannedReplies'])->first();
        if (empty($cannedReplyCategory)) {
            return Helper::send_error_response('wring_canned_reply_id', Lang::get('mailbox.wring_canned_reply_id'), 422);
        }
        return Response::make(json_encode([
            'success' => true,
            'data' => $cannedReplyCategory
        ]), 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $mailboxId
     * @param  \Illuminate\Http\Request $request
     * @param  int $categoryId
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $categoryId)
    {
        $data = $request->all();
        $category = CannedReplyCategories::find($categoryId);

        if (empty($category)) {
            return Helper::send_error_response('canned_reply_category', Lang::get('mailbox.wrong_canned_reply_category_id'), 401);
        }
        return Response::make(json_encode(['success' => $category->update($data)]), 200);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $mailboxId
     * @param  int $categoryId
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($categoryId)
    {
        $category = CannedReplyCategories::find($categoryId);
        if (!empty($category)) {
            CannedReply::where('category_id',$categoryId)->delete();
            $category->delete();
            return Response::make(json_encode(['success' => true]), 200);
        }
        return Helper::send_error_response('canned_reply_category', Lang::get('mailbox.wrong_canned_reply_category_id'), 401);
    }
}
