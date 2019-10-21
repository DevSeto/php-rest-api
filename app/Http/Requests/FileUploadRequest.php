<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileUploadRequest extends FormRequest
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
            'file' => 'required|mimes:jpeg,png,jpg,gif,tif,tiff,doc,pdf,docx,txt,ppc,mp3,wav,avi,mpg,mpeg,wmv,iso,flvmov|max:2048'
        ];
    }
}
