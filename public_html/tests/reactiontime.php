<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once INCLUDES_PATH . '/auth.php';
requireAuth('../pages/login_page.php');
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reaction time test</title>

    <!-- Favicon -->
    <link rel="icon" href="../assets/img/favicon.svg" media="(prefers-color-scheme: light)">
    <link rel="icon" href="../assets/img/favicon_white.svg" media="(prefers-color-scheme: dark)">

    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/components/footer.css">
    <link rel="stylesheet" href="../assets/css/components/nav_bar.css">
    <link rel="stylesheet" href="../assets/css/tests/reactiontime.css">
    <script defer src="../assets/js/tests/reactiontime.js"></script>
    <?php include '../components/cookie-consent-include.php'; ?>
  </head>
  <body>
    <?php $assetPath = '../'; include '../components/nav_bar/nav_bar.php'; ?>
    <div class="progress-bar">
      <div class="progress-bar-filling" id="progress-bar-filling"></div>
    </div>
    <img src="../assets/img/duck.png" class="duck" id="duck" alt="duck(bird)">
    <div class="colored-box" id="colored-box">
      <div class="text-in-box">
        Click to start
      </div>
      <div class="last-result"></div>
    </div>
    <div class="text">
      <h1>
        Reaction Time Test
      </h1>
      <p>This benchmark tests your reaction time. Your job is very simple, click on the colored box as quickly as possible when it turns <strong>green</strong>.
      If you press the colored box while it is not green you have to start over.
      There are a total of ten rounds, at the end your average performance will be calculated. Good luck! :D</p>
    </div>

    <script src="<?php echo $assetPath; ?>assets/js/theme.js" defer></script>
    <?php $assetPath = '../'; include '../components/footer/footer.php'; ?>
  </body>
</html>
