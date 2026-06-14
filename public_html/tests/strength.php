<?php require_once __DIR__ . '/../../includes/bootstrap.php';
require_once INCLUDES_PATH . '/auth.php';
requireAuth('../pages/login_page.php');
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strength test</title>

    <!-- Favicon -->
    <link rel="icon" href="../assets/img/favicon.svg" media="(prefers-color-scheme: light)">
    <link rel="icon" href="../assets/img/favicon_white.svg" media="(prefers-color-scheme: dark)">

    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/components/footer.css">
    <link rel="stylesheet" href="../assets/css/components/nav_bar.css">
    <link rel="stylesheet" href="../assets/css/tests/strength.css">
    <script defer src="../assets/js/tests/strength.js"></script>
    <?php include '../components/cookie-consent-include.php'; ?>
  </head>
  <body>
    <?php require_once __DIR__ . '/../../includes/demo-banner.php'; ?>
    <?php $assetPath = '../'; include '../components/nav_bar/nav_bar.php'; ?>
    <img src="../assets/img/duck.png" class="duck" id="duck" alt="duck(bird)">
    <div class="colored-box" id="colored-box">
      <div class="text-in-box">
        Press any key as hard as you can!
      </div>
    </div>

    <script src="<?php echo $assetPath; ?>assets/js/theme.js" defer></script>
    <?php $assetPath = '../'; include '../components/footer/footer.php'; ?>
    <?php require_once __DIR__ . '/../../includes/demo-footer.php'; ?>
  </body>
</html>
