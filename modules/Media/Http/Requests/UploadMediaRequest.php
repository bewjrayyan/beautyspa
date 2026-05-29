<?php

namespace Modules\Media\Http\Requests;

use Modules\Core\Http\Requests\Request;

class UploadMediaRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240',
                'mimes:jpg,jpeg,png,gif,webp,svg,pdf,mp4,webm',
            ],
        ];
    }
}
