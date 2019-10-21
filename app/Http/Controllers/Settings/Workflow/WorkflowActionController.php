<?php

namespace App\Http\Controllers\Settings\Workflow;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Crypto;
use App\Models\Workflows\DataActions;
use App\Models\Workflows\Workflows;
use App\Helpers\Helper;
use Response;
use Validator;
use DB;

class WorkflowActionController extends Controller
{
    function __construct()
    {
        $this->middleware('check_token');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \Illuminate\Http\Request $request
     *
     */
    public function create(Request $request)
    {
        $data = $request->all();
        foreach ($data['data'] as $item) {
            DataActions::create([
                'workflow_id' => $item['workflow_id'],
                'action_id' => $item['action_id'],
                'action_value_id' => $item['action_value_id'],
                'action_value' => $item['action_value']
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
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
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $actions = DataActions::where('workflow_id', $id)->get();
        return Response::make(json_encode([
            'success' => true,
            'data' => $actions
        ]), 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
