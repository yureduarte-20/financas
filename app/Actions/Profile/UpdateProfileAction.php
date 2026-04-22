<?php

namespace App\Actions\Profile;

use App\Actions\AbstractAction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProfileAction extends AbstractAction
{
    /**
     * @return array<string, Rule|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }

    public function execute(array $input): mixed
    {
        $validated = $this->validate($input);

        /** @var User $user */
        $user = Auth::user();
        $user->update($validated);

        return $user->refresh();
    }
}
