<?php
/** @var \phpOMS\Views\View $this */
if (isset($this->response) && $this->response->header->status === \phpOMS\Message\Http\RequestStatusCode::R_200) {
    $this->response->header->status = \phpOMS\Message\Http\RequestStatusCode::R_404;
}
?>
<div class="floater">
    <div id="welcome-section">
        <span class="error-code">404</span>
        <h1>Lost in the mist</h1>
        <p>The page you are looking for doesn't exist (yet). Maybe it has been moved, or it is one of the many
        places we are still building.</p>
        <p><a class="button featured" href="/">Back to the start</a><a class="button" href="/game">Game overview</a></p>
    </div>
</div>
