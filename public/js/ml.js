$( document ).ready(function() {
    //console.log(process.env.MIX_SENTRY_DSN_PUBLIC)
	 $("#auth").show();
    console.log( "ready!" );
    //window.myVar = '{{ env('MY_VAR') }}';
    MELI.init({ 
    	client_id: 7842023519454578, 
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
    // var price = "634.00";
    // var newP = Math.ceil(price)
    //  price = price.slice(0,-3);
    //  console.log(price);
    //  console.log(newP);
     var prod = "Guante del Artista con 2 Dedos para Tabletas Graficas y Pantallas de Tablet";
    var cat = "MLM1499";
    $.get('https://api.mercadolibre.com/sites/MLM/category_predictor/predict?title='+prod+'&category_from='+cat, function(data){
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



$("#publish-new-button").click(function(){
     $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            type:'GET',
            //url: 'http://127.0.0.1:8000/api/new-products',
            //url: "https://pbshop.online/api/new-products",
            url: "https://pbshop.online/api/new-products",
            
            success:function(response){
                var not_published = [];
                console.log(response);
                $.each(response.response, function(index, data){
                    //  if (data.asin == null) {
                    //     return 'Skip';
                    // }
                    if (data.price == "0.00"){
                        return 'skip'
                    }
                    $("#published-table").show();
                    var estado = "";
                    var url = "/items";
                    //var data = data.response[0];
                    var picturesArray = JSON.parse(data.url);
                    var tagsArray = JSON.parse(data.tags_object);
                    var shippingArray = JSON.parse(data.full_atts);

                    var title = data.title;
                    var category_id = data.category_id;
                    var margin = data.margin_sale;
                    if (margin != null) {
                     var price = Math.round(data.price*margin);
                    }else{
                     var price = Math.round(data.price*1.30);
                    }
                    price = Math.round(price);
                   // price = 
                   // price = price.slice(0,-3);
                    var currency_id = data.currency_id;
                    var available_quantity = data.available_quantity;
                    var buying_mode = data.buying_mode;
                    var listing_type_id = data.listing_type_id;
                    var condition = "new";
                    //Preparar lista de imagenes
                    //var attributes = JSON.parse(data.attributes[0].attributes_details);
                    var picturesArrayList = [];
                    $.each(picturesArray,function(index, pic) {
                        picturesArrayList.push(pic);
                    });
                    var desc_array = new Array();
                    desc_array['plain_text'] = data.description;
                    var cat = "";
                     $.ajax({
                        //headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        type:'GET',
                        url: 'https://api.mercadolibre.com/sites/MLM/category_predictor/predict?title='+title+'&category_from='+category_id,
                        success:function(response_cat){
                             cat = response_cat.id;
                        var productObj = {
                         title: title,
                         category_id: cat,
                         price: price,
                         currency_id: currency_id,
                         available_quantity: available_quantity,
                         buying_mode: buying_mode,
                         listing_type_id: listing_type_id,
                         condition: condition,
                         description: {plain_text: "**Envío GRATIS pregunte por los tiempos de entrega y la disponibilidad del producto ANTES de ofertar.\n\n\n "+data.description},
                         tags: tagsArray,
                         pictures: picturesArrayList,
                         shipping: shippingArray,
                        // attributes: attributes,
                         seller_custom_field: data.asin

                    }
                    
                    var provider_id = data.provider_id;
                    console.log('Obj producto');
                    console.log(productObj);

                    //Publicar a ML
                        try{
                            MELI.post(url,productObj, function(data) {
                                console.log("ML response: ")
                                console.log(data);
                                this.estado = "Publicado";
                                var state = 1;
                                if (data[0] != 201) {
                                    state = 2;
                                    not_published.push({product:productObj, error: data});
                                    $.ajax({
                                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                                        type:'POST',
                                        data:{id: provider_id, state: state},
                                        url: "https://pbshop.online/api/products/state/update",
                                        success:function(response){
                                            console.log(response)
                                        },
                                        error:function(err){
                                            console.log(err)
                                        }
                                        });
                                    this.estado = "No publicado";
                                }
                                $.ajax({
                                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                                    type:'POST',
                                    data:{id: provider_id, state: state},
                                    url: "https://pbshop.online/api/products/state/update",
                                    success:function(response){
                                        console.log(response)
                                    },
                                    error:function(err){
                                        console.log(err)
                                    }
                            });
                            })
                        }catch (e){
                        console.log('Error: ');
                        console.log(e);
                        this.estado = "No Publicado";
                        }
                   
                            $("#table-rows").append("<tr style='font-size: 10pt'><td>"+data.id+"</td><td>"+title+"</td><td>"+price+"</td><td>"+this.estado+"</td></tr>")
                      

                  
                        },
                         error:function(response){
                            console.log(response)
                        }
                    });
                });
                console.log(not_published);
            },
            error:function(response){

            }
        });
});


