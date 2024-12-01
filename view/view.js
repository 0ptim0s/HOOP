document.getElementById("postBtn").addEventListener("click",()=>{
    const review = document.getElementById("reviewInput").value;
    const rating = document.getElementById("ratingInput").value;
    const date = new Date();

    if(rating > 5 || rating < 0 || rating == ""){document.getElementById("errorLbl").innerText="Invalid rating";console.log("error")}else{
        document.getElementById("errorLbl").innerText="";
    }


});