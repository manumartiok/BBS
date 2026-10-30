@php
    $logos = App\Models\Logo::first();
    $contacto = App\Models\Contacto::first();

    $cliente = $pedido->cliente ?: $pedido->usuario;
    $items = $pedido->items;
    $totales = array_merge([
        'subtotal_sin_descuento' => $pedido->subtotal,
        'descuento_items' => 0,
    ], $pedido->totales ?: []);

    $entregas = [
        'mercado_flex' => 'Mercado Flex',
        'transporte_terminal' => 'Transporte a la terminal',
        'retiro_local' => 'Retiro en local',
        'retiro' => 'Retiro cliente',
        'reparto' => 'Reparto Resortes generales',
        'convenir' => 'A convenir',
    ];
    $formasPago = [
        'mercado_pago' => 'Mercado Pago',
        'transferencia' => 'Transferencia bancaria',
        'efectivo' => 'Efectivo (únicamente en local)',
    ];
    $entrega = $entregas[$pedido->entrega] ?? $pedido->entrega;
    $formaPago = $formasPago[$pedido->forma_pago] ?? $pedido->forma_pago;

    $numero = str_pad($pedido->id, 8, '0', STR_PAD_LEFT);

    $pesos = fn ($n) => '$' . number_format($n, 0, ',', '.');
    $porcentaje = fn ($n) => rtrim(rtrim(number_format($n, 2, ',', '.'), '0'), ',') . '%';
    $archivo = fn ($ruta) => str_replace('\\', '/', $ruta);

    // Los archivos se leen del disco (el PDF no baja imágenes por URL)
    $rutaPublica = function (?string $url, string $default) use ($archivo) {
        $ruta = $url ? public_path(ltrim($url, '/')) : null;

        return $archivo($ruta && is_file($ruta) ? $ruta : public_path($default));
    };
    $logo = $logos?->foto_nav ? $rutaPublica($logos->foto_nav, 'assets/img/default.png') : null;

    $subtotalSinDescuento = (float) ($totales['subtotal_sin_descuento'] ?? 0);
    $descuentoItems = (float) ($totales['descuento_items'] ?? 0);
    $descuentoTotal = $subtotalSinDescuento > 0 ? round($descuentoItems / $subtotalSinDescuento * 100, 2) : 0;

    $datosEmpresa = collect([
        $contacto?->direccion ? 'BBS Motion - ' . $contacto->direccion : 'BBS Motion',
        $contacto?->telefono,
        $contacto?->mail,
    ])->filter()->implode(' / ');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pedido N° {{ $numero }}</title>
    <style>
        @font-face { font-family: 'Montserrat'; font-weight: normal; src: url('{{ $archivo(public_path('fonts/Montserrat-Regular.ttf')) }}') format('truetype'); }
        @font-face { font-family: 'Montserrat'; font-weight: bold; src: url('{{ $archivo(public_path('fonts/Montserrat-Bold.ttf')) }}') format('truetype'); }
        @font-face { font-family: 'MontserratSemi'; font-weight: normal; src: url('{{ $archivo(public_path('fonts/Montserrat-SemiBold.ttf')) }}') format('truetype'); }

        @page { margin: 36px 40px 40px 40px; }
        body { font-family: 'Montserrat', sans-serif; font-size: 11px; color: #000; margin: 0; line-height: 1.4; }
        table { border-collapse: collapse; }
        p { margin: 0; }
        .semi { font-family: 'MontserratSemi', 'Montserrat', sans-serif; font-weight: normal; }
        .right { text-align: right; }
        .verde { color: #006907; }

        .cabecera { width: 100%; border-bottom: 1px solid #D9D9D9; }
        .cabecera td { padding: 0 0 16px 0; vertical-align: middle; }
        .logo { height: 44px; }
        .cabecera-datos { text-align: right; font-size: 11px; line-height: 1.6; }

        .titulo { font-size: 18px; margin: 22px 0 18px 0; }

        .tarjeta { border: 1px solid #E5E7EB; border-radius: 10px; margin-bottom: 16px; }
        .tarjeta-titulo { background: #F6F8FA; border-radius: 10px 10px 0 0; padding: 12px 18px; font-size: 14px; }
        .tarjeta-cuerpo { padding: 12px 18px; }
        .datos { width: 100%; }
        .datos td { padding: 3px 0; vertical-align: top; font-size: 11px; }
        .datos td.etiqueta { width: 120px; }

        .items { width: 100%; }
        .items th { background: #F6F8FA; border-bottom: 1px solid #D9D9D9; padding: 10px 6px; font-family: 'MontserratSemi', 'Montserrat', sans-serif; font-weight: normal; font-size: 9px; text-transform: uppercase; }
        .items td { border-bottom: 1px solid #D9D9D9; padding: 10px 6px; vertical-align: middle; font-size: 10px; }
        .foto { width: 52px; height: 52px; border: 1px solid #D9D9D9; border-radius: 8px; padding: 2px; }
        .foto img { width: 48px; height: 48px; }
        .nombre { font-size: 10px; }

        .resumen { width: 100%; }
        .resumen td { padding: 5px 0; font-size: 12px; }
        .resumen .linea td { border-top: 1px solid #D9D9D9; padding: 0; height: 8px; font-size: 1px; }
        .resumen .total td { padding-top: 8px; font-size: 18px; font-family: 'MontserratSemi', 'Montserrat', sans-serif; }
        .resumen .total .iva-incluido { font-size: 10px; }

        .pie { margin-top: 26px; padding-top: 12px; border-top: 1px solid #D9D9D9; font-size: 9px; color: #5C5C5C; }
    </style>
</head>
<body>

{{-- cabecera --}}
<table class="cabecera">
    <tr>
        <td>
            @if($logo)
                <img src="{{ $logo }}" alt="BBS Motion" class="logo">
            @endif
        </td>
        <td class="cabecera-datos">
            <p>Pedido N° {{ $numero }}</p>
            <p>Fecha: {{ $pedido->created_at?->format('d/m/Y H:i') }}</p>
        </td>
    </tr>
</table>

<h1 class="titulo semi">N° pedido {{ $numero }}</h1>

{{-- datos del pedido --}}
<div class="tarjeta">
    <div class="tarjeta-titulo semi">Datos del pedido</div>
    <div class="tarjeta-cuerpo">
        <table class="datos">
            <tr><td class="etiqueta semi">Cliente</td><td>{{ $cliente?->empresa ?: ($cliente?->usuario ?? 'N/A') }}</td></tr>
            <tr><td class="etiqueta semi">Email</td><td>{{ $cliente?->email ?? 'N/A' }}</td></tr>
            <tr><td class="etiqueta semi">Forma de entrega</td><td>{{ $entrega ?: '-' }}</td></tr>
            <tr><td class="etiqueta semi">Forma de pago</td><td>{{ $formaPago ?: '-' }}</td></tr>
            @if($pedido->mensaje)
                <tr><td class="etiqueta semi">Mensaje</td><td>{{ $pedido->mensaje }}</td></tr>
            @endif
        </table>
    </div>
</div>

{{-- productos --}}
<table class="items">
    <thead>
        <tr>
            <th style="width: 62px;"></th>
            <th style="text-align:left;">Producto</th>
            <th class="right" style="width: 80px;">Precio x unidad</th>
            <th class="right" style="width: 80px;">Descuento</th>
            <th class="right" style="width: 54px;">Cantidad</th>
            <th class="right" style="width: 84px;">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            @php
                $descuentoItem = $item->precio_base > 0 ? round((1 - $item->precio_con_descuento / $item->precio_base) * 100, 2) : 0;
                $foto = $item->producto?->foto;
                $fotoRuta = $rutaPublica(($foto && $foto !== '/default.png') ? $foto : null, 'assets/img/default.png');
            @endphp
            <tr>
                <td>
                    <div class="foto"><img src="{{ $fotoRuta }}" alt=""></div>
                </td>
                <td>
                    <p class="semi">{{ $item->codigo ?: '-' }}</p>
                    <p class="nombre">{{ $item->nombre ?: '-' }}{{ $item->formacion ? ' (' . $item->formacion . ')' : '' }}</p>
                </td>
                <td class="right">{{ $pesos($item->precio_base) }}</td>
                <td class="right {{ $descuentoItem > 0 ? 'verde' : '' }}">
                    <p>{{ $pesos($item->precio_con_descuento) }}</p>
                    @if($descuentoItem > 0)
                        <p style="font-size: 8px;">({{ $porcentaje($descuentoItem) }})</p>
                    @endif
                </td>
                <td class="right">{{ number_format($item->cantidad, 0, ',', '.') }}</td>
                <td class="right">{{ $pesos($item->subtotal) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

{{-- totales --}}
<table style="width: 100%; margin-top: 22px;">
    <tr>
        <td style="width: 50%;"></td>
        <td style="width: 50%; vertical-align: top;">
            <div class="tarjeta" style="margin-bottom: 0;">
                <div class="tarjeta-titulo semi">Tu pedido</div>
                <div class="tarjeta-cuerpo">
                    <table class="resumen">
                        <tr><td>Subtotal</td><td class="right">{{ $pesos($subtotalSinDescuento) }}</td></tr>
                        @if($descuentoTotal > 0)
                            <tr class="verde"><td>Descuento ({{ $porcentaje($descuentoTotal) }})</td><td class="right">{{ $pesos($descuentoItems) }}</td></tr>
                        @endif
                        <tr><td>Subtotal</td><td class="right">{{ $pesos($pedido->subtotal) }}</td></tr>
                        <tr class="linea"><td colspan="2">&nbsp;</td></tr>
                        @if(isset($totales['iva_21']))
                            <tr><td>IVA 10,5%</td><td class="right">{{ $pesos($totales['iva_105'] ?? 0) }}</td></tr>
                            <tr><td>IVA 21%</td><td class="right">{{ $pesos($totales['iva_21']) }}</td></tr>
                        @else
                            <tr><td>IVA 21%</td><td class="right">{{ $pesos($pedido->iva) }}</td></tr>
                        @endif
                        <tr class="total">
                            <td>Total <span class="iva-incluido">(IVA incluido)</span></td>
                            <td class="right">{{ $pesos($pedido->total) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </td>
    </tr>
</table>

<div class="pie">
    <p>Los precios se encuentran expresados en pesos argentinos (sujeto a modificación sin previo aviso).</p>
    <p>{{ $datosEmpresa }}</p>
    <p>Generado automáticamente el {{ now()->format('d/m/Y H:i:s') }}</p>
</div>

</body>
</html>
