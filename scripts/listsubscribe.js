
function signup(){
var ajaxRequest;  // The variable that makes Ajax possible!
	
	try{
		// Opera 8.0+, Firefox, Safari
		ajaxRequest = new XMLHttpRequest();
	} catch (e){
		// Internet Explorer Browsers
		try{
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		} catch (e) {
			try{
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			} catch (e){
				// Something went wrong
				alert("Your browser broke!");
				return false;
			}
		}
	}
	// Create a function that will receive data sent from the server
	ajaxRequest.onreadystatechange = function(){
		if(ajaxRequest.readyState == 4){
			var ajaxDisplay = document.getElementById('response_div');
			ajaxDisplay.innerHTML = ajaxRequest.responseText;
			
		}
	}
	var firstname = document.getElementById("list_firstname").value;
	var lastname = document.getElementById("list_lastname").value;
	var email = document.getElementById("list_email").value;
	var queryString = "?firstname="+firstname+"&lastname="+lastname+"&email="+email;
	ajaxRequest.open("GET", "http://aoproductions.net/blog/subscribemethod.php" + queryString, true);
	ajaxRequest.send(queryString);
	
}