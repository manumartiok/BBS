<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; }
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; }
        .pdf-header { width: 100%; height: auto; margin: 0; padding: 0; line-height: 0; }
        .pdf-header img { display: block; width: 100%; height: auto; margin: 0; padding: 0; border: 0; }
        .document-content { margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #D7B5DF; }
        .header h1 { color: #D7B5DF; margin: 0; font-size: 22px; }
        .info { margin: 15px 0; background: #f9f9f9; padding: 10px; border-left: 3px solid #D7B5DF; }
        .info p { margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 7px 9px; text-align: left; }
        th { background: #D7B5DF; color: white; font-weight: bold; }
        .totals { margin-top: 15px; text-align: right; }
        .totals table { width: 280px; margin-left: auto; }
        .totals td { padding: 7px 9px; }
        .total-final { background: #f0f0f0; font-weight: bold; font-size: 13px; }
        .footer { margin-top: 30px; font-size: 9px; color: #999; text-align: center; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>

@php
    $esCotizacion = $esCotizacion ?? false;
    $cliente = $esCotizacion ? (object) ($cotizacion['cliente'] ?? []) : ($pedido->cliente ?: $pedido->usuario);
    $items = $esCotizacion ? collect($cotizacion['items'] ?? []) : $pedido->items;
    $totales = $esCotizacion ? ($cotizacion['totales'] ?? []) : array_merge([
        'subtotal_sin_descuento' => $pedido->subtotal,
        'descuento_items' => 0,
    ], $pedido->totales ?: []);
    $entrega = $esCotizacion
        ? ($cotizacion['entrega_label'] ?? '-')
        : ([
            'mercado_flex' => 'Mercado Flex',
            'transporte_terminal' => 'Transporte a la terminal',
            'retiro_local' => 'Retiro en local',
            'retiro' => 'Retiro cliente',
            'reparto' => 'Reparto Resortes generales',
            'convenir' => 'A convenir',
        ][$pedido->entrega] ?? $pedido->entrega);
    $formaPago = $esCotizacion ? ($cotizacion['forma_pago_label'] ?? '-') : null;
    $mostrarDescuentos = $esCotizacion ? ($cotizacion['mostrar_descuentos'] ?? true) : true;
    $numero = $esCotizacion ? 'COTIZACION' : 'PEDIDO #' . str_pad($pedido->id, 8, '0', STR_PAD_LEFT);
    $fecha = $esCotizacion ? now()->format('d/m/Y H:i') : $pedido->created_at?->format('d/m/Y H:i');
@endphp

@if($esCotizacion)
    <div class="pdf-header">
        <img src="{{ public_path('assets/img/cabezalpdf.png') }}" alt="Uper Cab">
    </div>
@else
    <div class="header">
        <h1>{{ $numero }}</h1>
        <p>Fecha: {{ $fecha }}</p>
    </div>
@endif

<div class="document-content">
<div class="info">
    <p><strong>Cliente:</strong> {{ $cliente->empresa ?: ($cliente->usuario ?? 'N/A') }}</p>
    <p><strong>Email:</strong> {{ $cliente->email ?? 'N/A' }}</p>
    <p><strong>Entrega:</strong> {{ $entrega }}</p>
    @if($formaPago)
        <p><strong>Condicion de pago:</strong> {{ $formaPago }}</p>
    @endif
    @if(!$esCotizacion && $pedido->mensaje)
        <p><strong>Mensaje:</strong> {{ $pedido->mensaje }}</p>
    @endif
</div>

<table>
    <thead>
        <tr>
            <th>Código</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Precio unit.</th>
            <th>Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
        <tr>
            <td>{{ $esCotizacion ? ($item['codigo'] ?? '-') : ($item->codigo ?: '-') }}</td>
            <td>{{ $esCotizacion ? ($item['nombre'] ?? '-') : ($item->nombre ?: '-') }}{{ $esCotizacion ? (!empty($item['formacion']) ? ' (' . $item['formacion'] . ')' : '') : ($item->formacion ? ' (' . $item->formacion . ')' : '') }}</td>
            <td style="text-align:right;">{{ number_format($esCotizacion ? ($item['cantidad'] ?? 0) : $item->cantidad, 0, ',', '.') }}</td>
            <td style="text-align:right;">$ {{ number_format($esCotizacion ? ($item['precio_con_descuento'] ?? 0) : $item->precio_con_descuento, 2, ',', '.') }}</td>
            <td style="text-align:right;">$ {{ number_format($esCotizacion ? ($item['subtotal'] ?? 0) : $item->subtotal, 2, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="totals">
    <table>
        @if($mostrarDescuentos)
            <tr><td>Subtotal sin descuento:</td><td style="text-align:right;">$ {{ number_format($totales['subtotal_sin_descuento'] ?? 0, 2, ',', '.') }}</td></tr>
        @endif
        @if($mostrarDescuentos && ($totales['descuento_items'] ?? 0) > 0)
            <tr style="color:#007E22;"><td>Descuento cliente:</td><td style="text-align:right;">-$ {{ number_format($totales['descuento_items'], 2, ',', '.') }}</td></tr>
        @endif
        <tr><td>Subtotal:</td><td style="text-align:right;">$ {{ number_format($esCotizacion ? ($totales['subtotal'] ?? 0) : $pedido->subtotal, 2, ',', '.') }}</td></tr>
        <tr><td>IVA (21%):</td><td style="text-align:right;">$ {{ number_format($esCotizacion ? ($totales['iva'] ?? 0) : $pedido->iva, 2, ',', '.') }}</td></tr>
        <tr class="total-final"><td>TOTAL:</td><td style="text-align:right;">$ {{ number_format($esCotizacion ? ($totales['total'] ?? 0) : $pedido->total, 2, ',', '.') }}</td></tr>
    </table>
</div>

<div class="footer">
    <p>Generado automáticamente el {{ now()->format('d/m/Y H:i:s') }}</p>
</div>
</div>

</body>
</html>
