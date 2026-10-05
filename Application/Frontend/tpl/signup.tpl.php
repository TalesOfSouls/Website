<div class="floater">
    <div class="auth-box panel">
        <h1>Create account</h1>
        <p class="muted">One account for the game, the website and the community.</p>
        <form class="site-form" method="POST" action="/signup">
            <div class="form-group">
                <label for="signup-name">User name</label>
                <input id="signup-name" type="text" name="name" autocomplete="username" minlength="3" maxlength="32" required>
                <span class="form-hint">Visible to other players. 3 - 32 characters.</span>
            </div>
            <div class="form-group">
                <label for="signup-email">E-mail</label>
                <input id="signup-email" type="email" name="email" autocomplete="email" required>
            </div>
            <div class="form-group">
                <label for="signup-password">Password</label>
                <input id="signup-password" type="password" name="password" autocomplete="new-password" minlength="10" required>
                <span class="form-hint">At least 10 characters.</span>
            </div>
            <div class="form-group">
                <label for="signup-password2">Repeat password</label>
                <input id="signup-password2" type="password" name="password_repeat" autocomplete="new-password" minlength="10" required>
            </div>
            <div class="form-group">
                <label for="signup-birthday">Date of birth</label>
                <input id="signup-birthday" type="date" name="birthday" required>
            </div>
            <div class="form-group check">
                <input id="signup-terms" type="checkbox" name="terms" value="1" required>
                <label for="signup-terms">I accept the <a href="/terms">terms of service</a>, the <a href="/conduct">code of conduct</a> and the <a href="/privacy">privacy policy</a>.</label>
            </div>
            <div class="form-group check">
                <input id="signup-newsletter" type="checkbox" name="newsletter" value="1">
                <label for="signup-newsletter">Send me news about Tales of Souls (optional).</label>
            </div>
            <button type="submit" class="featured">Create account</button>
        </form>
        <div class="form-links">
            <p>Already have an account? <a href="/signin">Sign in</a></p>
        </div>
    </div>
</div>
