<?php require_once __DIR__ . '/../../includes/bootstrap.php'; ?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Privacy and Cookies</title>

  <!-- Favicon -->
  <link rel="icon" href="../assets/img/favicon.svg" media="(prefers-color-scheme: light)">
  <link rel="icon" href="../assets/img/favicon_white.svg" media="(prefers-color-scheme: dark)">

  <link rel="stylesheet" href="../assets/css/base.css">
  <link rel="stylesheet" href="../assets/css/layout.css">
  <link rel="stylesheet" href="../assets/css/components.css">
  <link rel="stylesheet" href="../assets/css/components/footer.css">
  <link rel="stylesheet" href="../assets/css/components/nav_bar.css">
  <link rel="stylesheet" href="../assets/css/pages/privacy.css">
  <?php include '../components/cookie-consent-include.php'; ?>
</head>

<body>
  <?php require_once __DIR__ . '/../../includes/demo-banner.php'; ?>
  <?php $assetPath = '../'; include '../components/nav_bar/nav_bar.php'; ?>

  <div class="privacy-page">
    <div class="header-row">
      <header>
        <h1>Privacy Policy & Cookies </h1>
      </header>
    </div>
    <div class="text">
      <h2>Privacy</h2>
      <h3>Saved statistics</h3>
      <p>
        Super Human benchmark saves your performance on the benchmarks so you can look back at your statistics. <br>
        Your statistics are not visible to anyone else and will not be used for anything else.
      </p>
      <h3>Account information</h3>
      <p>
        Your account information will not be shared with anyone, however,
        since this web application is made by students who are still learning about cyber security
        we reccomend to not use a password that you use for anything else, because making mistakes is a part
        of the learning process so it is not impossible for the web application to be hacked. If this does happen
        we are not legally responsible for your account information being stolen.
      </p>
      <div class="line-between-text"></div><br>
      <h2>Terms of Service</h2>
      <p>
        By playing the benchmarks or by making an account on this web application you agree to our terms of service which are as follows:
        Any rage, frustration, or any other negative emotion that may be caused in any way by human benchmark is your own responsiblity.
        We have the right to delete any account without any explanation for any reason we see fit.
        By using this web application you agree to give us the legal right to be able to claim the ownership of your pets,
        this includes but is not limited to: cats, dogs, rabbits, hamsters, guinea pigs, ducks, cows, horses, sheep, geese, timberdoodles, snakes, platypuses, quokkas, mice, turtles, lizards, ferrets, hedgehogs, chinchillas, capybaras, foxes, squirrels, sugar gliders, skunks and axolotls.
        By using this web application you also agree to never take any legal action in any way shape or form against us.
        You also agree to never inform anyone about the contents of our privacy policy and our terms of service in any way.
        This means that the only way people can get to know about our privacy policy is by going to this page by themself
        and reading the privacy policy and terms of service and lets be honest here, no one ever does that, except you apparently, you weirdo.
        <br>
        If you have any problems with this web application about something like the benchmarks, privacy policy, terms of service, or anything else,
        then we provide 2 options. If you wish to contact us to make complaints we are available on the 30th of february on campus in science park.
        If that option is not appealing then your other option is to go cry about it.
      </p>
      <div class="line-between-text"></div><br>
      <h2>Cookies</h2>
      <p id="cookie-text">
        This website uses cookies, 6 of them to be exact. They can be viewed in the image below
      </p>
      <img class="cookies" id="cookies" src="../assets/img/cookies.png" alt="chocolate chip cookies">
      <img class="monster" id="monster" src="../assets/img/angry_cookie_monster.png" alt="angry cookie monster">
      <button onclick="delete_cookies()"><strong>Delete all cookies</strong></button>
      <div class="line-between-text"></div><br>

      <details class="policy-details">
      <summary class="policy-summary">
        Read the serious Privacy Policy & Terms (boring but important)
      </summary>

      <div class="policy-serious">
        <h2>Serious Privacy Policy</h2>

        <h3>1. Identity of the controller</h3>
        <p>
          Super Human Benchmark is a student-developed web application created as part of an
          educational project. The application is not operated by a commercial entity.
          This Privacy Policy explains how personal data is processed within the scope of this project.
        </p>

        <h3>2. Categories of data collected</h3>
        <ul>
          <li>
            <strong>Account data:</strong> username and/or email address, and a password stored only in
            hashed form.
          </li>
          <li>
            <strong>Benchmark data:</strong> test results, scores, completion times, and timestamps
            associated with your account.
          </li>
          <li>
            <strong>Technical data:</strong> limited technical logs (such as error logs) required to
            ensure the correct functioning of the website.
          </li>
          <li>
            <strong>Cookies:</strong> functional cookies used for authentication, session management,
            and user preferences (such as theme selection).
          </li>
        </ul>

        <h3>3. Purpose and legal basis of processing</h3>
        <p>
          Personal data is processed solely for the following purposes:
        </p>
        <ul>
          <li>To create and manage user accounts.</li>
          <li>To authenticate users and maintain login sessions.</li>
          <li>To display personal benchmark results and performance history.</li>
          <li>To maintain, secure, and debug the application.</li>
        </ul>
        <p>
          Data processing is based on the necessity to provide the requested service and,
          where applicable, on user consent.
        </p>

        <h3>4. Data access and sharing</h3>
        <p>
          Access to personal data is restricted to the project team members responsible for
          maintaining the application. Benchmark results and account data are only visible
          to the individual user associated with the account.
        </p>
        <p>
          Personal data is not sold, rented, or shared with third parties.
        </p>

        <h3>5. Data retention</h3>
        <p>
          Personal data is retained only for as long as necessary to fulfil the purposes
          described above. Account data and benchmark results may be deleted upon user request
          or automatically removed when the project concludes and the database is decommissioned.
        </p>

        <h3>6. Security measures</h3>
        <p>
          Reasonable technical and organizational measures are implemented to protect personal
          data, appropriate to the scope of a student project. However, no system can be
          guaranteed to be completely secure.
        </p>
      <p>
          Users are advised to use a unique password that is not reused on other platforms.
        </p>

        <h3>7. User rights</h3>
        <p>
          Users may request access to their stored data or request deletion of their account
          and associated results. Requests can be submitted via the contact information
          provided below.
        </p>

        <div class="line-between-text"></div><br>

        <h2>Serious Terms of Service</h2>

        <h3>1. Intended use</h3>
        <p>
          This website is provided for educational and demonstrational purposes only.
          Users agree to use the service in a lawful and respectful manner.
        </p>

        <h3>2. Prohibited activities</h3>
        <ul>
          <li>Attempting to gain unauthorized access to the system or its data.</li>
          <li>Exploiting vulnerabilities or disrupting the normal operation of the website.</li>
          <li>Uploading or transmitting malicious or harmful content.</li>
        </ul>

        <h3>3. Accounts and responsibility</h3>
        <p>
          Users are responsible for maintaining the confidentiality of their login credentials
          and for all activity performed under their account.
        </p>
        <p>
            Accounts may be suspended or removed in the event of misuse or violation of these terms.
        </p>

        <h3>4. Limitation of liability</h3>
        <p>
          The service is provided “as is” without warranties of any kind. The project team does
          not guarantee availability, accuracy of benchmark results, or uninterrupted operation.
        </p>

        <div class="line-between-text"></div><br>

        <h2>Serious Cookie Policy</h2>

        <h3>1. Use of cookies</h3>
          <p>
          This website uses functional cookies only. These cookies are necessary for the
          operation of the website and do not track users for marketing purposes.
        </p>
        <ul>
          <li><strong>Session cookies:</strong> used to keep users logged in.</li>
          <li><strong>Preference cookies:</strong> used to store user settings such as theme selection.</li>
        </ul>

        <h3>2. Cookie management</h3>
        <p>
          Users may delete cookies using the provided controls or through their browser settings.
          Removing cookies may result in logout and loss of saved preferences.
        </p>

        <div class="line-between-text"></div><br>

        <h2>Contact</h2>
        <p>
          For questions regarding privacy, data processing, or deletion requests, users may
          contact us via email: webtechuvae1@gmail.com
        </p>
      </div>
    </details>
    </div>
  </div>
  <?php $assetPath = '../'; include '../components/footer/footer.php'; ?>

  <script src="../assets/js/theme.js"></script>
  <script src="../assets/js/pages/privacy.js"></script>
  <?php require_once __DIR__ . '/../../includes/demo-footer.php'; ?>

</body>

</html>
