@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
    <div class="auth-title">Masuk ke Panel Admin</div>
    <div class="auth-subtitle">PT. Teknologi Karya Anak Bangsa</div>

    <form method="POST" action="{{ route('login.store') }}">
        @csrf

        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <div class="input-group">
                <svg class="input-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="2" y="3" width="12" height="10" rx="1.5"/><path d="M2 5l6 4 6-4"/></svg>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                    @class(['form-control', 'is-invalid' => $errors->has('email')])
                    placeholder="nama@karyaanakbangsa.co.id" autocomplete="username" required autofocus>
            </div>
            @error('email')
                <div class="form-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="password">Kata Sandi</label>
            <div class="input-group">
                <svg class="input-icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="7" width="10" height="7" rx="1.5"/><path d="M5 7V5a3 3 0 016 0v2"/></svg>
                <input type="password" id="password" name="password"
                    @class(['form-control', 'is-invalid' => $errors->has('password')])
                    placeholder="••••••••" autocomplete="current-password" required>
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
