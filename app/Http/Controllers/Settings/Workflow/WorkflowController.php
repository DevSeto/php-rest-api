<?php

namespace App\Http\Controllers\Settings\Workflow;

use App\Helpers\Crypto;
use App\Helpers\WorkflowHelper;
use App\Models\Workflows\DataConditions;
use App\Models\Workflows\Workflows;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use Response;
use Validator;
use DB;
use App\Models\Workflows\Conditions;
use App\Models\Workflows\WorkflowActions;
use App\Models\Workflows\WorkflowActionsList;

class WorkflowController extends Controller
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
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $apiToken = $request->header('Authorization');
        $user = Helper::getUser($apiToken);
        $data = $request->all();
        $data['user_id'] = $user['id'];
        $workflow = Workflows::create($data);

        return Response::make(json_encode([
            'success' => true,
            'data' => Crypto::encrypt(json_encode($workflow, true))
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
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $workflow = Workflows::find($id)->with(['actions'])->first();
        $dataConditions = WorkflowHelper::toSortDataConditions($workflow->conditions()->get());
        $workflow->conditions = $dataConditions;

        return Response::make(json_encode([
            'success' => true,
            'data' => Crypto::encrypt(json_encode($workflow, true))
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

    public function conditions()
    {
        $conditions = Conditions::get();
        return Response::make(json_encode([
            'success' => true,
            'data' => $conditions
        ]), 200);
    }

    public function condition($id)
    {
        $conditions = Conditions::select(['id', 'name'])->where('id', $id)->with(['operators', 'values'])->first();
        return Response::make(json_encode([
            'success' => true,
            'data' => $conditions
        ]), 200);
    }

    public function actions()
    {
        $action = WorkflowActionsList::with(['types', 'values'])->get();
        return Response::make(json_encode([
            'success' => true,
            'data' => $action
        ]), 200);
    }

    public function action($id)
    {
        $action = WorkflowActions::select(['id', 'action'])->where('id', $id)->first();
        return Response::make(json_encode([
            'success' => true,
            'data' => $action
        ]), 200);
    }
}
