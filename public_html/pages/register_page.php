<?php require_once __DIR__ . '/../../includes/bootstrap.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Register</title>

    <!-- Favicon -->
    <link rel="icon" href="../assets/img/favicon.svg" media="(prefers-color-scheme: light)">
    <link rel="icon" href="../assets/img/favicon_white.svg" media="(prefers-color-scheme: dark)">

    <link rel="stylesheet" href="../assets/css/pages/login-register.css" />
    <?php include '../components/cookie-consent-include.php'; ?>
</head>

<body>
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
                <header class="header_register">
                    <h1 class="title">Create an account</h1>
                    <p class="subtitle">Skill issue incoming</p>
                </header>

                <form class="form" action="#" method="post">
                    <?php
                        require_once __DIR__ . '/../../includes/csrf.php';
                    ?>
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php echo htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>"
                    />

                    <div class="message message-slot"></div>

                    <label class="sr-only" for="username">Username</label>
                    <input
                        class="input"
                        id="username"
                        name="username"
                        type="text"
                        placeholder="username"
                        autocomplete="username"
                        required
                    />

                    <label class="sr-only" for="fname">First name</label>
                    <input
                        class="input_half"
                        id="fname"
                        name="fname"
                        type="text"
                        placeholder="First name"
                        autocomplete="given-name"
                    />

                    <label class="sr-only" for="infix">Infix</label>
                    <input
                        class="input_half"
                        id="infix"
                        name="infix"
                        type="text"
                        placeholder="Infix"
                        autocomplete="additional-name"
                    />

                    <label class="sr-only" for="lname">Last name</label>
                    <input
                        class="input"
                        id="lname"
                        name="lname"
                        type="text"
                        placeholder="Last name"
                        autocomplete="family-name"
                    />

                    <label class="sr-only" for="email">Email</label>
                    <input
                        class="input"
                        id="email"
                        name="email"
                        type="email"
                        placeholder="Email"
                        autocomplete="email"
                    />

                    <label class="sr-only" for="password">Password</label>
                    <input
                        class="input"
                        id="password"
                        name="password"
                        type="password"
                        placeholder="Password"
                        autocomplete="current-password"
                    />
                    <div class="terms">
                        <label class="terms_label">
                            <input type="checkbox" id="agree_terms" />
                            <span>
                                I agree to our
                            <a href="privacy.php" target="_blank" rel="noopener">
                                Policy
                            </a>
                            </span>
                        </label>
                    </div>

                    <button class="btn" type="submit">Register</button>
                </form>

                <div class="brand">
                    <img
                        class="brand_logo"
                        src="../assets/img/logo_with_green_edge.svg"
                        alt="Superhuman Benchmark Logo"
                    />
                </div>
            </div>
        </section>
    </main>



    <script src="../assets/js/pages/register_page.js"></script>
</body>
</html>
