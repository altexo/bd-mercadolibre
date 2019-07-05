@extends('layouts.app')
@section('content')
    <h3>Asin de nuevo producto</h3>
    @if(session()->has('error'))
        <div class="alert alert-danger">
                {{ session()->get('error') }}
        </div>
    @endif
    @if(session()->has('success'))
        <div class="alert alert-primary">
                {{ session()->get('success') }}
        </div>
    @endif
    <form method="post" action="{{route('proucts.create')}}">
        {{ csrf_field() }}
        <div class="form-group">
            <label for="asin">Asin a capturar</label>
            <input type="text" name="asin" class="form-control" id="asin" aria-describedby="asin" placeholder="ASIN">
            <small id="asin" class="form-text text-muted">Escribs el asin sin espacios ni caravteres extraños</small>
        </div>
        <button class="btn btn-primary">Obtener</button>
    </form>
@endsection