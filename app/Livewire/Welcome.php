<?php

namespace App\Livewire;

use Livewire\Component;

class Welcome extends Component
{
    public function mount()
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            if ($user->role === \App\Enums\UserRole::ADMIN) {
                return redirect()->to('/admin');
            } elseif ($user->role === \App\Enums\UserRole::PROMOTER) {
                return redirect()->to('/promoter');
            } elseif ($user->role === \App\Enums\UserRole::VALIDATOR) {
                return redirect()->to('/validator');
            }
        }
    }

    public function render()
    {
        return view('livewire.welcome')->layout('components.layouts.landing');
    }
}
