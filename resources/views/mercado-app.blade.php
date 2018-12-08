@extends('layouts.app')
@section('content')
	
	<div id="app">
		<navbar></navbar>

		<div class="container">	
			<ml-login></ml-login>
		</div>
	</div>
@endsection
@section('scripts')
	{{-- <script src="{{asset('js/app.js')}}" type="text/javascript" charset="utf-8" async defer></script>
@endsection --}}