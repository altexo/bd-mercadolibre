@extends('layouts.app')
@section('head')
<style src="{{asset('css/style.css')}}" type="text/css" media="screen"></style>
<style type="text/css" media="screen">
	.hidden{
		display: none;
	}
</style>
@endsection
@section('content')
<div class="container">
	<div class="card text-center ">

	 	<div class="card-body hidden" id="not-logged">
	    	<h5 class="card-title">Aún no Esta autorizado para publicar en MercadoLibre</h5>
	    	<p class="card-text">Para hacerlo comienza dando click en el siguiente boton:</p>
	    	<a href="#" id="login-button" class="btn btn-primary">Authorizar</a>
	  	</div>
	  	<div class="card-body hidden"  id="auth">
	    	<h5 class="card-title">Usuario: </h5>
	    	{{-- <p class="card-text">Para hacerlo comienza dando click en el siguiente boton:</p> --}}
	    	<a href="#" id="get-user-button" class="btn btn-primary">Obtener Usuario</a>
	    	<button type="button" class="btn btn-primary" id="publish-button">Publicar productos</button>
	  	</div>
	</div>
</div>
@endsection
@section('scripts')
{{-- <script src="{{asset('js/mercadolibre-1.0.4.js')}}"></script> --}}
<script src="https://a248.e.akamai.net/secure.mlstatic.com/org-img/sdk/mercadolibre-1.0.4.js"></script>
<script src="{{asset('js/ml.js')}}" type="text/javascript" charset="utf-8" async defer></script>
@endsection