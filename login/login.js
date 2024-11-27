document.getElementById("registerLabel").addEventListener("click",()=>{
    window.location.href = "../register/register.html";
});

document.getElementById("loginButton").addEventListener("click",()=>{
    var email = document.getElementById("email").value;
    var password = document.getElementById("password").value;

    if(email == ""){
        document.getElementById("invalidInput").innerText = "Invalid Email";
    }

    if(password == ""){
        document.getElementById("invalidInput").innerText = "Invalid Password";
    }


    /**for now */
    window.location.href = "../home/home.html";
    /**Ajax goes here */
});