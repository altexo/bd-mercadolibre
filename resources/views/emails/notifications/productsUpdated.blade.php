@component('mail::message')
# Notificación: 

{{$msj}}

@component('mail::button', ['url' => 'https://pbshop.online/ml'])
Ir a Dadasell
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
