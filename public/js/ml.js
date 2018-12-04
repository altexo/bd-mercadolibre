$( document ).ready(function() {
    console.log( "ready!" );
    MELI.init({ client_id: 6586 });

    MELI.login(function() {
	  	MELI.get("/users/me", {}, function(data) {
	    	alert("Hello " + data[2].first_name);
	  	});
	});
});