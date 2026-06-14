<?php require_once __DIR__ . '/../../includes/bootstrap.php';
require_once INCLUDES_PATH . '/auth.php';
requireAuth('../pages/login_page.php');
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Colorblind test</title>

    <!-- Favicon -->
    <link rel="icon" href="../assets/img/favicon.svg" media="(prefers-color-scheme: light)">
    <link rel="icon" href="../assets/img/favicon_white.svg" media="(prefers-color-scheme: dark)">

    <link rel="stylesheet" href="../assets/css/layout.css">
    <link rel="stylesheet" href="../assets/css/base.css">
    <link rel="stylesheet" href="../assets/css/components.css">
    <link rel="stylesheet" href="../assets/css/components/footer.css">
    <link rel="stylesheet" href="../assets/css/components/nav_bar.css">
    <link rel="stylesheet" href="../assets/css/tests/blindtest.css">
    <script defer src="../assets/js/tests/blindtest.js"></script>
    <script src="../assets/js/theme.js" defer></script>
    <?php include '../components/cookie-consent-include.php'; ?>
  </head>
  <body>
    <?php $assetPath = '../'; include '../components/nav_bar/nav_bar.php'; ?>
    <div class="text">
      <h1>
        Colorblind test
      </h1>
      <p>
        Welcome to the colorblind test! Here we will test your brains ability to comprehend the neural signals sent by your eyeballs. And the ability of your eyeballs to catch photons with the rods and cones on your retina and to translate those photons into neural signals based off of their wavelength.
        This test has 2 stages, if you fail even one of them you fail so make sure you understand them. <br>Stage 1: 470 colored letters will show up on the screen. You will have to enter a number based on the letters. You will start with the number one, then go through all the letters and change the number according to the letter.
        The following rules apply: If a letter is red, it does nothing, the number remains the same. If a letter is green add 3 times the index of the letter in the alphabet to the number, a = 1, z = 26. purple letters are cool. If a number is yellow the effect of the letter after the next letter is counted twice. If a letter is blue the next letters color to the next color in alphabetical order. If a letter is white you have to make a frog noise. orange letters are shy, if they are next to a cool letter they halve the number. If the color of a letter does not have a specific property and the color is multiple colors combined it has the effect of all of those colors. cerulean blue letters are NOT the same as blue letters. If a letters color has a HEX code that starts with #A1, then multiply the number by the amount of times you have read the letter r so far. The letter Q is never cool.
        Whitespaces can be ignored. Frogs dont live in the sea, a frog noise will remove any nearby c's. According to NASA, Kepler-186f is a exoplanet that is roughly the size of earth and in the habitable zone of a red dwarf star. If there are any plants on this planet the stars red-wavelength photons could cause the plants there to be red instead of green like they are here on earth. That doesn't have anything to do with the quiz I just felt like throwing a fun fact in here. The letter s is short for supercalifragilisticexpialidocious, any s in the text needs to be expanded to that word, all letters of the word will be the same color as that s. The letter f is unnecessary, any f in the text has to be removed. If there is a Yellow Y, the final answer will be 23 no matter what the other letters are. The letter L is late, it should be moved 3 positions to the left where it was supposed to be originally. The letter W is called a double-v not a double-u that makes no sense, if you are trying to tell me that uu looks more like a w than vv then you dont have to take this test, you are blind. not colorblind, just blind. <br>
        Stage 2: This is an easier stage. You will be shown a color and 2 options for the name of the color, all you have to do is choose the correct name of the color.
      </p>
    </div>
    <button class="button" onclick="startTest()"> Click here to start the test!</button>

    <div class="text first-stage" id="first-stage">
      <h1> Stage 1 </h1>
      <p>
        OlEUKVPPtl
        UxBMKViNUf
        oelqJohsLc
        RPCXpxXAuY
        GTNeTmwGBD
        zcZJQVUHDV
        vWOHHjYszB
        hDPTYiiwjP
        wqPaCIxfXF
        jIUSZVZCMU
        LqDpUCsqbD
        TpvdKajYyV
        htkQaxcPrX
        CTCTUVdgBE
        bjxRsxmKjW
        kbhPxVcLBm
        avpOmkIEVx
        WeCrrbBWGc
        WyrQsQPJhm
        FYdGqANzwP
        NrbMVUdVwu
        vGrQUhYRqk
        vIuctVRqpk
        pLTUHkrlFi
        oHTpczIXcx
        URrULDCwxj
        JXpSICEaxY
        FjPiayLwfp
        xvXhkqDqrG
        ozdCepAYTl
        NGsfxvMBBv
        aREhEbzPRO
        LUhyneiEdA
        WIEVaffRap
        klezYcykjV
        gGynFwkbvC
        WRoblUvdNC
        ALFyhYVfDn
        qURdUZQxpy
        yFvxxIQrZJ
        IMELXSHrno
        iHlVZMOeZE
        BSVBgBbyqz
        cgasHYJSZV
        qvrxrvSENS
        wpOZHAIMUo
        VVllRCJviO
      </p>
      <div class="center-child"><input id="stage1input"></input></div>
      <div class="center-child"><button class="button" onclick="submitStage1()">Submit answer</button></div>
    </div>
    <div class="text stage2" id="stage2">
      <h1>Stage 2</h1>
      <p>What is the name of this color?</p>
      <div class="color" id="color"></div>
      <button class="button" onclick="option(1)" id="option1">option1</button>
      <button class="button" onclick="option(2)" id="option2">option2</button>
    </div>
    <div class="text fail" id="fail">
      <h1>You failed :(</h1>
      <p>Unfortunately you failed the test. If you think you should've passed you can start the test again to give it another try.</p>
    </div>
    <div class="text success" id="success">
      <h1>You passed! :D</h1>
      <p>Congratulations! You are not colorblind, I might be though.</p>
    </div>
    <?php $assetPath = '../'; include '../components/footer/footer.php'; ?>
  </body>
</html>
