<?php
/**
 * Demo Banner - Top bar + side bars shown in demo mode.
 * Displays "Demo Version" label and countdown to next reset.
 *
 * Include at the top of <body> in every page:
 *   <?php require_once INCLUDES_PATH . '/demo-banner.php'; ?>
 */

if (!defined('DEMO_MODE') || !DEMO_MODE) {
    return;
}
?>
<script>document.body.setAttribute('data-demo','1');</script>
<div class="demo-banner" id="demo-banner">
    <span class="demo-banner-left">
        &#9888;&nbsp; Demo Version &mdash; All data resets periodically
    </span>
    <span class="demo-banner-right">
        Next reset in: <strong id="demo-countdown"><?php echo DEMO_RESET_MINUTES; ?>:00</strong>
    </span>
</div>
<div class="demo-frame-left"><span>Demo</span></div>
<div class="demo-frame-right"><span>Demo</span></div>
<script>
(function() {
    var resetMinutes = <?php echo DEMO_RESET_MINUTES; ?>;
    var resetMs = resetMinutes * 60 * 1000;

    function readLastReset() {
        var stored = localStorage.getItem('demo_last_reset');
        return stored ? parseInt(stored, 10) : null;
    }

    function writeLastReset(ts) {
        localStorage.setItem('demo_last_reset', String(ts));
    }

    var lastReset = readLastReset();
    if (!lastReset) {
        lastReset = Date.now();
        writeLastReset(lastReset);
    }

    var el = document.getElementById('demo-countdown');
    function tick() {
        var elapsed = Date.now() - lastReset;
        var remaining = Math.max(0, resetMs - elapsed);
        if (remaining <= 0) {
            lastReset = Date.now();
            writeLastReset(lastReset);
            remaining = resetMs;
        }
        var totalSec = Math.ceil(remaining / 1000);
        var m = Math.floor(totalSec / 60);
        var s = totalSec % 60;
        el.textContent = m + ':' + (s < 10 ? '0' : '') + s;
    }

    tick();
    setInterval(tick, 1000);
})();
</script>