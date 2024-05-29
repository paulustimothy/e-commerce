<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Forgot Password')]

class Forgot extends Component
{
    public $email;

    public function save()
    {
        $this->validate([
            'email' => 'email|required|max:255|exists:users,email'
        ]);
        $status = Password::sendResetLink(['email' => $this->email]);

        if ($status === Password::RESET_LINK_SENT) {
            session()->flash('success', 'Password reset link has been sent');
            $this->email = '';
        }
    }

    public function render()
    {
        return view('livewire.auth.forgot');
    }
}
