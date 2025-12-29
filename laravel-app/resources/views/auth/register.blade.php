@extends('layout')

@section('title', 'Register - ARTSCI')

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
        <h1 class="auth-title">Register</h1>
        <p class="auth-subtitle">Create your ARTSCI account</p>

        @if ($errors->any())
            <div class="alert alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="auth-form">
            @csrf

            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
                @error('name')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
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

            <div class="form-group">
                <label for="password_confirmation">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required>
            </div>

            <button type="submit" class="btn btn-primary btn-lg">Register</button>
        </form>

        <p class="auth-link">Already have an account? <a href="{{ route('login') }}">Login here</a></p>
    </div>
</div>
@endsection
