<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Reset Password</title>

    <!-- Favicon -->
    <link rel="icon" href="../assets/img/favicon.svg" media="(prefers-color-scheme: light)">
    <link rel="icon" href="../assets/img/favicon_white.svg" media="(prefers-color-scheme: dark)">

    <link rel="stylesheet" href="../assets/css/pages/login-register.css" />
    <?php include '../components/cookie-consent-include.php'; ?>
</head>
<body>
    <?php require_once __DIR__ . '/../../includes/demo-banner.php'; ?>
    <main class="page">
        <section class="card" aria-label="Reset password card">
            <div class="card_left">
                <img
                    class="illustration"
                    src="../assets/img/loginpage.png"
                    alt="Reset password page image"
                    />
            </div>

            <div class="card_right">
                <header class="header_login">
                    <h1 class="title">Reset Password</h1>
                    <p class="subtitle">Enter your new password below</p>
                </header>

                <form class="form" action="#" method="post">
                    <label class="sr-only" for="password">New Password</label>
                    <input
                        class="input"
                        id="password"
                        name="password"
                        type="password"
                        placeholder="New Password"
                        autocomplete="new-password"
                    />
                    <?php require_once __DIR__ . '/../../includes/csrf.php'; ?>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                    <label class="sr-only" for="confirm_password">Confirm Password</label>
                    <input
                        class="input"
                        id="confirm_password"
                        name="confirm_password"
                        type="password"
                        placeholder="Confirm Password"
                        autocomplete="new-password"
                    />

                    <div class="row">
                        <a class="link" href="login_page.php" aria-label="Back to login">Back to login</a>
                    </div>

                    <button class="btn" type="submit">Reset Password</button>
                </form>

                

                <div class="brand">
                    <img class="brand_logo" src="../assets/img/logo_with_green_edge.svg" alt="Superhuman Benchmark Logo" />
                </div>
            </div>
        </section>
    </main>
    <script src="../assets/js/pages/reset_password.js"></script>
    <?php require_once __DIR__ . '/../../includes/demo-footer.php'; ?>
</body>
</html>
