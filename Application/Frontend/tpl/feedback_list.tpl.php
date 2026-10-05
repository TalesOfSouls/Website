<?php
// @todo Sample feedback until the feedback board is connected to the database
$feedbacks = [
    ['2026-10-02', 'planned', 'Planned', 'Suggestion', 'Add a transmog system for all armor types', 128],
    ['2026-09-30', 'progress', 'In progress', 'Bug', 'Pets sometimes get stuck on stairs', 64],
    ['2026-09-28', 'open', 'Open', 'Suggestion', 'Guild bank permissions per rank', 57],
    ['2026-09-25', 'done', 'Done', 'Website', 'Show item rarity colors in the database', 41],
    ['2026-09-21', 'open', 'Open', 'Balance', 'Berserker bleed damage too strong in 2v2', 33],
    ['2026-09-18', 'closed', 'Declined', 'Suggestion', 'Flying mounts in the open world', 29],
    ['2026-09-12', 'done', 'Done', 'Bug', 'Auction house sorting by price is reversed', 18],
];
?>
<div class="floater">
    <div id="welcome-section">
        <h1>Feedback</h1>
    </div>
</div>

<div id="ladder" class="page-section">
    <form class="floater filter-bar" method="GET" action="/feedback">
        <input type="text" name="q" placeholder="Search feedback">
        <select name="type">
            <option value="">Any type</option>
            <option>Bug</option>
            <option>Suggestion</option>
            <option>Balance</option>
            <option>Website</option>
        </select>
        <select name="state">
            <option value="">Any state</option>
            <option>Open</option>
            <option>Planned</option>
            <option>In progress</option>
            <option>Done</option>
            <option>Declined</option>
        </select>
        <button type="submit">Filter</button>
        <a class="button featured" href="/feedback/create">Submit feedback</a>
    </form>

    <div class="floater">
        <table class="content-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>State</th>
                    <th>Type</th>
                    <th class="wf-100">Title</th>
                    <th>Votes</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($feedbacks as $feedback) : ?>
                <tr>
                    <td><?= $feedback[0]; ?></td>
                    <td><span class="badge <?= $feedback[1]; ?>"><?= $feedback[2]; ?></span></td>
                    <td><?= $feedback[3]; ?></td>
                    <td><?= $feedback[4]; ?></td>
                    <td><?= $feedback[5]; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
