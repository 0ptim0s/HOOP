

document.getElementById("register").addEventListener("click",()=>{
    var password = document.getElementById("password").value;
    var confirmpassword = document.getElementById("confirmpassword").value;
    var name = document.getElementById("firstname").value;
    var surname = document.getElementById("surname").value;
    var DOB = document.getElementById("DOB").value;
    var email = document.getElementById("email").value;
    var age = calculateAge(DOB);
    
    if(!passwordsMatch(password,confirmpassword)){alert("Passwords do not match");}

    /**Ajax goes here */

    
    if((password != "") && (name != "") && (surname != "") && (DOB != "") && (email != "") && (age != "")){
        var RegisterXHR = new XMLHttpRequest();
        RegisterXHR.onreadystatechange = function(){
            if(RegisterXHR.readyState ==  4){
                var details = JSON.parse(this.responseText);
                if(RegisterXHR.status == 201){
                    alert(details.message);
                }
                if(RegisterXHR.status == 400){
                    document.getElementById("missingDetails").innerText = details.message;
                }
                if(RegisterXHR.status == 422){
                    document.getElementById("missingDetails").innerText = details.message;
                }
                }
            }
        }else{
            document.getElementById("missingDetails").innerText = "Missing details";
        }

        const reqBody = JSON.stringify({
            "type" : "Register",
            "name" : name,
            "surname" : surname,
            "email" : email,
            "password" : password,
            "DOB" : DOB
        });
        RegisterXHR.open("POST","http://localhost/HOOP/api/api.php", true);
        RegisterXHR.send(reqBody);
        //document.getElementById("missingDetails").innerText = "";
});


function passwordsMatch(a, b){
    return a==b;
}

/**Ayush's code */
function calculateAge(dob){
    var d1 = dob;
    const d2 = new Date();
    var year = d2.getFullYear();
    var y1 = d1.substr(0, 4);
    return year - y1;
}

document.getElementById("BackToLogin").addEventListener("click",()=>{
    window.location.href = "../login/login.html";
});
