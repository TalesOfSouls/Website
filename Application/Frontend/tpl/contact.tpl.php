<div class="floater">
    <div id="welcome-section">
        <h1>Contact</h1>
    </div>
</div>

<div class="floater page-section">
    <div class="layout-split right">
        <div class="panel">
            <form id="contact-form" class="site-form wide" method="POST" action="/api/contact">
                <input type="hidden" name="CSRF" value="<?= \phpOMS\Uri\UriFactory::getQuery('$CSRF'); ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label for="contact-name">Name</label>
                        <input id="contact-name" type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="contact-email">E-mail</label>
                        <input id="contact-email" type="email" name="email" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="contact-topic">Topic</label>
                    <select id="contact-topic" name="topic">
                        <option>General question</option>
                        <option>Account &amp; support</option>
                        <option>Payments</option>
                        <option>Press</option>
                        <option>Business</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="contact-message">Message</label>
                    <textarea id="contact-message" name="message" required></textarea>
                </div>
                <div class="form-group check">
                    <input id="contact-privacy" type="checkbox" name="privacy" value="1" required>
                    <label for="contact-privacy">I agree that my data is processed to answer my request (see <a href="/privacy">privacy policy</a>).</label>
                </div>
                <button type="submit" class="featured">Send message</button>
            </form>
        </div>
        <div class="panel">
            <h2>Other channels</h2>
            <dl class="stat-list">
                <dt>Discord</dt><dd><a href="">Join our server</a></dd>
                <dt>E-mail</dt><dd>[contact e-mail]</dd>
                <dt>Response time</dt><dd>1 - 3 days</dd>
            </dl>
            <h3>Before you write</h3>
            <p>Many questions are already answered in the <a href="/faq">FAQ</a>.</p>
            <p>Bug reports and game suggestions belong on the <a href="/feedback">feedback board</a> so other players can see and vote on them.</p>
        </div>
    </div>
</div>
