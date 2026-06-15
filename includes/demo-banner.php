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
<style>
.demo-banner { position: fixed; top: 0; left: 0; right: 0; z-index: 99999; display: flex; align-items: center; justify-content: space-between; padding: 0 44px; height: 44px; background: #1a1a19; color: #f5f5f5; font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; font-size: 13px; line-height: 44px; border-bottom: 2px solid #628141; }
.demo-banner-left { white-space: nowrap; }
.demo-banner-right { white-space: nowrap; }
.demo-banner-right strong { font-weight: 700; color: #628141; }
.demo-footer { position: fixed; bottom: 0; left: 0; right: 0; z-index: 99999; display: flex; align-items: center; justify-content: center; padding: 0 44px; height: 40px; background: #1a1a19; color: #f5f5f5; font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; font-size: 12px; line-height: 40px; border-top: 2px solid #628141; }
.demo-footer a { color: #628141; text-decoration: none; margin-left: 4px; }
.demo-footer a:hover { text-decoration: underline; }
.demo-frame-left { position: fixed; top: 0; left: 0; width: 44px; height: 100%; z-index: 99998; background: #1a1a19; border-right: 2px solid #628141; display: flex; align-items: center; justify-content: center; }
.demo-frame-left span { writing-mode: vertical-rl; transform: rotate(180deg); font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; color: #628141; font-weight: 600; }
.demo-frame-right { position: fixed; top: 0; right: 0; width: 44px; height: 100%; z-index: 99998; background: #1a1a19; border-left: 2px solid #628141; display: flex; align-items: center; justify-content: center; }
.demo-frame-right span { writing-mode: vertical-rl; transform: rotate(180deg); font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; color: #628141; font-weight: 600; }
@media (max-width: 900px) { .demo-frame-left, .demo-frame-right { display: none; } .demo-banner { padding: 0 16px; } }
@media (max-width: 600px) { .demo-banner { flex-direction: column; height: auto; padding: 6px 12px; line-height: 1.4; text-align: center; } }
.demo-credentials { margin: 16px 0; padding: 16px; background: var(--surface, #fff); border: 1px solid var(--border, #e0e0e0); border-radius: 6px; }
.demo-credentials h3 { margin: 0 0 10px 0; font-size: 14px; color: var(--text, #1a1a19); }
.demo-creds-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.demo-creds-table td { padding: 4px 8px; color: var(--text, #1a1a19); }
.demo-creds-table td:first-child { font-weight: 600; width: 70px; }
</style>
<script>
(function() {
  document.body.style.padding = '114px 46px 40px 46px';
  document.body.style.margin = '0';
  document.addEventListener('DOMContentLoaded', function() {
    var topnav = document.querySelector('.topnav');
    if (topnav) { topnav.style.top = '44px'; topnav.style.left = '46px'; topnav.style.right = '46px'; topnav.style.width = 'auto'; }
    var devider = document.querySelector('.devider');
    if (devider) { devider.style.top = '114px'; devider.style.left = '46px'; devider.style.right = '46px'; devider.style.width = 'auto'; }
    var hamburger = document.querySelector('.hamburger_menu');
    if (hamburger) hamburger.style.top = '56px';
    var hero = document.querySelector('.hero');
    if (hero) hero.style.marginTop = '134px';
  });
})();
</script>
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