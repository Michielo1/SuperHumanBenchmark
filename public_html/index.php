<?php require_once __DIR__ . '/../includes/bootstrap.php'; $assetPath = ''; ?>
<!doctype html>
<html lang="en" data-theme="light">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>SuperHumanBenchmark</title>
    <meta name="description"
        content="Test your reaction time, memory, and cognitive skills with Super Human Benchmark - challenging tests with unexpected twists. Can you beat the benchmarks?" />
    <meta name="author" content="SuperHumanBenchmark" />
    <meta name="theme-color" content="#628141" />
    <meta name="robots" content="index, follow" />

    <!-- Favicon -->
    <link rel="icon" href="assets/img/favicon.svg" media="(prefers-color-scheme: light)">
    <link rel="icon" href="assets/img/favicon_white.svg" media="(prefers-color-scheme: dark)">

    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website" />
    <meta property="og:title" content="SuperHumanBenchmark - Test Your Limits" />
    <meta property="og:description"
        content="Test your reaction time, memory, and cognitive skills with Super Human Benchmark - challenging tests with unexpected twists. Can you beat the benchmarks?" />
    <meta property="og:site_name" content="SuperHumanBenchmark" />

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="SuperHumanBenchmark - Test Your Limits" />
    <meta name="twitter:description"
        content="Test your reaction time, memory, and cognitive skills with Super Human Benchmark - challenging tests with unexpected twists." />

    <link rel="stylesheet" href="<?php echo $assetPath; ?>assets/css/base.css">
    <link rel="stylesheet" href="<?php echo $assetPath; ?>assets/css/layout.css">
    <link rel="stylesheet" href="<?php echo $assetPath; ?>assets/css/components.css">
    <link rel="stylesheet" href="<?php echo $assetPath; ?>assets/css/pages/home.css">

</head>

<body>
  <!-- Navbar -->
  <?php include 'components/nav_bar/nav_bar.php'; ?>

    <!-- Hero -->
    <section class="hero">
        <h1 class="hero-title">SUPER HUMAN BENCHMARK</h1>
        <p class="hero-subtitle">Try a little harder next time :)</p>
    </section>

    <!-- Benchmarks -->
    <main class="container">
        <section class="benchmarks">
            <h2 class="benchmarks-title">BENCHMARKS</h2>
            <div class="benchmarks-grid">
                <article class="benchmark-card" onclick="window.location.href='tests/typingtest.php'" style="cursor: pointer;">
                    <h3 class="card-title">Typing test</h3>
                    <p class="card-subtitle">Prove your typing skills... No rigging</p>
                    <img
                        src="assets/img/typingtest.png"
                        alt="Typing test benchmark"
                        class="card-image"
                    />
                </article>
                <article class="benchmark-card" onclick="window.location.href='tests/reactiontime.php'" style="cursor: pointer;">
                    <h3 class="card-title">Reaction test</h3>
                    <p class="card-subtitle">You’re not as fast as you think... Try it</p>
                    <img
                        src="assets/img/reactiontest.png"
                        alt="Reaction test benchmark"
                        class="card-image"
                    />
                </article>
                <article class="benchmark-card" onclick="window.location.href='tests/aimtest.php'" style="cursor: pointer;">
                    <h3 class="card-title">Aim test</h3>
                    <p class="card-subtitle">Your aim isn’t as good as you think</p>
                    <img
                        src="assets/img/aimtest.png"
                        alt="Aim test benchmark"
                        class="card-image"
                    />
                </article>
                 <article class="benchmark-card" onclick="window.location.href='tests/blindtest.php'">
                    <h3 class="card-title">Colorblind test</h3>
                    <p class="card-subtitle">Try your vision against a very normal colorblind test</p>
                    <img
                        src="assets/img/colorblindtest.png"
                        alt="Colorblind test benchmark"
                        class="card-image"
                    />
                </article>
                <article class="benchmark-card" onclick="window.location.href='tests/strength.php'" style="cursor: pointer;">
                    <h3 class="card-title">Strength test</h3>
                    <p class="card-subtitle">Are you a different animal and the same beast</p>
                    <img
                        src="assets/img/strengthtest.png"
                        alt="Strength test benchmark"
                        class="card-image"
                    />
                </article>
            </div>
            <p class="benchmarks-subtitle">"You failed because you assumed it wouldn't change. That assumption was
                yours."</p>
        </section>
    </main>

    <!-- Footer -->
    <?php include 'components/footer/footer.php'; ?>

    <script src="<?php echo $assetPath; ?>assets/js/theme.js" defer></script>
</body>

</html>
