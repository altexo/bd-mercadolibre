   	@extends('layouts.app')

   	@section('content')
   	<form method="post" action="{{route('import.products')}}" enctype="multipart/form-data" accept-charset="UTF-8">
	        		{{csrf_field()}}
		<div class="modal-header">
			<h5 class="modal-title" id="exampleModalLabel">Importar productos desde CSV</h5>
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
		    <button type="submit" class="btn btn-primary">Importar</button>
		</div>
	</form>
@endsection