<div class="floater">
    <div class="auth-box panel">
        <h1>Forgot password</h1>
        <p class="muted">Enter your e-mail address and we will send you a link to reset your password.</p>
        <form id="forgot-form" class="site-form" method="POST" action="/api/account/forgot">
            <input type="hidden" name="CSRF" value="<?= \phpOMS\Uri\UriFactory::getQuery('$CSRF'); ?>">
            <div class="form-group">
                <label for="forgot-email">E-mail</label>
                <input id="forgot-email" type="email" name="email" autocomplete="email" required>
            </div>
            <button type="submit" class="featured">Send reset link</button>
        </form>
        <div class="form-links">
            <p><a href="/signin">Back to sign in</a></p>
        </div>
    </div>
</div>
