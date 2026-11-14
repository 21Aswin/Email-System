<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= \App\Core\View::e($title ?? 'Sign In - Novalyx Cloud & Tech') ?></title>
    <link rel="icon" type="image/svg+xml" href="/assets/images/logo.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="auth-page">
    <div class="auth-card-wrapper">
        <div class="auth-brand-header">
            <img src="/assets/images/logo.svg" alt="Novalyx logo" class="auth-brand-logo">
            <h1 class="auth-brand-title">Novalyx Cloud</h1>
            <p class="auth-brand-subtitle">Next-Gen Enterprise Webmail</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                <span><?= \App\Core\View::e($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" role="alert">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                <span><?= \App\Core\View::e($success) ?></span>
            </div>
        <?php endif; ?>

        <div class="auth-card">
            <?= $content ?? '' ?>
        </div>

        <div class="auth-footer">
            <p>&copy; <?= date('Y') ?> Novalyx Cloud & Tech. All rights reserved.</p>
            <p class="auth-badge">Secure Enterprise Mail System</p>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(targetId, btn) {
            const input = document.getElementById(targetId);
            if (!input) return;
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            btn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            btn.title = isPassword ? 'Hide password' : 'Show password';
            const iconEye = btn.querySelector('.icon-eye');
            const iconEyeOff = btn.querySelector('.icon-eye-off');
            if (iconEye && iconEyeOff) {
                iconEye.style.display = isPassword ? 'none' : 'block';
                iconEyeOff.style.display = isPassword ? 'block' : 'none';
            }
        }
    </script>
</body>
</html>
