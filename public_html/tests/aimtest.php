<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once INCLUDES_PATH . '/auth.php';
requireAuth('../pages/login_page.php');
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aim test</title>

    <!-- Favicon -->
    <link rel="icon" href="../assets/img/favicon.svg" media="(prefers-color-scheme: light)">
    <link rel="icon" href="../assets/img/favicon_white.svg" media="(prefers-color-scheme: dark)">

    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/components/footer.css">
    <link rel="stylesheet" href="../assets/css/components/nav_bar.css">
    <link rel="stylesheet" href="../assets/css/tests/aimtest.css">
    <script defer src="../assets/js/tests/aimtest.js"></script>
    <?php include '../components/cookie-consent-include.php'; ?>
  </head>
  <body>
    <?php $assetPath = '../'; include '../components/nav_bar/nav_bar.php'; ?>
    <div class="progress-bar">
      <div class="progress-bar-filling" id="progress-bar-filling"></div>
    </div>
    <div class="canvas" style="position: relative;">
      <canvas id="canvas" width="1000" height="500"></canvas>
      <div id="start-overlay" class="start-overlay">
        <span>Click to start</span>
      </div>
    </div>
    <div class="text">
      <h1>
        Aim Test
      </h1>
      <p>
        Click the target as accurately and quickly as you can. Each hit spawns a new target in a new position.
        The test ends after <strong>15</strong> targets. Your final score is your average time per target.
        If you struggle with the red targets, then you should make the <a class="link" href="blindtest.php" aria-label="Blind test">Colorblind test</a>.
      </p>

    </div>

    <script src="<?php echo $assetPath; ?>assets/js/theme.js" defer></script>
    <?php $assetPath = '../'; include '../components/footer/footer.php'; ?>
  </body>
</html>
