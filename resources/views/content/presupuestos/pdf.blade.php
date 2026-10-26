<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; }
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; }
        .document-content { margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #0D1A35; }
        .header h1 { color: #0D1A35; margin: 0; font-size: 22px; }
        .info { margin: 15px 0; background: #f9f9f9; padding: 10px; border-left: 3px solid #0D1A35; }
        .info p { margin: 4px 0; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { border: 1px solid #ddd; padding: 7px 9px; text-align: left; }
        th { background: #0D1A35; color: white; font-weight: bold; }
        .totals { margin-top: 15px; text-align: right; }
        .totals table { width: 280px; margin-left: auto; }
        .totals td { padding: 7px 9px; }
        .total-final { background: #f0f0f0; font-weight: bold; font-size: 13px; }
        .footer { margin-top: 30px; font-size: 9px; color: #999; text-align: center; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>

@php
    $cliente = $presupuesto->usuario;
    $totales = $presupuesto->totales ?: [];
@endphp

<div class="document-content">
<div class="header">
    <h1>PRESUPUESTO #{{ str_pad($presupuesto->id, 8, '0', STR_PAD_LEFT) }}</h1>
    <p>Fecha: {{ $presupuesto->created_at?->format('d/m/Y H:i') }} | Vencimiento: {{ $presupuesto->fecha_vencimiento->format('d/m/Y') }}</p>
</div>

<div class="info">
    <p><strong>Cliente:</strong> {{ $cliente?->empresa ?: ($cliente?->usuario ?? 'N/A') }}</p>
    <p><strong>Email:</strong> {{ $cliente?->email ?? 'N/A' }}</p>
    <p><strong>Entrega:</strong> {{ $entrega ?: '-' }}</p>
    <p><strong>Condicion de pago:</strong> {{ $formaPago ?: '-' }}</p>
    @if($presupuesto->mensaje)
        <p><strong>Mensaje:</strong> {{ $presupuesto->mensaje }}</p>
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
        @foreach($presupuesto->items as $item)
        <tr>
            <td>{{ $item->codigo ?: '-' }}</td>
            <td>{{ $item->nombre ?: '-' }}</td>
            <td style="text-align:right;">{{ number_format($item->cantidad, 0, ',', '.') }}</td>
            <td style="text-align:right;">$ {{ number_format($item->precio_con_descuento, 2, ',', '.') }}</td>
            <td style="text-align:right;">$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="totals">
    <table>
        <tr><td>Subtotal sin descuento:</td><td style="text-align:right;">$ {{ number_format($totales['subtotal_sin_descuento'] ?? 0, 2, ',', '.') }}</td></tr>
        @if(($totales['descuento_items'] ?? 0) > 0)
            <tr style="color:#007E22;"><td>Descuento cliente:</td><td style="text-align:right;">-$ {{ number_format($totales['descuento_items'], 2, ',', '.') }}</td></tr>
        @endif
        <tr><td>Subtotal:</td><td style="text-align:right;">$ {{ number_format($presupuesto->subtotal, 2, ',', '.') }}</td></tr>
        <tr><td>IVA (21%):</td><td style="text-align:right;">$ {{ number_format($presupuesto->iva, 2, ',', '.') }}</td></tr>
        <tr class="total-final"><td>TOTAL:</td><td style="text-align:right;">$ {{ number_format($presupuesto->total, 2, ',', '.') }}</td></tr>
    </table>
</div>

<div class="footer">
    <p>Los precios están sujetos a modificación sin previo aviso. Presupuesto válido hasta el {{ $presupuesto->fecha_vencimiento->format('d/m/Y') }}.</p>
    <p>Generado automáticamente el {{ now()->format('d/m/Y H:i:s') }}</p>
</div>
</div>

</body>
</html>
