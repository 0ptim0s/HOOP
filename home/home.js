document.getElementById("searchBar").addEventListener("keypress",(event)=>{
    if(event.key == "Enter"){
        var search = document.getElementById("searchBar").value;
        /**Ajax here*/
        
    }
})


function viewpage(){
    window.location.href = "../view/view.html";
}