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

    if(email != "" && password != ""){
        var LoginXHR = new XMLHttpRequest();
        LoginXHR.onreadystatechange = function(){
            if(LoginXHR.readyState == 4){
                if(LoginXHR.status == 200){
                    var details = JSON.parse(this.responseText);
                    console.log(details);
                }

                if(LoginXHR.status == 409){
                    var details = JSON.parse(this.responseText);
                    document.getElementById("invalidInput").innerText = details.message;
                }
            }
        }//ajax

        const reqBody = JSON.stringify({
            "type" : "Login",
            "email": email,
            "password" : password
        });
        LoginXHR.open("POST","http://localhost/HOOP/api/api.php",true);
        LoginXHR.send(reqBody);

    }//!null

});
