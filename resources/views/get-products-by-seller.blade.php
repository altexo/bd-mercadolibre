@extends('layouts.app')

@section('content')
<div class="col-md-12">
    @if (session('status'))
        <div class="alert alert-danger ">
            {{ session('message') }}
        </div>
    @endif
</div>
    <form method="post" action="{{route('getProductsBySeller')}}">
            {{ csrf_field() }}
        <div class="form-group">
            
            <label for="seller">ID del vendedor</label>
            <input type="seller" name="seller" class="form-control" id="seller" aria-describedby="emailHelp" placeholder="Seller ID">
            <small id="emailHelp" class="form-text text-muted"></small>
        </div>
        <button class="btn primary">
            Obtener
        </button>
    </form>
@endsection