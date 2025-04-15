document.getElementById("show-signup").addEventListener("click", function() {
    document.getElementById("login-box").classList.add("hidden");
    document.getElementById("signup-box").classList.remove("hidden");
});

document.getElementById("show-login").addEventListener("click", function() {
    document.getElementById("signup-box").classList.add("hidden");
    document.getElementById("login-box").classList.remove("hidden");
});
