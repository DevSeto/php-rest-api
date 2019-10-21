<?php

namespace App\Http\Controllers\Settings\Ticketing;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateLabelRequest;
use App\Models\TicketLabels;
use Illuminate\Http\Request;
use App\Models\Label;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Lang;
use Response;
use Validator;
use App\Helpers\Crypto;


class LabelController extends Controller
{

    protected $subDomain;

    function __construct()
    {
        $this->middleware('check_token');
        $this->subDomain = Helper::getSubDomain();
    }

    /**
     * Get all labels.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $labels = Label::select('*', 'labels.id as label_id')->orderBy('id', 'desc')->get();

        return Response::make(json_encode([
            'success' => true,
            'data' => $labels
        ]), 200);
    }

    /**
     * Create new label.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function create(CreateLabelRequest $request)
    {
        $data = $request->all();

        $label = Label::create([
            'color' => $data['color'],
            'body' => $data['body']
        ]);

        return Response::make(json_encode([
            'success' => true,
            'data' => $label
        ]), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Get label.
     *
     * @param  int $labelId
     *
     * @return \Illuminate\Http\Response
     */
    public function show($labelId)
    {
        $label = Label::find($labelId);
        return Response::make(json_encode([
            'success' => true,
            'data' => !empty($label) ? $label : []
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
     * Update label.
     *
     * @param  \Illuminate\Http\Request $request ,
     * @param  int $labelId
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $labelId)
    {
        $data = $request->all();
        $label = Label::find($labelId);
        if (empty($label)) {
            return Helper::send_error_response('wrong_label_id', Lang::get('labels.wrong_id'), 422);
        }

        $label->update($data);
        return Response::make(json_encode(['success' => true]), 200);
    }

    /**
     * Delete label.
     *
     * @param  int $labelId
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($labelId)
    {
        $label = Label::find($labelId);
        if (empty($label)) {
            return Helper::send_error_response('wrong_label_id', Lang::get('labels.wrong_id'), 422);
        }

        $label->delete();
        TicketLabels::where('label_id', $labelId)->delete();
        return Response::make(json_encode(['success' => true]), 200);
    }

    /**
     * Delete all labels.
     *
     * @return \Illuminate\Http\Response
     */
    public function deleteAll()
    {
        $labels = Label::get();
        if (!empty($labels->toArray())) {
            return Response::make(json_encode(['success' => false]), 200);
        }

        foreach ($labels as $item) {
            $item->delete();
        }
        return Response::make(json_encode(['success' => true]), 200);
    }
}
