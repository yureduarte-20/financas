<?php

namespace App\Livewire\Forms;

use Illuminate\Support\Facades\Gate;
use Livewire\Form;

abstract class AbstractActionForm extends Form
{

    public function verify(?array $rules = null)
    {
        return $this->validate(
            $rules ?? $this->rules(),
            $this->messages(),
            $this->attributes()
        );
    }

    public function rules(): array
    {
        return $this->getAction()->rules();
    }
    protected function validationAttributes()
    {
        return $this->getAction()->attributes();
    }
    public function attributes()
    {
        return $this->getValidationAttributes();
    }

    public function messages(): array
    {
        return $this->getAction()->messages();
    }

    abstract public function getAction(): \App\Actions\AbstractAction;
    protected function beforeSubmit()
    {
        $this->verify();
    }
    protected function afterSubmit($result)
    {
    }
    public function submit(): mixed
    {
        $this->beforeSubmit();
        return tap($this->getAction()->execute(
            $this->all()
        ), fn($result) => $this->afterSubmit($result));
    }
}