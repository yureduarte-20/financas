<?php

namespace App\Livewire\Profile;

use App\Livewire\Forms\UpdatePasswordForm;
use App\Livewire\Forms\UpdateProfileForm;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ManageProfile extends Component
{
    public UpdateProfileForm $profileForm;
    public UpdatePasswordForm $passwordForm;

    public function mount(): void
    {
        $this->profileForm->name = (string) Auth::user()->name;
        $this->profileForm->email = (string) Auth::user()->email;
    }

    public function render()
    {
        return view('livewire.profile.manage-profile');
    }

    public function updateProfile(): void
    {
        $user = $this->profileForm->submit();

        $this->dispatch('profile-updated', name: $user->name);
        $this->dispatch('notify', type: 'success', title: 'Sucesso', message: 'Perfil atualizado com sucesso.');
    }

    public function updatePassword(): void
    {
        $this->passwordForm->submit();
        $this->passwordForm->reset();
        $this->dispatch('notify', type: 'success', title: 'Sucesso', message: 'Senha alterada com sucesso.');
    }
}
