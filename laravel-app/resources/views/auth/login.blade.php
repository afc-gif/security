@extends('layout')

@section('title', 'Login - ARTSCI')

@section('content')
<div class="auth-container">
    <div class="auth-box">
        <div class="auth-brand">
            <img src="{{ asset('images/logo.png') }}" alt="ARTSCI logo">
            <div class="brand-text">
                <span class="brand-name">ARTSCI</span>
                <span class="brand-tagline">Security POS</span>
            </div>
        </div>
        <h1 class="auth-title">Login</h1>
        <p class="auth-subtitle">Welcome back to ARTSCI</p>

        @if ($errors->any())
            <div class="alert alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="auth-form">
            @csrf

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                @error('password')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary btn-lg">Login</button>
        </form>

        <p class="auth-link">Don't have an account? <a href="{{ route('register') }}">Register here</a></p>
    </div>
</div>
@endsection
