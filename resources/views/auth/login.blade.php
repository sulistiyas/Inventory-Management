<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>Login — WarehouSe</title>

  {{-- Favicon --}}
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='6' fill='%23F59E0B'/><path d='M8 22V12l8-4 8 4v10l-8 4-8-4z' fill='none' stroke='white' stroke-width='1.5'/></svg>" />

  {{-- CSS --}}
  <link rel="stylesheet" href="{{ asset('css/app.css') }}" />

  <style>
    /* ── Login Page Styles ─────────────────────────────────────── */
    body.login-page {
      background: var(--bg-body);
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }

    .login-container {
      width: 100%;
      max-width: 420px;
      padding: 24px;
    }

    .login-header {
      text-align: center;
      margin-bottom: 40px;
    }

    .login-logo {
      width: 56px;
      height: 56px;
      background: var(--accent);
      border-radius: var(--radius-md);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      box-shadow: var(--shadow-md);
    }

    .login-logo svg {
      width: 32px;
      height: 32px;
      color: white;
    }

    .login-header h1 {
      font-size: 28px;
      font-weight: 700;
      color: var(--text-primary);
      margin-bottom: 8px;
    }

    .login-header p {
      font-size: 14px;
      color: var(--text-secondary);
    }

    .login-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 32px;
      box-shadow: var(--shadow-md);
    }

    .login-form {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }

    .form-group {
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .form-label {
      font-size: 14px;
      font-weight: 500;
      color: var(--text-primary);
    }

    .form-input {
      padding: 10px 12px;
      border: 1px solid var(--border);
      border-radius: var(--radius-sm);
      font-size: 14px;
      font-family: var(--font-body);
      color: var(--text-primary);
      background: var(--bg-card);
      transition: border-color var(--transition), box-shadow var(--transition);
    }

    .form-input:focus {
      outline: none;
      border-color: var(--accent);
      box-shadow: 0 0 0 3px var(--accent-glow);
    }

    .form-input::placeholder {
      color: var(--text-muted);
    }

    .form-input.error {
      border-color: var(--danger);
      background: #FEE2E2;
    }

    .form-error {
      font-size: 13px;
      color: var(--danger);
      margin-top: -4px;
    }

    .alert {
      padding: 12px 14px;
      border-radius: var(--radius-sm);
      font-size: 13px;
      margin-bottom: 20px;
      display: flex;
      gap: 12px;
      align-items: flex-start;
    }

    .alert-error {
      background: var(--danger-bg);
      color: var(--danger);
      border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .alert-error svg {
      width: 18px;
      height: 18px;
      flex-shrink: 0;
      margin-top: 2px;
    }

    .alert-content {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .alert-title {
      font-weight: 600;
    }

    .alert-message {
      font-size: 12px;
    }

    .btn-login {
      padding: 10px 16px;
      background: var(--accent);
      color: white;
      border: none;
      border-radius: var(--radius-sm);
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: background-color var(--transition), box-shadow var(--transition);
      font-family: var(--font-body);
    }

    .btn-login:hover {
      background: var(--accent-dark);
      box-shadow: var(--shadow-md);
    }

    .btn-login:active {
      transform: scale(0.98);
    }

    .login-footer {
      margin-top: 24px;
      padding-top: 20px;
      border-top: 1px solid var(--border);
      text-align: center;
      font-size: 13px;
      color: var(--text-secondary);
    }

    .login-footer strong {
      color: var(--text-primary);
      display: block;
      margin-bottom: 8px;
    }

    @media (max-width: 480px) {
      .login-container {
        padding: 16px;
      }

      .login-card {
        padding: 24px;
      }

      .login-header h1 {
        font-size: 24px;
      }
    }
  </style>
</head>

<body class="login-page">

  <div class="login-container">

    {{-- Logo & Header --}}
    <div class="login-header">
      <div class="login-logo">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path d="M20 7l-8.97 5.7a1.94 1.94 0 01-2.06 0L4 7M20 7v10a2 2 0 01-2 2H6a2 2 0 01-2-2V7m16 0H4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </div>
      <h1>WarehouSe</h1>
      <p>Inventory Management System</p>
    </div>

    {{-- Login Card --}}
    <div class="login-card">

      {{-- Error Alert --}}
      @if ($errors->any())
        <div class="alert alert-error">
          <svg fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
          <div class="alert-content">
            <div class="alert-title">Login Failed</div>
            @foreach ($errors->all() as $error)
              <div class="alert-message">{{ $error }}</div>
            @endforeach
          </div>
        </div>
      @endif

      {{-- Login Form --}}
      <form method="POST" action="{{ route('login.submit') }}" class="login-form">
        @csrf

        {{-- Email Field --}}
        <div class="form-group">
          <label for="email" class="form-label">Email Address</label>
          <input
            type="email"
            id="email"
            name="email"
            value="{{ old('email') }}"
            placeholder="admin@example.com"
            required
            class="form-input @error('email') error @enderror"
          />
          @error('email')
            <div class="form-error">{{ $message }}</div>
          @enderror
        </div>

        {{-- Password Field --}}
        <div class="form-group">
          <label for="password" class="form-label">Password</label>
          <input
            type="password"
            id="password"
            name="password"
            placeholder="••••••••"
            required
            class="form-input @error('password') error @enderror"
          />
          @error('password')
            <div class="form-error">{{ $message }}</div>
          @enderror
        </div>

        {{-- Submit Button --}}
        <button type="submit" class="btn-login">
          Sign In
        </button>
      </form>

      {{-- Footer --}}
      <div class="login-footer">
        <strong>Demo Credentials</strong>
        Contact your administrator for login credentials
      </div>
    </div>

  </div>

</body>
</html>
