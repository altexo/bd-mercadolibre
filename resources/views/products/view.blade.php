@extends('layouts.app')
@section('head')
<style>
    .edit-icon{
        padding: 0px 3px 0px;
    }
    .form-control-borderless {
    border: none;
}

.form-control-borderless:hover, .form-control-borderless:active, .form-control-borderless:focus {
    border: none;
    outline: none;
    box-shadow: none;
}
</style>

@endsection
@section('content')
<div class="col-md-12 mt-4 mb-4">
<a href="{{route('proucts.addNew')}}" class="btn btn-primary btn-sm mb-3">Añadir nuevo producto</a>
<div class="row justify-content-center">
    <div class="col-12 col-md-12 col-lg-12">
        <form class="card card-sm" method="GET" action="{{route('products.search')}}">
            <div class="card-body row no-gutters align-items-center">
                <div class="col-auto mr-2 mt-1">
                    <i class="fas fa-search h5 text-body"></i>
                </div>
                <!--end of col-->
                <div class="col">
                    <input name="search" value="{{request()->search}}" class="form-control form-control-md form-control-borderless" type="search" placeholder="Buscar por título, ASIN">
                </div>
                <!--end of col-->
                <div class="col-auto">
                    <button class="btn btn-md btn-success" type="submit">Buscar</button>
                </div>
                <!--end of col-->
            </div>
        </form>
    </div>
    <!--end of col-->
</div>
</div>
    <div class="col-md-12 mt-4">
        <div class="card table-responsive">
            <table class="table">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Asin</th>
                    <th scope="col">Titulo</th>
                    <th scope="col">Margen</th>
                    <th scope="col">P. ML</th>
                    <th scope="col">P. Prov</th>
                    <th scope="col">Estado</th>
                    <th scope="col"></th>
                  </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                    <tr>
                        <th scope="row">{{$product->ml_data_id}}</th>
                        <td>{{$product->asin}}</td>
                        <td id="product-title{{$product->ml_data_id}}">{{$product->title}}</td>
                        <td id="product-margen{{$product->ml_data_id}}">{{$product->margin_sale}}</td>
                        <td>{{$product->ml_price}}</td>
                        <td>{{$product->provider_price}}</td>
                        <td>{{$product->status_name}}</td>
                        <td>
                            <button style="padding: 2px;" id="edit{{$product->ml_data_id}}" class="btn btn-primary" onclick="edit_product({{$product->ml_data_id}})">
                                <i class="fas fa-edit  edit-icon"></i>
                            </button>
                            <button class="btn btn-danger btn-sm delete-product" >
                               
                                <i class="fas fa-trash"> <input type="hidden" value="{{$product->provider_id}}">
                                    <input type="hidden" value="{{$product->ml_data_id}}"></i>
                            </button>
                            <button type="button" id="update{{$product->ml_data_id}}" class="btn btn-success btn-hide btn-sm" onclick="update_product({{$product->ml_data_id}})">
                                <i class="fas fa-save"></i>
                                <input type="hidden" value="{{route('products.get.product',[$product->ml_data_id])}}" id="url{{$product->ml_data_id}}">
                            </button>
                            <button type="button" id="cancel{{$product->ml_data_id}}" class="btn btn-danger btn-hide btn-sm" onclick="cancel({{$product->ml_data_id}})">
                                <i class="fas fa-times"></i>
                                <input type="hidden" value="" id="title-product{{$product->ml_data_id}}">
                                <input type="hidden" value="" id="margen-product{{$product->ml_data_id}}">
                            </button>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            vacio
                        </tr>
                    @endforelse
                
                </tbody>
              </table>
              {{$products->links()}}
        </div>
    </div>

@endsection
@section('scripts')
    <script>

        function cancel(prod_id){
            $("#edit"+prod_id).show();
            $("#update"+prod_id).hide();
            $("#cancel"+prod_id).hide();
            var title = $("#title-product"+prod_id).val();
            document.getElementById("product-title"+prod_id).innerHTML=title;
            var margen = $("#margen-product"+prod_id).val();
            document.getElementById("product-margen"+prod_id).innerHTML=margen;
        }

        function edit_product(prod_id){
            $("#edit"+prod_id).hide();
            $("#update"+prod_id).show();
            $("#cancel"+prod_id).show();
            //input en titulo
            var title=document.getElementById("product-title"+prod_id);
            var title_data=title.innerHTML;
            $("#title-product"+prod_id).val(title_data); //En caso de cancelar se conserva el valor inicial
            title.innerHTML="<input type='text' class='input-title' id='product-title-text"+prod_id+"' value='"+title_data+"'>";
            //input en margen
            var margen=document.getElementById("product-margen"+prod_id);
            var margen_data=margen.innerHTML;
            $("#margen-product"+prod_id).val(margen_data); //En caso de cancelar se conserva el valor inicial
            margen.innerHTML="<input type='text' id='product-margen-text"+prod_id+"' value='"+margen_data+"'>";
        }

        function update_product(prod_id){
            $("#edit"+prod_id).show();
            $("#update"+prod_id).hide();
            $("#cancel"+prod_id).hide();
            var title = $("#product-title-text"+prod_id).val();
            document.getElementById("product-title"+prod_id).innerHTML=title;

            var margen = $("#product-margen-text"+prod_id).val();
            document.getElementById("product-margen"+prod_id).innerHTML=margen;

            $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            type:'GET',
            url: $("#url"+prod_id).val(),
            success:function(response){
                console.log(response);
                $.ajax({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                type:'POST',
                url: '{{route('products.update')}}',
                data: {'title': title, margin_sale: margen, 'product_id': response.id},
                success:function(response){
                    console.log(response)
                    $("#response-msj").text(response);
                }});
            }})
        }
        $("body").on("click", ".delete-product", function(){
            var provider_id = $(this).find('input').val();
            var ml_id = $(this).find('input').next().val();
            if (confirm('Seguro quieres borrar este producto?')) {
                $.ajax({
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    type:'POST',
                    url: '{{route('products.delete')}}',
                    data: {'provider_id': provider_id, 'ml_id': ml_id},
                    success:function(response){
                        alert(response.msj)

                        console.log(response);
                    },
                    error:function(response){
                        alert(response.msj)
                        console.log(response)
                    }
                })
            }
        });
        
    </script>

@endsection