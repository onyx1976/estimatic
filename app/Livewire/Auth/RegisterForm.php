<?php

namespace App\Livewire\Auth;

use Illuminate\View\View;
use Livewire\Component;

/**
 * Livewire component for the register form.
 */
class RegisterForm extends Component
{
    public function render(): View
    {
        return view('livewire.auth.register-form');
    }
}
