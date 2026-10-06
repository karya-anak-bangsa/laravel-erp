@extends('layouts.auth')

@section('content')
    <div class="auth-title">Masuk ke Panel Admin</div>

    <form method="POST" action="{{ route('login.store') }}">
        @csrf

        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <div class="input-group">
                <i class="input-icon fa-solid fa-envelope" aria-hidden="true"></i>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                    @class(['form-control', 'is-invalid' => $errors->has('email')])
                    placeholder="Email Anda" autocomplete="username" required autofocus>
            </div>
            @error('email')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Kata Sandi</label>
            <div class="input-group">
                <i class="input-icon fa-solid fa-lock" aria-hidden="true"></i>
                <input type="password" id="password" name="password"
                    @class(['form-control', 'is-invalid' => $errors->has('password')])
                    placeholder="••••••••" autocomplete="current-password" required>
                <button type="button" class="input-toggle" data-toggle-password="password"
                    aria-label="Tampilkan kata sandi" aria-pressed="false" hidden>
                    <i class="fa-solid fa-eye" aria-hidden="true"></i>
                </button>
            </div>
            @error('password')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;height:38px">
            Masuk
        </button>
    </form>
@endsection
