<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return response()->json([
            'message' => 'Login endpoint - redirect to React frontend',
            'status' => session('status'),
        ]);
    }

    /**
     * Handle login attempt.
     */
    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string|max:255',
            'password' => 'required|string|min:1',
            'login_method' => 'required|in:email,username',
        ]);

        $loginMethod = $request->login_method;
        $identifier = $request->identifier;

        // Buscar usuario por email o username
        $user = User::where(function ($query) use ($loginMethod, $identifier) {
            if ($loginMethod === 'email') {
                $query->where('email', $identifier);
            } else {
                $query->where('username', $identifier);
            }
        })->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'identifier' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        if (!$user->is_active) {
            throw ValidationException::withMessages([
                'identifier' => ['La cuenta está desactivada.'],
            ]);
        }

        // Autenticar al usuario
        Auth::login($user, $request->boolean('remember', false));

        // Actualizar último login
        $user->update(['last_login_at' => now()]);

        \Log::info('Web Auth: Login successful', [
            'user_id' => $user->id,
            'identifier' => $identifier,
            'identifier_type' => $loginMethod,
        ]);

        // Redirigir al dashboard o página de inicio
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        \Log::info('Web Auth: User logged out', [
            'user_id' => $user->id ?? null,
            'username' => $user->username ?? null,
        ]);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Show the dashboard.
     */
    public function dashboard()
    {
        return response()->json([
            'message' => 'Dashboard endpoint - redirect to React frontend',
            'user' => Auth::user()->load('roles'),
        ]);
    }
}
