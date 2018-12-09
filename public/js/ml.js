$( document ).ready(function() {
	// $("#auth").hide();
    console.log( "ready!" );
    MELI.init({ 
    	client_id: 6677614414680820, 
    	xauth_protocol: "https://",
  		xauth_domain: "secure.mlstatic.com",
  		xd_url: "/org-img/sdk/xd-1.0.4.html"
    });

	MELI.getLoginStatus(function(data) {
		console.log('LoginStatus: ')
  		console.log(data);
	});
 });

//Login ML click Event
$("#login-button").click(function(){
	 MELI.login(function() {
	 	$("#not-logged").hide();
	 	$("#auth").show();
	 	  	MELI.get("/users/me", {}, function(data) {
	    	localStorage.setItem("user_id", data[2].id);
	  	});
	});
});

$("#get-user-button").click(function(){
	var user_id = localStorage.getItem("user_id");
	MELI.get("/users/"+user_id, null, function(data) {
		//console.log(data[0]);
		console.log(data);
	});
});

$

