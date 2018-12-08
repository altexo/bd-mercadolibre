$( document ).ready(function() {
	$("#auth").hide();
    console.log( "ready!" );
    MELI.init({ client_id: 6677614414680820 });
	
// 	if (localStorage.getItem("token") != null) {
// 		$("#not-logged").hide();
// 	 	$("#auth").show();
// 		var token = localStorage.getItem("token");
// 		console.log("Token in localStorage: "+token);
// 	}else{
// 		console.log("Aun no haz iniciado sesion amiwo")
// 	}

// });


$("#login-button").click(function(){
	 MELI.login(function() {
	 	$("#not-logged").hide();
	 	$("#auth").show();
	 	  	// MELI.get("/users/me", {}, function(data) {
	   //  	alert("Hello " + data[2].first_name);
	  	// });
	  	localStorage.setItem("token", MELI.getToken());
	  	MELI.get("/users/me", {}, function(data) {
	    	console.log("Login data: "+data)
	    	console.log("token: "+MELI.getToken());
	    	var storage = {token: MELI.getToken()};
	  	});
	  	
	});
});

$("#get-user-button").click(function(){
	var token = localStorage.getItem("token");
	MELI.get("/users/6677614414680820", null, function(data) {
		console.log(data[0]);
		console.log(data);
	});
});

