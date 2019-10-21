<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTicketRequest extends FormRequest
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
        $baseRules = [
            'customer_name' => 'required|max:35|min:5',
            'customer_email' => 'required|email',
            'subject' => 'required',
            'status' => 'in:open,closed,spam,pending,draft'
        ];

        if (empty($this->request->get('attachments'))) {
            $baseRules['body'] = 'required';
        }

        return $baseRules;
    }
}
