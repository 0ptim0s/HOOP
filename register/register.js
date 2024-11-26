

document.getElementById("register").addEventListener("click",()=>{
    var password = document.getElementById("password").value;
    var confirmpassword = document.getElementById("confirmpassword").value;
    var name = document.getElementById("firstname").value;
    var surname = document.getElementById("surname").value;
    var DOB = document.getElementById("DOB").value;
    var region = document.getElementById("region").value;
    var email = document.getElementById("email").value;
    var age = calculateAge(DOB);
    
    if(!passwordsMatch(password,confirmpassword)){alert("Passwords do not match");}

    /**Ajax goes here */
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
