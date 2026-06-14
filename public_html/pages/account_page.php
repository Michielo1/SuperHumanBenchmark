<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once INCLUDES_PATH . '/auth.php';
requireAuth('login_page.php');

// Load current account
require_once CLASSES_PATH . '/Account.php';
$user = null;
$userId = getCurrentUserId();
if ($userId !== null) {
    $user = Account::findById((int)$userId);
}
$user_name = $user ? htmlspecialchars($user->getUsername(), ENT_QUOTES, 'UTF-8') : '';
$first_name = $user ? htmlspecialchars($user->getFirstName(), ENT_QUOTES, 'UTF-8') : '';
$infix_name = $user ? htmlspecialchars((string)$user->getInfix(), ENT_QUOTES, 'UTF-8') : '';
$last_name = $user ? htmlspecialchars($user->getLastName(), ENT_QUOTES, 'UTF-8') : '';
$email = $user ? htmlspecialchars($user->getEmail(), ENT_QUOTES, 'UTF-8') : '';
$full_name = $user ? htmlspecialchars($user->getFullName(), ENT_QUOTES, 'UTF-8') : 'User';

// Determine base path relative to current file location
if (!isset($basePath)) {
    // If we're in a pages directory, go up one level
    $basePath = (basename(dirname($_SERVER['PHP_SELF'])) === 'pages') ? '../' : '';
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <title>Account page</title>
        <meta name="description"
            content="About you" />
        <meta name="author" content="SuperHumanBenchmark" />
        <meta name="theme-color" content="#628141" />

        <!-- Favicon -->
        <link rel="icon" href="../assets/img/favicon.svg" media="(prefers-color-scheme: light)">
        <link rel="icon" href="../assets/img/favicon_white.svg" media="(prefers-color-scheme: dark)">

        <link rel="stylesheet" href="../assets/css/base.css">
        <link rel="stylesheet" href="../assets/css/pages/account_page.css">
        <link rel="stylesheet" href="../assets/css/components/sidebar.css">
        <link rel="stylesheet" href="../assets/css/components/footer.css">

        <script src="../assets/js/theme.js" defer></script>
        <script src="../assets/js/pages/sidebar.js" defer></script>
        <script src="../assets/js/pages/account_page.js" defer></script>
        <?php include '../components/cookie-consent-include.php'; ?>
    </head>

    <body>
        <?php require_once __DIR__ . '/../../includes/demo-banner.php'; ?>
        <div class="layout">
            <div class="sidebar_account">
                <!-- Sidebar -->
                <?php $assetPath = ''; include '../components/sidebar/sidebar.php'; ?>
            </div>

            <div class="content">
                <main class="page">
                    <div class="profile_content">
                        <div class="return_home">
                            <a href="<?php echo $basePath; ?>index.php" class="home_link">
                                <span class="arrow"></span>
                                Return Home
                            </a>
                            <h1>PROFILE</h1>
                        </div>

                        <section class="profile_card">
                            <div class="banner">
                                <div class="banner_image">
                                    <input type="file" id="banner_input" accept="image/*" hidden disabled>
                                </div>
                            </div>

                            <div class="profile_content">
                                <div class="profile_picture">
                                    <input type="file" id="profile_picture_input" accept="image/*" hidden disabled>
                                </div>

                                <div class="user_details">
                                    <span class="username"><?php echo $full_name; ?></span>
                                </div>
                            </div>

                            <form class="profile_settings">
                                <div class="profile_left">
                                    <h2>Personal Information</h2>
                                    <div class="information">
                                        <span class="label">First name</span>
                                        <input type="text" name="firstname" id="firstname" value="<?php echo $first_name; ?>">
                                    </div>
                                <?php require_once __DIR__ . '/../../includes/csrf.php'; ?>
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                    <div class="information">
                                        <span class="label">Infix</span>
                                        <input type="text" name="infix" id="infix" value="<?php echo $infix_name; ?>">
                                    </div>

                                    <div class="information_full">
                                        <span class="label">Surname</span>
                                        <input type="text" name="surname" id="surname" value="<?php echo $last_name; ?>">
                                    </div>

                                    <div class="information_full">
                                        <span class="label">Username</span>
                                        <input type="text" name="username" id="username" value="<?php echo $user_name; ?>">
                                    </div>

                                    <h2>Account Details</h2>
                                    <div class="information_full">
                                        <span class="label">Email</span>
                                        <input type="text" name="email" id="email" value="<?php echo $email; ?>" readonly>
                                    </div>
                                    <a class="link" href="forgot_password.php" aria-label="Forgot password">Change password</a>
                                </div>

                                <div class="line"></div>

                                <div class="profile_right">
                                    <h2>About You</h2>
                                    <div class="about_you">
                                        <textarea name="description" id="description"></textarea>
                                    </div>
                                </div>
                                <button type="submit" hidden></button>
                            </form>

                            <div class="jumpscare">
                                <div class="jumpscare_left">
                                    <h2>Jumpscares</h2>
                                    <div class="jumpscare_text">If you're scared turn jumpscares off.</div>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" id="jumpscare_click" checked>
                                    <span  class="slide_switch"></span>
                                </label>
                            </div>

                        </section>
                    </div>
                </main>
            </div>
        </div>

        <?php $assetPath = '../'; include '../components/footer/footer.php'; ?>

        <div class="scary" id="jumpscare" aria-hidden="true">
            <img id="jumpscare_img" src="../assets/img/boe.png" alt="enge foto">
            <audio src="../assets/audio/jumpscare_audio.mp3" id="jumpscare_audio"></audio>
        </div>

        <video class="footage" id="song_clip" muted playsinline>
            <source src="../assets/video/quantummechanics.mp4" type="video/mp4">
        </video>
        <?php require_once __DIR__ . '/../../includes/demo-footer.php'; ?>
    </body>
</html>
