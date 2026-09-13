const form = document.getElementById("registerForm");

const username = document.getElementById("username");
const password = document.getElementById("password");
const confirmPassword = document.getElementById("confirmPassword");

const message = document.getElementById("message");


form.addEventListener("submit", function (event) {

    event.preventDefault();

    const user = username.value.trim();
    const pass = password.value;
    const confirm = confirmPassword.value;


    // Check username
    if (user === "") {
        showMessage("Ingrese un usuario.");
        username.focus();
        return;
    }


    // Check password
    if (pass === "") {
        showMessage("Ingrese una contraseña.");
        password.focus();
        return;
    }


    // Check password length
    if (pass.length < 6) {
        showMessage("La contraseña debe tener al menos 6 caracteres.");
        password.focus();
        return;
    }


    // Check confirmation
    if (confirm === "") {
        showMessage("Repita la contraseña.");
        confirmPassword.focus();
        return;
    }


    // Check passwords
    if (pass !== confirm) {
        showMessage("Las contraseñas no coinciden.");
        confirmPassword.focus();
        return;
    }


    // Success
    message.textContent = "Registro válido.";
    message.style.color = "#8bc878";

});


function showMessage(text) {

    message.textContent = text;
    message.style.color = "#ff7777";

}