<?php

namespace App\Http\Controllers\Settings\Workflow;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Crypto;
use App\Models\Workflows\DataConditions;
use App\Models\Workflows\Workflows;
use App\Helpers\Helper;
use Response;
use Validator;
use DB;
use App\Helpers\WorkflowHelper;

class WorkflowConditionController extends Controller
{
    function __construct()
    {
        $this->middleware('check_token');
    }

    /**
     * Display a listing of the resource.
     *
     * @param integer $workflowId
     *
     * @return \Illuminate\Http\Response
     */
    public function index($workflowId)
    {
        $data = DataConditions::where('workflow_id', $workflowId)->get();
        $sortedData = WorkflowHelper::toSortDataConditions($data);

        return Response::make(json_encode([
            'success' => true,
            'data' => Crypto::encrypt(json_encode($sortedData, true))
        ]), 200);
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
        $dataConditions = $request->all();
        $conditions = $dataConditions['data'];

        foreach ($conditions as $condition) {
            $maxId = 0;
            if (count($condition) > 1) {
                for ($i = 0; $i < count($condition); $i++) {
                    $item = DataConditions::create([
                        'workflow_id' => $condition[$i]['workflow_id'],
                        'condition_id' => $condition[$i]['condition_id'],
                        'operator_id' => !empty($condition[$i]['operator_id']) ? $condition[$i]['operator_id'] : null,
                        'condition_value_id' => !empty($condition[$i]['condition_value_id']) ? $condition[$i]['condition_value_id'] : null,
                        'condition_value' => $condition[$i]['condition_value'],
                        'relation' => 'or',
                        'relative_condition_id' => $maxId
                    ]);
                    $maxId = $item->id;
                }
            } else {
                DataConditions::create([
                    'workflow_id' => $condition[0]['workflow_id'],
                    'condition_id' => $condition[0]['condition_id'],
                    'operator_id' => !empty($condition[0]['operator_id']) ? $condition[0]['operator_id'] : null,
                    'condition_value_id' => !empty($condition[0]['condition_value_id']) ? $condition[0]['condition_value_id'] : null,
                    'condition_value' => $condition[0]['condition_value'],
                    'relation' => 'and',
                    'relative_condition_id' => $condition[0]['workflow_id']
                ]);
            }
        }

        return Response::make(json_encode(['success' => true]), 200);
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
        //
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
}
