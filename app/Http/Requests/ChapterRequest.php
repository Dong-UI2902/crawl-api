<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChapterRequest extends FormRequest
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
        return [
            'source_url' =>     'required|url|unique:chapters,source_url',
            'story_id' => 'required|exists:stories,id',
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
        ];
    }
}
