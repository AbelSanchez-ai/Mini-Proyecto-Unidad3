@component('mail::message')
# ¡Felicidades! Venta Validada

Hola,

Un comprador ha adquirido uno de tus productos y la orden ha sido validada. Por favor, prepara el envío.

**Detalles de la Orden:**
- **Orden ID:** {{ $ordenId }}
- **Fecha de Validación:** {{ now()->format('d/m/Y H:i') }}

**Producto a Enviar:**
- **Nombre:** {{ $nombreProducto }}
- **Cantidad:** {{ $cantidad }}

**Datos del Comprador (para el envío):**
- **Nombre:** {{ $nombreComprador }}
- **Email:** {{ $emailComprador }}
{{-- @isset($direccionEnvio)
- **Dirección de Envío:** {{ $direccionEnvio }}
@endisset --}}

Por favor, procede con el envío lo antes posible.

Gracias,
<br>
{{ config('app.name') }}
@endcomponent