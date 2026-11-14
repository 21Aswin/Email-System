<div class="auth-box">
    <div class="auth-box-header">
        <h2>Sign in to your account</h2>
        <p>Enter your credentials to access your mailbox</p>
    </div>

    <form method="POST" action="/login" class="auth-form" autocomplete="on">
        <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::getCsrfToken() ?>">

        <div class="form-group">
            <label for="email" class="form-label">Email Address</label>
            <div class="input-icon-wrapper">
                <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                <input type="email" id="email" name="email" class="form-input" placeholder="admin@novalyx.cloud" value="adminnkinflyx@gmail.com" required autofocus>
            </div>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <div class="input-icon-wrapper">
                <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                <input type="password" id="password" name="password" class="form-input has-toggle" placeholder="••••••••" value="password123" required>
                <button type="button" class="btn-password-toggle" onclick="togglePasswordVisibility('password', this)" title="Show password" aria-label="Show password">
                    <svg class="icon-eye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                    <svg class="icon-eye-off" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                </button>
            </div>
        </div>

        <div class="form-helper-row">
            <label class="checkbox-label">
                <input type="checkbox" name="remember" checked>
                <span>Remember this workstation</span>
            </label>
            <span class="seed-badge" title="Demo Seed Account">Demo: password123</span>
        </div>

        <button type="submit" class="btn btn-primary btn-block">
            Sign In
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </button>
    </form>

    <div class="auth-alt-action">
        <p>New to nkinfyx groups? <a href="/register" class="link-brand">Create an account</a></p>
    </div>
</div>
