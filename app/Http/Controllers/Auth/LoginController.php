<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Show the login form.
     */
    public function showForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (! $this->authService->login($credentials['email'], $credentials['password'])) {
            throw ValidationException::withMessages([
                'email' => 'Invalid email or password.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    /**
     * Handle a logout request.
     */
    public function logout(Request $request)
    {
        $this->authService->logout();

        return redirect()->route('login');
    }
}
