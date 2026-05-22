<?php

namespace App\Http\Requests\Admin;

use App\Models\PostComment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePostCommentVisibilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in([PostComment::STATUS_VISIBLE, PostComment::STATUS_HIDDEN])],
        ];
    }
}
