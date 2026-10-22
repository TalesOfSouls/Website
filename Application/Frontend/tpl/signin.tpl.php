<div class="floater">
    <div class="auth-box panel">
        <h1>Sign in</h1>
        <form id="signin-form" class="site-form" method="POST" action="/api/account/signin">
            <input type="hidden" name="CSRF" value="<?= \phpOMS\Uri\UriFactory::getQuery('$CSRF'); ?>">
            <input type="hidden" name="redirect" value="<?= $this->printHtml($this->data['redirect'] ?? ''); ?>">
            <div class="form-group">
                <label for="signin-user">User name or e-mail</label>
                <input id="signin-user" type="text" name="user" autocomplete="username" required>
            </div>
            <div class="form-group">
                <label for="signin-password">Password</label>
                <input id="signin-password" type="password" name="password" autocomplete="current-password" required>
            </div>
            <div class="form-group check">
                <input id="signin-remember" type="checkbox" name="remember" value="1">
                <label for="signin-remember">Keep me signed in</label>
            </div>
            <button type="submit" class="featured">Sign in</button>
        </form>
        <div class="form-links">
            <p><a href="/forgot">Forgot your password?</a></p>
            <p>No account yet? <a href="/signup">Create one</a></p>
        </div>
    </div>
</div>
