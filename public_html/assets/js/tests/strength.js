const textInBox = document.querySelector('.text-in-box');

let played = false;

function getRandomInt(max) {
  return Math.floor(Math.random() * max);
}

window.addEventListener('keydown', function () {
    if(!played) {
        let value = getRandomInt(9) + 1;
        textInBox.innerHTML = `Sore:  ${value}/100<br>You were so weak we didn't bother saving it`;
        played = true;
    }
});