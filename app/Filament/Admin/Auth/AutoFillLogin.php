<?php

declare(strict_types=1);

namespace App\Filament\Admin\Auth;

use Filament\Facades\Filament;
use Filament\Pages\Auth\Login;

class AutoFillLogin extends Login
{
    public function mount(): void
    {
        $email = env('FILAMENT_ADMIN_PREFILL_EMAIL');
        $password = env('FILAMENT_ADMIN_PREFILL_PASSWORD');

        if ($email !== null && $password !== null) {
            $this->data = [
                'email' => $email,
                'password' => $password,
                'remember' => true,
            ];
        }

        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }

        $this->form->fill($this->data ?? []);
    }
}
