<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Attempt to authenticate a user with email and password.
     */
    public function login(string $email, string $password): bool
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return false;
        }

        if (! $user->is_active) {
            return false;
        }

        Auth::login($user, remember: false);

        return true;
    }

    /**
     * Log the current user out and invalidate the session.
     */
    public function logout(): void
    {
        Auth::logout();

        session()->invalidate();
    }

    /**
     * Get the currently authenticated user.
     */
    public function getAuthenticatedUser(): ?User
    {
        return Auth::user();
    }
}
