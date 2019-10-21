<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateMailboxRequest extends FormRequest
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
        return [
            'name' => 'required',
            'email' => 'required|email',
            // auto_reply enabled 1 disabled 3
            'auto_reply' => 'required|in:1,3',
            'auto_reply_subject' => 'required_if:auto_reply,==,1',
            'auto_reply_body' => 'required_if:auto_reply,==,1',
            'auto_bcc' => 'required|in:1,2',
            //if users = 1 => allowed users, if = 0 only me , 2 => everyone
            'users' => 'required|in:1,2,3',
            'allowed_users' => 'required_if:users,==,1'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return ['allowed_users.required_if' => 'There is no selected users'];
    }
}
