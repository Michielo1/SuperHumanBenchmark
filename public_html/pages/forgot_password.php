<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Forgot Password</title>

    <!-- Favicon -->
    <link rel="icon" href="../assets/img/favicon.svg" media="(prefers-color-scheme: light)">
    <link rel="icon" href="../assets/img/favicon_white.svg" media="(prefers-color-scheme: dark)">

    <link rel="stylesheet" href="../assets/css/pages/login-register.css" />
    <?php include '../components/cookie-consent-include.php'; ?>
</head>
<body>
    <main class="page">
        <section class="card" aria-label="Forgot password card">
            <div class="card_left">
                <img
                    class="illustration"
                    src="../assets/img/loginpage.png"
                    alt="Forgot password page image"
                    />
            </div>

            <div class="card_right">
                <header class="header_login">
                    <h1 class="title">Forgot Password?</h1>
                    <p class="subtitle">No worries, we'll send you reset instructions</p>
                </header>

                <form class="form" action="#" method="post">
                    <label class="sr-only" for="email">Email</label>
                    <input
                        class="input"
                        id="email"
                        name="email"
                        type="email"
                        placeholder="Email"
                        autocomplete="email"
                    />
                    
                    <div class="row">
                        <a class="link" href="login_page.php" aria-label="Back to login">Back to login</a>
                    </div>

                    <button class="btn" type="submit">Send Reset Link</button>
                </form>
                    <?php require_once __DIR__ . '/../../includes/csrf.php'; ?>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                

                <div class="brand">
                    <img class="brand_logo" src="../assets/img/logo_with_green_edge.svg" alt="Superhuman Benchmark Logo" />
                </div>
            </div>
        </section>
    </main>
    <script src="../assets/js/pages/forgot_password.js"></script>
</body>
</html>
