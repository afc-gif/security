<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        Log::info('Login route hit', ['method' => $request->method()]);
        
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:1',
        ]);

        Log::info('Validation passed', ['email' => $credentials['email']]);

        // Check if user exists and is approved
        $user = User::where('email', $credentials['email'])->first();
        
        if ($user && $user->isPending()) {
            return back()->withErrors([
                'email' => 'Your account is pending approval. Please contact an administrator.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials)) {
            Log::info('Auth::attempt succeeded', ['email' => $credentials['email']]);
            $request->session()->regenerate();
            
            // Redirect based on role
            /** @var \App\Models\User $user */
            $user = auth()->user();
            if ($user->isAdmin()) {
                return redirect()->route('admin.dashboard')->with('success', 'Logged in successfully!');
            } elseif ($user->isManager()) {
                return redirect()->route('admin.dashboard')->with('success', 'Logged in successfully!');
            } elseif ($user->isFieldStaff()) {
                return redirect()->route('field.dashboard')->with('success', 'Logged in successfully!');
            } elseif ($user->isPos()) {
                return redirect()->route('pos.index')->with('success', 'Logged in to POS!');
            }
            
            return back()->withErrors([
                'email' => 'No role assigned. Please contact an administrator.',
            ])->onlyInput('email');
        }

        Log::warning('Auth::attempt failed', ['email' => $credentials['email']]);
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            // role will use database default value
            'status' => 'pending', // Waiting for approval
        ]);

        Log::info('New user registered and pending approval', ['email' => $user->email]);

        return redirect()->route('login')->with('success', 'Registration successful! Your account is pending admin approval. Please wait for an administrator to assign your role.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Logged out successfully!');
    }
}
