<!-- Footer styles and fonts are loaded in main page -->
<?php
// Determine base path relative to current file location
if (!isset($basePath)) {
    // If we're in a pages or tests directory, go up one level
    $currentDir = basename(dirname($_SERVER['PHP_SELF']));
    $basePath = (($currentDir === 'pages') || ($currentDir === 'tests')) ? '../' : '';
}
?>
<footer class="footer">
        <div class="footer_container">
            <div class="footer_logo">
                <img class="light-logo"
                    src="<?php echo isset($assetPath) ? $assetPath : $basePath; ?>assets/img/logo_black_text.svg"
                    alt="logo">

                <img class="dark-logo"
                    src="<?php echo isset($assetPath) ? $assetPath : $basePath; ?>assets/img/whitelogo.svg"
                    alt="logo">
            </div>
            <div class="footer_columns">
                <div class="footer_column">
                    <h3>Benchmarks</h3>
                    <a href="<?php echo $basePath; ?>tests/typingtest.php">Typing Test</a>
                    <a href="<?php echo $basePath; ?>tests/reactiontime.php">Reaction Test</a>
                    <a href="<?php echo $basePath; ?>tests/aimtest.php">Aim Test</a>
                    <a href="<?php echo $basePath; ?>tests/blindtest.php">Colorblind Test</a>
                    <a href="<?php echo $basePath; ?>tests/strength.php">Strength Test</a>
                </div>

                <div class="footer_column">
                    <h3>Account</h3>
                    <a href="<?php echo $basePath; ?>pages/account_page.php">Profile</a>
                    <a href="<?php echo $basePath; ?>pages/user_dashboard.php">Dashboard</a>
                    <a href="<?php echo $basePath; ?>pages/scores.php">Score</a>
                </div>

                <div class="footer_column">
                    <h3>About Us</h3>
                    <a href="<?php echo $basePath; ?>pages/about_us.php">What is this</a>
                    <a href="<?php echo $basePath; ?>pages/about_us.php">Unpaid interns</a>
                </div>

                <div class="footer_column">
                    <h3>More Information</h3>
                    <a href="<?php echo $basePath; ?>pages/privacy.php">Privacy Policy</a>
                    <a href="<?php echo $basePath; ?>pages/privacy.php">Terms of Service</a>
                    <a href="<?php echo $basePath; ?>pages/privacy.php">Cookie Policy</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Cookie consent popup (only shown when user has not chosen) -->
    <div id="cookie-consent-popup" style="display:none;position:fixed;bottom:20px;right:20px;z-index:10000;background:#fff;border:1px solid #ddd;padding:16px;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,0.08);max-width:320px;">
        <div style="font-weight:700;margin-bottom:8px;">Cookie preferences</div>
        <div style="font-size:13px;margin-bottom:12px;">We use optional analytics cookies to help improve the site. Analytics are disabled by default. You can opt in or out at any time.</div>
        <div style="text-align:right;">
            <button id="cookie-consent-decline" style="margin-right:8px;padding:6px 10px;border:none;background:#eee;border-radius:4px;">Decline</button>
            <button id="cookie-consent-accept" style="padding:6px 10px;border:none;background:#2b8fdd;color:#fff;border-radius:4px;">Accept</button>
        </div>
    </div>

    <!-- Cookie consent JS -->
    <script src="<?php echo isset($assetPath) ? $assetPath : $basePath; ?>assets/js/cookie-consent.js" defer></script>
