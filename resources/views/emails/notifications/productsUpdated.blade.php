@component('mail::message')
# Notificación: 

{{$msj}}

@component('mail::button', ['url' => 'https://pbshop.online/ml'])
Ir a PBShop
@endcomponent

Gracias,<br>
{{ config('app.name') }}
@endcomponent
