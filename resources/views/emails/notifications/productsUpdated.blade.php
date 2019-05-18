@component('mail::message')
# Notificación: 

{{$msj}}

@component('mail::button', ['url' => 'https://dadasell.app/ml'])
Ir a Dadasell
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
