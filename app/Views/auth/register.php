<div class="auth-box">
    <div class="auth-box-header">
        <h2>Create an account</h2>
        <p>Get started with nkinfyx groups enterprise webmail</p>
    </div>

    <form method="POST" action="/register" class="auth-form" autocomplete="on">
        <input type="hidden" name="_csrf_token" value="<?= \App\Core\Session::getCsrfToken() ?>">

        <div class="form-group">
            <label for="display_name" class="form-label">Full Name</label>
            <div class="input-icon-wrapper">
                <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                <input type="text" id="display_name" name="display_name" class="form-input" placeholder="e.g. Sampath Kumar" required autofocus>
            </div>
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email Address</label>
            <div class="input-icon-wrapper">
                <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                <input type="email" id="email" name="email" class="form-input" placeholder="name@novalyx.cloud" required>
            </div>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <div class="input-icon-wrapper">
                <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                <input type="password" id="password" name="password" class="form-input" placeholder="At least 6 characters" minlength="6" required>
            </div>
        </div>

        <div class="form-group">
            <label for="password_confirm" class="form-label">Confirm Password</label>
            <div class="input-icon-wrapper">
                <svg class="input-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                <input type="password" id="password_confirm" name="password_confirm" class="form-input" placeholder="Repeat password" minlength="6" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block">
            Create Account
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </button>
    </form>

    <div class="auth-alt-action">
        <p>Already have an account? <a href="/login" class="link-brand">Sign in here</a></p>
    </div>
</div>
