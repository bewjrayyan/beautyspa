<?php

namespace Modules\Blog\Http\Requests;

use Illuminate\Validation\Rule;
use Modules\Blog\Entities\BlogPost;
use Modules\Core\Http\Requests\Request;

class SaveBlogPostRequest extends Request
{
    /**
     * Available attributes.
     *
     * @var string
     */
    protected $availableAttributes = 'blog::attributes.blogs.blogs';


    protected function prepareForValidation(): void
    {
        if ($this->has('description')) {
            $this->merge(['description' => clean_html($this->input('description'))]);
        }
    }

    public function rules()
    {
        return [
            'slug' => $this->getSlugRule(),
            'title' => 'required',
            'description' => 'required',
            'publish_status' => 'required',
        ];
    }


    private function getSlugRule(): array
    {
        $rules = $this->route()->getName() === 'admin.blog_posts.update' ? ['required'] : ['sometimes'];

        $slug = BlogPost::withoutGlobalScope('published')
            ->where('id', $this->id)
            ->value('slug');

        $rules[] = Rule::unique('blog_posts', 'slug')->ignore($slug, 'slug');

        return $rules;
    }
}
