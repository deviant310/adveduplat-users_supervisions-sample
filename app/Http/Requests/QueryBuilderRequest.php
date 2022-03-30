<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class QueryBuilderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @param ValidationFactory $factory
     * @return Validator
     */
    public function validator(ValidationFactory $factory): Validator
    {
        return $factory->make(
            $this->sanitize(), $this->container->call([$this, 'rules']), $this->messages()
        );
    }

    /**
     * Sanitize the request.
     *
     * @return array
     */
    public function sanitize(): array
    {
        $filters = $this->get('filters');

        if(!empty($filters)){
            if(!is_array($filters)){
                $this->merge([
                    'filters' => json_decode(str_replace("\\n", '', $filters), true) ?? $filters
                ]);
            }

            $filtersContainsUnexpectedItems = collect($filters)->contains(function ($value) {
                return !is_array($value);
            });

            if(is_array($filters) && $filtersContainsUnexpectedItems){
                $this->merge([
                    'filters' => collect($filters)
                        ->map(function ($filter) {
                            return json_decode(str_replace("\\n", '', $filter), true) ?? $filter;
                        })
                        ->all()
                ]);
            }
        }

        return $this->all();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        if($this->route('id')){
            return [
                'append' => 'array',
                'with' => 'array',
                'select' => 'array',
            ];
        } else {
            return [
                'append' => 'array',
                'with' => 'array',
                'filters' => 'array',
                'select' => 'array',
                'sort' => 'array',
                'page' => 'integer',
                'start' => 'integer',
                'limit' => 'integer'
            ];
        }
    }
}
