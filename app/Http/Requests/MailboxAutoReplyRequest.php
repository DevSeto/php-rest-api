<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MailboxAutoReplyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Override validationData
     * @return array
     */

    protected function validationData()
    {
        return $this->request->all();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        //if 1 turned on every time
        //if 2 than turned on only in available hours
        //if 3 than turned off
        return [
            'auto_reply' => 'required|in:1,2,3',
            'auto_reply_subject' => 'required_if:auto_reply,==,1|required_if:auto_reply,==,2',
            'auto_reply_body' => 'required_if:auto_reply,==,1|required_if:auto_reply,==,2',
            'auto_reply_timeline' => 'required_if:auto_reply,==,2'
        ];
    }
}
