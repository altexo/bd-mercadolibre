$( document ).ready(function() {
	 $("#auth").show();
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
  		if (data.status =="AUTHORIZED") {
  			$("#auth").show();
  		}else{
  			$("#not-logged").show();
  		}
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

$("#publish-button").click(function(){
	 $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            type:'GET',
            //url: 'http://127.0.0.1:8000/api/products',
           	url: "https://bd-mercadolibre.herokuapp.com/api/products",
            success:function(response){
            	var url = "/items";
            	var data = response.response;
            	var picturesArray = JSON.parse(data.pictures[0].url);
            	var tagsArray = JSON.parse(data.tags[0].tags_object);
            	var shippingArray = JSON.parse(data.shipping[0].full_atts);

            	var title = data.products[0].title;
            	var category_id = data.category_id;
            	var price = data.price;
            	var currency_id = data.currency_id;
            	var available_quantity = data.available_quantity;
            	var buying_mode = data.buying_mode;
            	var listing_type_id = data.listing_type_id;
            	var condition = "new"
            	//Preparar lista de imagenes
            	var picturesArrayList = [];
            	$.each(picturesArray,function(index, pic) {
            		picturesArrayList.push(pic);
            	});
            	var productObj = {
            		 title: title,
            		 category_id: category_id,
            		 price: price,
            		 currency_id: currency_id,
            		 available_quantity: available_quantity,
            		 buying_mode: buying_mode,
            		 listing_type_id: listing_type_id,
            		 condition: condition,
            		 tags: tagsArray,
            		 pictures: picturesArrayList,

            	}
            	console.log(productObj);
            	console.log(picturesArrayList);
    //         	try{
	   //          	MELI.post(url, productObj, function(data) {
	   //          		console.log("ML response: ")
				// 		console.log(data);
				// 	});
				// } catch (e){
				// 	console.log('Error: ');
				// 	console.log(e);
				// }
				console.log(price);
            },
            error:function(response){

            }
        });
});


