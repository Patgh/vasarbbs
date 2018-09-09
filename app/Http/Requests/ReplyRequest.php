<?php

namespace App\Http\Requests;

class ReplyRequest extends Request
{
    public function rules()
    {
        return [
            'content' => ['required', 'min:5'],
        ];
    }

    public function messages()
    {
        return [
            // Validation messages
        ];
    }
}
