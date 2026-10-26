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
        .total-final { background: #f0f0f0; font-weight: bold; font-size: 13px; }
        .footer { margin-top: 30px; font-size: 9px; color: #999; text-align: center; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
<div class="document-content">
    <div class="header">
        <h1>CANJE DE PUNTOS #{{ str_pad($canje->id, 8, '0', STR_PAD_LEFT) }}</h1>
        <p>Fecha: {{ $canje->created_at?->format('d/m/Y H:i') }} | Estado: {{ $canje->estado_texto }}</p>
    </div>

    <div class="info">
        <p><strong>Nombre:</strong> {{ $canje->nombre }}</p>
        <p><strong>CUIT / CUIL:</strong> {{ $canje->cuit }}</p>
        <p><strong>Email:</strong> {{ $canje->email }} | <strong>Celular:</strong> {{ $canje->celular }}</p>
        <p><strong>Dirección:</strong> {{ $canje->direccion }}, {{ $canje->localidad }}, {{ $canje->provincia }} ({{ $canje->codigo_postal }})</p>
        <p><strong>Forma de envío:</strong> {{ $envio }}@if($canje->guia_transporte) | <strong>Guía:</strong> {{ $canje->guia_transporte }}@endif</p>
        @if($canje->entrega_otra)
            <p><strong>Entrega en:</strong> {{ $canje->entrega_otra['direccion'] ?? '' }}, {{ $canje->entrega_otra['localidad'] ?? '' }}, {{ $canje->entrega_otra['provincia'] ?? '' }} ({{ $canje->entrega_otra['codigo_postal'] ?? '' }})</p>
        @endif
        @if($canje->mensaje)
            <p><strong>Mensaje:</strong> {{ $canje->mensaje }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Código</th>
                <th>Producto</th>
                <th>Puntos</th>
            </tr>
        </thead>
        <tbody>
            @foreach($canje->items as $item)
                <tr>
                    <td>{{ $item->codigo ?: '-' }}</td>
                    <td>{{ $item->nombre ?: '-' }}</td>
                    <td style="text-align:right;">{{ number_format($item->puntos, 0, ',', '.') }}</td>
                </tr>
            @endforeach
            <tr class="total-final">
                <td colspan="2">TOTAL CANJEADO</td>
                <td style="text-align:right;">{{ number_format($canje->puntos, 0, ',', '.') }} puntos</td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Generado automáticamente el {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</div>
</body>
</html>
