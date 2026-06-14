const jumpscareswitch = document.getElementById("jumpscare_click");
const jumpscare_image = document.getElementById("jumpscare");
const video_song = document.getElementById('song_clip');
var audio = document.getElementById("jumpscare_audio");

// Jumpscare or video
jumpscareswitch.addEventListener("change", () => {
    if (!jumpscareswitch.checked) {
        player_jumpscare();
    } else {
        player_video();
    }
});

// Show jumpscare
function player_jumpscare(ms = 3000) {
    document.body.classList.add("no-scroll");

    video_song.pause();
    video_song.style.display = "none";

    jumpscare_image.style.display = "flex";
    audio.currentTime = 0;
    audio.play();

    setTimeout(() => {
        jumpscare_image.style.display = "none";
        document.body.classList.remove("no-scroll");
    }, ms);
}


// Show video
function player_video() {
    document.body.classList.add("no-scroll");

    jumpscare_image.style.display = "none";
    video_song.style.display = "flex";
    video_song.currentTime = 0.05;

    video_song.muted = false;
    video_song.volume = 1;

    video_song.play();

    video_song.addEventListener("ended", () => {
        video_song.style.display = "none";
        video_song.currentTime = 0.05;
        document.body.classList.remove("no-scroll");
    }, { once: true });
}

// Alert user of input.
const form = document.querySelector('.profile_settings');

form.addEventListener('submit', function(evt) {
    const firstname = document.getElementById('firstname').value;
    const infix = document.getElementById('infix').value;
    const surname = document.getElementById('surname').value;
    const password = document.getElementById('password').value;

    if (firstname && !/^[a-zA-Z]+$/.test(firstname)) {
        alert("Firstnames can't have numbers or special characters in it.");
        evt.preventDefault();
        return;
    }

    if (infix && !/^[a-zA-Z]+$/.test(infix)) {
        alert("Infixes can't have numbers or special characters in it.");
        evt.preventDefault();
        return;
    }

    if (surname && !/^[a-zA-Z]+$/.test(surname)) {
        alert("Surnames can't have numbers or special characters in it.");
        evt.preventDefault();
        return;
    }

    if (password && password.length < 8) {
        alert("Password should atleast have 8 characters. Example: StarWarsFan34!");
        evt.preventDefault();
        return;
    } 

    if (password && !/\d/.test(password)) {
        alert("Password should atleast have 1 number in it. Example: StarWarsFan34!");
        evt.preventDefault();
        return;
    }

    if (password && !/[A-Z]/.test(password)) {
        alert("Password should atleast have 1 capital in it. Example: StarWarsFan34!");
        evt.preventDefault();
        return;
    }

    if (password && !/[a-z]/.test(password)) {
        alert("Password should atleast have 1 lowercase in it. Example: StarWarsFan34!");
        evt.preventDefault();
        return;
    }

    if (password && !/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
        alert("Password should atleast have 1 special character. Example: StarWarsFan!");
        evt.preventDefault();
        return;
    }
});


banner.addEventListener('change', function() {
    const file = banner.files[0];
    if (!file) {
        return;
    }
    
    const valid_img = ["image/jpg", "image/jpeg", "image/png"];
    if(!valid_img.includes(file.type)) {
        alert("Only JPEG, JPG and PNG are allowed.");
        banner.value = ' ';
        return;
    }

    if (file.size > 5 * 1024 * 1024) {
        alert("Only images smaller than 5 MB are allowed.");
        banner.value = ' ';
        return;
    }
});

pfp.addEventListener('change', function() {
    const file_pfp = pfp.files[0];
    if (!file_pfp) {
        return;
    }

    const valid_img = ["image/jpg", "image/jpeg", "image/png"];
    if(!valid_img.includes(file_pfp.type)) {
        alert("Only JPEG, JPG and PNG are allowed.");
        pfp.value = ' ';
        return;
    }

    if (file_pfp.size > 5 * 1024 * 1024) {
        alert("Only images smaller than 5 MB are allowed.");
        pfp.value = ' ';
        return;
    }
});