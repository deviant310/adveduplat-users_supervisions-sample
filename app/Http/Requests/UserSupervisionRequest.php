<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserSupervisionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        switch ($this->getMethod()) {
            case 'PUT':
                return [
                    'user_id' => ['exists:users,id'],
                    'course_id' => ['exists:courses,id'],
                    'status_id' => ['nullable', 'exists:users_supervisions_statuses,id'],
                    'comment' => ['nullable', 'string'],
                    'deadline_at' => ['nullable', 'date'],
                ];

            case 'POST':
                return [
                    'user_id' => ['required', 'exists:users,id'],
                    'course_id' => [
                        'required',
                        'exists:courses,id',
                        Rule::unique('users_supervisions')->where(function ($query) {
                            /** @noinspection PhpUndefinedFieldInspection */
                            return $query
                                ->where('user_id', $this->user_id)
                                ->where('course_id', $this->course_id);
                        })
                    ],
                    'status_id' => ['nullable', 'exists:users_supervisions_statuses,id'],
                    'comment' => ['nullable', 'string'],
                    'deadline_at' => ['nullable', 'date'],
                ];
            default:
                return [];
        }
    }
}
