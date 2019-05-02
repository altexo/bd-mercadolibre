@extends('layouts.app')
@section('content')
    <div class="col-md-12 mt-4 card">
        <div class="card">
            <table class="table">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Asin</th>
                    <th scope="col">Titulo</th>
                    <th scope="col">P. ML</th>
                    <th scope="col">P. Prov</th>
                    <th scope="col"></th>
                  </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                    <tr>
                        <th scope="row">{{$product->ml_data_id}}</th>
                            <td>{{$product->asin}}</td>
                            <td>{{$product->title}}</td>
                            <td>{{$product->ml_price}}</td>
                            <td>{{$product->provider_price}}</td>
                    <td><a  class="btn btn-primary p-1 get-product" href="#"><input type="hidden" value="{{route('products.get.product',[$product->ml_data_id])}}"><i class="fas fa-edit"></i></a></td>
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
        <div id="myModal" class="modal" tabindex="-1" role="dialog">
            <div class="modal-md" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Modal title</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <input type="hidden" id="product_id" value="">
                        <label for="title">Titulo de producto</label>
                        <input id="product-title" type="text" class="form-control" id="title" aria-describedby="emailHelp" value="" >
                    </div>
                    <div class="form-group">
                        <label for="margin">Margen</label>
                        <input id="product-margin" type="number" class="form-control" id="margin"  value="" >
                    </div>
                    <div class="col-md-12">
                        <p id="response-msj"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    
                  <button type="button" class="btn btn-secondary" data-dismiss="modal" id="close-modal">Cerrar</button>
                  <button type="button" class="btn btn-primary" id="update-product">Guardar</button>
                </div>
              </div>
            </div>
          </div>

@endsection
@section('scripts')
    <script>
        $(".get-product").click(function(){
            console.log('s')
            var id = $(this).find('input').val();
            console.log(id)

            $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            type:'GET',
            //url: 'http://127.0.0.1:8000/api/products',
            url: id,
            success:function(response){
                console.log(response);
                $("#product-title").val(response.title);
                $("#product-margin").val(response.margin_price);
                $("#product_id").val(response.id);
                $('#myModal').modal()
            }})
        });

        $("#update-product").click(function(){
            var title = $("#product-title").val();
            var id = $("#product_id").val();
            $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            type:'POST',
            //url: 'http://127.0.0.1:8000/api/products',
            url: '{{route('products.update')}}',
            data: {'title': title, 'product_id': id},
            success:function(response){
                console.log(response)
                $("#response-msj").text(response);
            }})
        })
        $("#close-modal").click(function(){
            $("#response-msj").text('');
        })
    </script>


@endsection