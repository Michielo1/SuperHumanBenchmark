const textStage1 = document.getElementById("first-stage");
const fail = document.getElementById("fail");
const success = document.getElementById("success");
const stage2 = document.getElementById("stage2");
let currentRound = 0;

function startTest() {
    fail.style.display = "none";
    success.style.display = "none";
    stage2.style.display = "none";
    currentRound = 0;
    textStage1.style.display = "block";
}

function submitStage1() {
    textStage1.style.display = "none";
    if (document.getElementById("stage1input").value == "1") {
        prepareRound();
        stage2.style.display = "block";
        textStage1.style.display = "none";
    } else {
        fail.style.display = "block";
        textStage1.style.display = "none";
    }
    document.getElementById("stage1input").value = "";
}

function option(n) {
    if (currentRound == 4) {
        success.style.display = "block";
        stage2.style.display = "none";
        currentRound = 0;
    } else if (n == stage2rounds[currentRound].answer) {
        currentRound++;
        prepareRound();
    } else {
        fail.style.display = "block";
        stage2.style.display = "none";
        currentRound = 0;
    }
}

const color = document.getElementById("color");
const option1 = document.getElementById("option1");
const option2 = document.getElementById("option2");
function prepareRound() {
    color.style.backgroundColor = stage2rounds[currentRound].hex;
    option1.innerHTML = stage2rounds[currentRound].optionOne;
    option2.innerHTML = stage2rounds[currentRound].optionTwo;
}

class Round {
    constructor(optionOne, optionTwo, hex, answer) {
        this.optionOne = optionOne;
        this.optionTwo = optionTwo;
        this.hex = hex;
        this.answer = answer;
    }
}

let stage2rounds = [
    new Round("Un-teal we meet again", "platypus-teal", "#7EC9C8", "1"),
    new Round("Patrick pink", "Drunk-tank pink", "#FF91AF", "2"),
    new Round("Snugglepuss", "Whale sail", "#9C94BB", "1"),
    new Round("Banan-appeal", "Yellow", " #FCEBB7", "1"),
    new Round("Green", "Grey", "#FF0000", "both")]
