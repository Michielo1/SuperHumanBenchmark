// privacy initialization

let button_counter = 0;
let size = 1;

function delete_cookies() {
    const cookies = document.getElementById("cookies");
    const monster = document.getElementById("monster");
    const text = document.getElementById("cookie-text");
    if (button_counter == 0) {
        cookies.style.opacity = 0;
        monster.style.opacity = 1;
        text.innerHTML = "This website uses cookies, well it used to untill you deleted them, look at what you did to cookiemonster. You crushed his hopes and dreams, despite what his name might suggest, it's clear that <strong>YOU</strong> are the real monster here. Your actions are unforgivable and cookiemonster will not forget. You have doomed yourself to eternal suffering. He will not allow you to ever experience a moment of peace ever again.";
    } else if (button_counter == 1) {
        monster.style.transition = 0.3;
        size = 1.25;
    } else if (button_counter == 2) {
        size = 1.5
    } else {
        monster.style.transition = 0;
        monster.style.zIndex = 10000000;
        monster.style.transition = 1
        size = 100;
        setTimeout(function() {
            monster.style.transition = 0;
            monster.style.scale = 1;
            monster.style.marginBottom = "20px"
            monster.style.zIndex = 1;
            monster.style.transition = 1;}, 290)
        setTimeout(function() {window.location.href = "../index.php"}, 300);
    }
    button_counter++;
    monster.style.scale = size;
    let bottomMargin = 310 * size - 290;
    monster.style.marginBottom = bottomMargin.toString() + 'px';
}
