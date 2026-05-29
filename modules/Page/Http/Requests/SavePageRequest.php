<?php

namespace Modules\Page\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Page\Entities\Page;
use Modules\Core\Http\Requests\Request;

class SavePageRequest extends Request
{
    /**
     * Available attributes.
     *
     * @var array
     */
    protected $availableAttributes = 'page::attributes';


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('body')) {
            $this->merge(['body' => clean_html($this->input('body'))]);
        }
    }

    public function rules()
    {
        return [
            'slug' => $this->getSlugRules(),
            'name' => 'required',
            'body' => 'required',
            'is_active' => 'required|boolean',
            'meta.meta_title' => 'nullable|string|max:70',
            'meta.meta_description' => 'nullable|string|max:320',
            'meta.og_image_id' => 'nullable|integer|exists:files,id',
            'meta.meta_robots' => ['nullable', Rule::in(['index, follow', 'noindex, follow'])],
        ];
    }


    private function getSlugRules()
    {
        $rules = $this->route()->getName() === 'admin.pages.update'
            ? ['required']
            : ['sometimes'];

        $slug = Page::withoutGlobalScope('active')->where('id', $this->id)->value('slug');

        $rules[] = Rule::unique('pages', 'slug')->ignore($slug, 'slug');

        return $rules;
    }
}
