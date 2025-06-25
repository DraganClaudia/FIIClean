document.querySelector("form").addEventListener("submit", function (e) {
    const email = document.getElementById("email").value.trim();
    const parola = document.getElementById("parola").value;

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const parolaRegex = /^(?=.*[A-Z])(?=.*\d).{6,}$/;

    let mesaj = "";

    if (!emailRegex.test(email)) {
        mesaj += "Emailul nu este valid. Ex: nume@mail.com\n";
    }

    if (!parolaRegex.test(parola)) {
        mesaj += "Parola trebuie să conțină cel puțin o majusculă, o cifră și să aibă minim 6 caractere.\n";
    }

    if (mesaj) {
        alert(mesaj);
        e.preventDefault();
    }
});

