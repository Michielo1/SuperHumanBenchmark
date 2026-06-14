<?php require_once __DIR__ . '/../../includes/bootstrap.php'; ?>
<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>About Us - Super Human Benchmark</title>
    <meta name="description"
        content="Meet the team behind Super Human Benchmark - a student project focused on measuring performance through superhuman tests." />
    <meta name="author" content="SuperHumanBenchmark" />
    <meta name="theme-color" content="#628141" />

    <!-- Favicon -->
    <link rel="icon" href="../assets/img/favicon.svg" media="(prefers-color-scheme: light)">
    <link rel="icon" href="../assets/img/favicon_white.svg" media="(prefers-color-scheme: dark)">

    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website" />
    <meta property="og:title" content="About Us - SuperHumanBenchmark" />
    <meta property="og:description"
        content="Meet the team behind Super Human Benchmark - a student project focused on measuring performance through superhuman tests." />
    <meta property="og:site_name" content="SuperHumanBenchmark" />

    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/pages/about_us.css">
    <?php include '../components/cookie-consent-include.php'; ?>
</head>

<body>
    <!-- Navbar -->
    <?php $assetPath = '../'; include '../components/nav_bar/nav_bar.php'; ?>

    <main class="about-page">
        <div class="about-container">

            <section class="about-header">
                <h1 class="about-header_title">ABOUT US</h1>
                <p class="about-header_subtitle">
                    the students behind the superhuman benchmark
                </p>
            </section>

            <section class="about-grid">
                <article class="about-panel">
                    <h2 class="about-panel__title">What is Super Human Benchmark?</h2>
                    <p class="about-panel__text">
                        Super Human Benchmark is a student project focused on measuring performance through
                        SUPERHUMAN tests and clear scoring.
                    </p>
                    <p class="about-panel__text">
                        This page introduces the team responsible for designing, developing, and evaluating
                        the benchmark.
                    </p>
                </article>

                <aside class="about-panel about-panel--accent">
                    <h2 class="about-panel__title">Quick facts</h2>
                    <ul class="about-stats">
                        <li>
                            <div class="about-stats__label">Team size</div>
                            <div class="about-stats__value">5 members</div>
                        </li>
                        <li>
                            <div class="about-stats__label">Used Tech</div>
                            <div class="about-stats__value">HTML, CSS, JS, PHP, SQL</div>
                        </li>
                    </ul>
                </aside>
            </section>

            <section class="about-team">
                <div class="about-team__header">
                    <h2 class="about-team__title">Team</h2>
                </div>

                <div class="about-teamgrid">
                    <article class="about-member">
                        <div class="about-member__img">
                            <img src="../assets/img/Anthony boek.jpg" alt="Anthony">
                        </div>
                        <p class="about-member__desc">
                            Anthony - Plant 2 - HELP, I'M NOT GETTING PAID!
                        </p>
                    </article>

                    <article class="about-member">
                        <div class="about-member__img">
                            <img src="../assets/img/michiel.png" alt="Michiel">
                        </div>
                        <p class="about-member__desc">
                            Michiel - Backend nerd - I don't have a life
                        </p>
                    </article>

                    <article class="about-member">
                        <div class="about-member__img">
                            <img src="../assets/img/sean.jpeg" alt="Sean">
                        </div>
                        <p class="about-member__desc">
                            Sean - Background character - Doing everything that I can
                        </p>
                    </article>

                    <article class="about-member">
                        <div class="about-member__img">
                            <img src="../assets/img/david.png" alt="David">
                        </div>
                        <p class="about-member__desc">
                            David - Head plant - CSS hater
                        </p>
                    </article>

                    <article class="about-member">
                        <div class="about-member__img">
                            <img src="../assets/img/nikki.jpg" alt="Svens cat">
                        </div>
                        <p class="about-member__desc">
                            Sven - Plant 2 - I'm not that interesting, look at this picture of my cat instead:D
                        </p>
                    </article>
                </div>
            </section>

        </div>
    </main>
    <script src="<?php echo $assetPath; ?>assets/js/theme.js" defer></script>
    <?php $assetPath = '../'; include '../components/footer/footer.php'; ?>
</body>

</html>
