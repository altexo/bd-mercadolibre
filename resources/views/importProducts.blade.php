   	@extends('layouts.app')

   	@section('content')
  
		<form class="mt-4" method="post" action="{{route('import.new.asins')}}" enctype="multipart/form-data" accept-charset="UTF-8">
	        		{{csrf_field()}}
		<div class="modal-header">
			<h5 class="modal-title" id="exampleModalLabel">Importar nuevos Asins desde CSV</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close">
			    <span aria-hidden="true">&times;</span>
			</button>
		</div>
		<div class="modal-body">
		    <div class="col-md-12">
		        <input type="file" name="file" required>
		    </div>   
		</div>
		<div class="modal-footer">
		    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
		    <button type="submit" class="btn btn-primary">Importar Asins</button>
		</div>
	</form>
@endsection