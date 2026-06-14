<?php require_once __DIR__ . '/../../includes/bootstrap.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login</title>

    <!-- Favicon -->
    <link rel="icon" href="../assets/img/favicon.svg" media="(prefers-color-scheme: light)">
    <link rel="icon" href="../assets/img/favicon_white.svg" media="(prefers-color-scheme: dark)">

    <link rel="stylesheet" href="../assets/css/pages/login-register.css" />
    <?php include '../components/cookie-consent-include.php'; ?>
</head>
<body>
    <?php require_once __DIR__ . '/../../includes/demo-banner.php'; ?>
    <main class="page">
        <section class="card" aria-label="Login card">
            <div class="card_left">
                <img
                    class="illustration"
                    src="../assets/img/loginpage.png"
                    alt="Login page image"
                    />
            </div>

            <div class="card_right">
                <header class="header_login">
                    <h1 class="title">Welcome Back</h1>
                    <p class="subtitle">Please, just do better :)</p>
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
                    <?php require_once __DIR__ . '/../../includes/csrf.php'; ?>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                    <label class="sr-only" for="password">Password</label>
                    <input
                        class="input"
                        id="password"
                        name="password"
                        type="password"
                        placeholder="Password"
                        autocomplete="current-password"
                    />

                    <div class="row">
                        <a class="link" href="register_page.php" aria-label="Create account">Create account</a>
                        <a class="link" href="forgot_password.php" aria-label="Forgot password">Forgot password</a>
                    </div>

                    <button class="btn" type="submit">Login</button>
                </form>

                <?php if (defined('DEMO_MODE') && DEMO_MODE): ?>
                <div class="demo-credentials">
                    <h3>Demo Accounts</h3>
                    <table class="demo-creds-table">
                        <tr>
                            <td><strong>User</strong></td>
                            <td><?php echo DEMO_USER_EMAIL; ?></td>
                            <td><?php echo DEMO_USER_PASSWORD; ?></td>
                        </tr>
                        <tr>
                            <td><strong>Admin</strong></td>
                            <td><?php echo DEMO_ADMIN_EMAIL; ?></td>
                            <td><?php echo DEMO_ADMIN_PASSWORD; ?></td>
                        </tr>
                    </table>
                </div>
                <?php endif; ?>

                <div class="brand">
                    <img class="brand_logo" src="../assets/img/logo_with_green_edge.svg" alt="Superhuman Benchmark Logo" />
                </div>
            </div>
        </section>
    </main>



    <script src="../assets/js/pages/login_page.js?v=2"></script>
    <?php require_once __DIR__ . '/../../includes/demo-footer.php'; ?>
</body>
</html>
