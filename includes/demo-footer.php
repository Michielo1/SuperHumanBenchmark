<?php
/**
 * Demo Footer - Bottom bar shown in demo mode.
 * Displays university attribution and GitHub link.
 *
 * Include at the bottom of <body> in every page:
 *   <?php require_once INCLUDES_PATH . '/demo-footer.php'; ?>
 */

if (!defined('DEMO_MODE') || !DEMO_MODE) {
    return;
}
?>
<div class="demo-footer">
    University of Amsterdam WebTech Project &mdash;
    <a href="https://github.com/Michielo1/SuperHumanBenchmark" target="_blank" rel="noopener">GitHub</a>
</div>
