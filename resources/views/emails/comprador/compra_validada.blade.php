@component('mail::message')
# ¡Tu Compra Ha Sido Validada!

Hola {{ $nombreComprador }},

Nos complace informarte que tu orden **#{{ $ordenId }}** ha sido validada. Los vendedores han sido notificados para preparar tus productos.

**Resumen de tu Orden:**
- **ID Orden:** {{ $ordenId }}
- **Monto Total:** ${{ number_format($montoTotal, 2) }}
- **Fecha de Validación:** {{ now()->format('d/m/Y H:i') }}

**Productos en tu orden:**
@foreach($productosOrden as $producto)
- {{ $producto->name }} (Cantidad: {{ $producto->pivot->quantity }})
@if($producto->vendedor) {{-- Asumiendo que Producto tiene una relación 'vendedor' --}}
*Vendido por: {{ $producto->vendedor->name }}*
@endif
@endforeach

**Contacto con el/los Vendedor(es):**
@if($listaVendedores->count() == 1)
Para cualquier consulta sobre tu producto o el envío, puedes contactar directamente al vendedor:
- **Vendedor:** {{ $listaVendedores->first()->name }}
- **Email:** {{ $listaVendedores->first()->email }}
@elseif($listaVendedores->count() > 1)
Tu orden incluye productos de múltiples vendedores. Puedes contactarlos para consultas específicas:
@foreach($listaVendedores as $vendedor)
- **Vendedor:** {{ $vendedor->name }} (Email: {{ $vendedor->email }}) - para los productos que le compraste.
@endforeach
@else
Si tienes alguna consulta, por favor, contacta a nuestro equipo de soporte.
@endif

Te recomendamos ponerte en contacto directamente con el/los vendedor(es) para coordinar los detalles del envío.

Gracias por tu compra,
<br>
{{ config('app.name') }}
@endcomponent