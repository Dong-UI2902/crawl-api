<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'url' => 'required|url|unique:stories,source_url',
        ];

        if ($this->isMethod(self::METHOD_PUT)) {
            $rules = [
                'title' => 'required|string',
                'slug' => 'required|string',
                'author_id' => 'required|exists:authors,id',
                'thumbnail' => 'required|unique:stories,thumbnail',
                'source_url' => 'required|unique:stories, url',
                'status' => 'required|boolean',
            ];
        };

        return $rules;
    }
}
