@extends('layouts.app')
@section('content')
<div class="container">
	<div class="card text-center ">

	 	<div class="card-body" id="not-logged">
	    	<h5 class="card-title">Aún no haz iniciado sesion en Mercado Libre</h5>
	    	<p class="card-text">Para hacerlo comienza dando click en el siguiente boton:</p>
	    	<a href="#" id="login-button" class="btn btn-primary">Iniciar Sesion</a>
	  	</div>
	  	<div class="card-body" id="auth">
	    	<h5 class="card-title">Usuario: </h5>
	    	{{-- <p class="card-text">Para hacerlo comienza dando click en el siguiente boton:</p> --}}
	    	<a href="#" id="get-user-button" class="btn btn-primary">Obtener Usuario</a>
	  	</div>
	</div>
</div>
@endsection
@section('scripts')
<script src="{{asset('js/mercadolibre-1.0.4.js')}}"></script>
<script src="https://a248.e.akamai.net/secure.mlstatic.com/org-img/sdk/mercadolibre-1.0.4.js"></script>
<script src="{{asset('js/ml.js')}}" type="text/javascript" charset="utf-8" async defer></script>
@endsection