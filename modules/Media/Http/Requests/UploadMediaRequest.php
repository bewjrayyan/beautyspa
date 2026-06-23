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
                // Match PHP upload_max_filesize (typically 40M on this stack).
                'max:40960',
                'mimes:jpg,jpeg,png,gif,webp,svg,pdf,mp4,webm,mov',
            ],
        ];
    }
}
