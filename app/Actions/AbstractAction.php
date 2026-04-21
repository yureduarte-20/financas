<?php

namespace App\Actions;

use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Illuminate\Validation\Validator;

abstract class AbstractAction
{
    /**
     * @return array<string, Rule|string>
     */
    abstract public function rules(): array;

    public function messages(): array
    {
        return [];
    }

    public function attributes(): array
    {
        return [];
    }

    public function makeValidator(array $input, ?array $rules = null, ?array $messages = null, ?array $attributes = null)
    {
        $rules ??= $this->rules();
        $messages ??= $this->messages();
        $attributes ??= $this->attributes();
        return ValidatorFacade::make(
            $input,
            $rules,
            $messages,
            $attributes
        )->after(fn($va) => $this->afterValidation($va));
    }

    public function validate($input)
    {
        return $this->makeValidator($input)->validate();
    }
    public function afterValidation(Validator $validator)
    {
    }
    abstract public function execute(array $input): mixed;
}