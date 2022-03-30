<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserSupervisionStatusRequest extends FormRequest
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
                    'title' => ['string'],
                    'is_archive' => ['boolean'],
                    'color' => ['string']
                ];

            case 'POST':
                return [
                    'title' => ['required', 'string'],
                    'is_archive' => ['boolean'],
                    'color' => ['string']
                ];
            default:
                return [];
        }
    }
}
