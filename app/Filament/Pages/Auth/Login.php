<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Http\Responses\Auth\LoginResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function authenticate(): LoginResponse
    {
        $data = $this->form->getState();
        Log::info('Filament custom Login authenticate() invoked', [
            'email' => $data['email'] ?? null,
        ]);

        // Check if user exists and is inactive
        $user = User::where('email', $data['email'] ?? null)->first();
        if ($user && ! (bool) $user->status) {
            Log::info('Filament login blocked: inactive user', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
            throw ValidationException::withMessages([
                'data.email' => __('Your account is inactive. Please contact the administrator.'),
            ]);
        }

        return parent::authenticate();
    }
}
