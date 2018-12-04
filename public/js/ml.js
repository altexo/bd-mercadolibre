$( document ).ready(function() {
    console.log( "ready!" );
    MELI.init({ client_id: 6677614414680820 });

    if (!MELI.getToken()) {
    	var token = MELI.getToken();
    	MELI.get("/users/me", null, function(data) {
	  	console.log(data);
		});
		console.log('Token: '+token)
	}
});

$("#login-button").click(function(){
	 MELI.login(function() {
	  	MELI.get("/users/me", {}, function(data) {
	    	alert("Hello " + data[2].first_name);
	  	});
	});
});