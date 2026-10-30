@php
    // Misma identidad que la web: navy #0D1A35, rojo #DE2222, bordes #D9D9D9, fondo #F6F8FA y Montserrat
    $contacto = \App\Models\Contacto::first();
    $logo = \App\Models\Logo::first();
    $logoUrl = $logo?->foto_footer ? url($logo->foto_footer) : null;
    $esDuenos = $destinatarioTipo === 'duenos';
    $fuente = "'Montserrat', Arial, Helvetica, sans-serif";
    $dinero = fn ($valor) => '$ ' . number_format($valor, 2, ',', '.');
    $totales = $pedido['totales'];

    $cliente = [
        'Usuario' => $pedido['cliente']['usuario'] ?? '-',
        'Email' => $pedido['cliente']['email'] ?? '-',
        'Empresa' => $pedido['cliente']['empresa'] ?? '-',
        'CUIT' => $pedido['cliente']['cuit'] ?? '-',
        'Teléfono' => $pedido['cliente']['telefono'] ?? '-',
    ];

    $datosPedido = ['Entrega' => $pedido['entrega_label'] ?? '-'];
    if (!empty($pedido['direccion'])) {
        $datosPedido['Dirección'] = $pedido['direccion'];
    }
    $datosPedido['Forma de pago'] = $pedido['forma_pago_label'] ?? '-';
    $datosPedido['Mensaje'] = $pedido['mensaje'] ?: '-';

    $resumen = [
        ['Subtotal sin descuento', $dinero($totales['subtotal_sin_descuento'])],
        ['Descuentos cliente', $dinero($totales['descuento_items'])],
    ];
    if (($totales['descuento_forma_pago'] ?? 0) > 0) {
        $resumen[] = ['Descuento por forma de pago', $dinero($totales['descuento_forma_pago'])];
    }
    $resumen[] = ['Subtotal', $dinero($totales['subtotal'])];
    if (isset($totales['iva_21'])) {
        $resumen[] = ['IVA 10,5%', $dinero($totales['iva_105'])];
        $resumen[] = ['IVA 21%', $dinero($totales['iva_21'])];
    } else {
        $resumen[] = ['IVA 21%', $dinero($totales['iva'])];
    }
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $esDuenos ? 'Nuevo pedido web recibido' : 'Confirmación de pedido' }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body style="margin: 0; padding: 0; background-color: #F6F8FA; font-family: {{ $fuente }}; color: #0D1A35;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #F6F8FA;">
    <tr>
        <td align="center" style="padding: 24px 12px;">
            <table role="presentation" width="640" cellpadding="0" cellspacing="0" border="0" style="width: 100%; max-width: 640px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);">

                {{-- Cabecera: barra navy con el logo, igual que el footer de la web --}}
                <tr>
                    <td align="center" style="background-color: #0D1A35; background-image: linear-gradient(180deg, #0D1A35 0%, #060912 100%); padding: 32px 24px; border-bottom: 4px solid #DE2222;">
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="BBS Motion" height="56" style="display: block; height: 56px; max-width: 240px; width: auto; border: 0;">
                        @else
                            <span style="font-family: {{ $fuente }}; font-size: 26px; font-weight: 700; color: #ffffff; letter-spacing: 1px;">BBS Motion</span>
                        @endif
                    </td>
                </tr>

                {{-- Título --}}
                <tr>
                    <td style="padding: 36px 32px 8px 32px;">
                        <p style="margin: 0 0 8px 0; font-family: {{ $fuente }}; font-size: 13px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; color: #DE2222;">
                            {{ $esDuenos ? 'Pedido web' : 'Tu pedido' }}
                        </p>
                        <h1 style="margin: 0 0 12px 0; font-family: {{ $fuente }}; font-size: 26px; line-height: 32px; font-weight: 600; color: #0D1A35;">
                            {{ $esDuenos ? 'Nuevo pedido web recibido' : 'Confirmación de pedido' }}
                        </h1>
                        <p style="margin: 0; font-family: {{ $fuente }}; font-size: 16px; line-height: 24px; color: #5C5C5C;">
                            @if($esDuenos)
                                Se recibió un nuevo pedido desde el sitio.
                            @else
                                Recibimos tu pedido correctamente. Te contactaremos para coordinar los detalles.
                            @endif
                        </p>
                    </td>
                </tr>

                {{-- Cliente --}}
                <tr>
                    <td style="padding: 28px 32px 0 32px;">
                        <h2 style="margin: 0 0 12px 0; font-family: {{ $fuente }}; font-size: 18px; line-height: 22px; font-weight: 600; color: #0D1A35;">Cliente</h2>
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border: 1px solid #D9D9D9; border-radius: 16px; background-color: #F6F8FA;">
                            @foreach($cliente as $etiqueta => $valor)
                                <tr>
                                    <td width="34%" valign="top" style="padding: {{ $loop->first ? '16px' : '6px' }} 8px {{ $loop->last ? '16px' : '6px' }} 20px; font-family: {{ $fuente }}; font-size: 14px; line-height: 20px; font-weight: 600; color: #0D1A35;">{{ $etiqueta }}</td>
                                    <td valign="top" style="padding: {{ $loop->first ? '16px' : '6px' }} 20px {{ $loop->last ? '16px' : '6px' }} 0; font-family: {{ $fuente }}; font-size: 14px; line-height: 20px; color: #5C5C5C; word-break: break-word;">{{ $valor }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>

                {{-- Datos del pedido --}}
                <tr>
                    <td style="padding: 28px 32px 0 32px;">
                        <h2 style="margin: 0 0 12px 0; font-family: {{ $fuente }}; font-size: 18px; line-height: 22px; font-weight: 600; color: #0D1A35;">Datos del pedido</h2>
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border: 1px solid #D9D9D9; border-radius: 16px; background-color: #F6F8FA;">
                            @foreach($datosPedido as $etiqueta => $valor)
                                <tr>
                                    <td width="34%" valign="top" style="padding: {{ $loop->first ? '16px' : '6px' }} 8px {{ $loop->last ? '16px' : '6px' }} 20px; font-family: {{ $fuente }}; font-size: 14px; line-height: 20px; font-weight: 600; color: #0D1A35;">{{ $etiqueta }}</td>
                                    <td valign="top" style="padding: {{ $loop->first ? '16px' : '6px' }} 20px {{ $loop->last ? '16px' : '6px' }} 0; font-family: {{ $fuente }}; font-size: 14px; line-height: 20px; color: #5C5C5C; word-break: break-word;">{{ $valor }}</td>
                                </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>

                {{-- Productos --}}
                <tr>
                    <td style="padding: 28px 32px 0 32px;">
                        <h2 style="margin: 0 0 12px 0; font-family: {{ $fuente }}; font-size: 18px; line-height: 22px; font-weight: 600; color: #0D1A35;">Productos</h2>
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse: separate; border-spacing: 0; border: 1px solid #D9D9D9; border-radius: 16px; overflow: hidden;">
                            <thead>
                                <tr style="background-color: #0D1A35;">
                                    <th align="left" style="padding: 12px 10px 12px 14px; font-family: {{ $fuente }}; font-size: 12px; font-weight: 600; color: #ffffff; background-color: #0D1A35;">Código</th>
                                    <th align="left" style="padding: 12px 10px; font-family: {{ $fuente }}; font-size: 12px; font-weight: 600; color: #ffffff; background-color: #0D1A35;">Producto</th>
                                    <th align="right" style="padding: 12px 10px; font-family: {{ $fuente }}; font-size: 12px; font-weight: 600; color: #ffffff; background-color: #0D1A35;">Precio</th>
                                    <th align="right" style="padding: 12px 10px; font-family: {{ $fuente }}; font-size: 12px; font-weight: 600; color: #ffffff; background-color: #0D1A35;">Precio c/ desc.</th>
                                    <th align="right" style="padding: 12px 10px; font-family: {{ $fuente }}; font-size: 12px; font-weight: 600; color: #ffffff; background-color: #0D1A35;">Cant.</th>
                                    <th align="right" style="padding: 12px 14px 12px 10px; font-family: {{ $fuente }}; font-size: 12px; font-weight: 600; color: #ffffff; background-color: #0D1A35;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pedido['items'] as $item)
                                    @php $borde = $loop->last ? '' : 'border-bottom: 1px solid #D9D9D9;'; @endphp
                                    <tr style="background-color: {{ $loop->even ? '#F6F8FA' : '#ffffff' }};">
                                        <td valign="top" style="padding: 12px 10px 12px 14px; {{ $borde }} font-family: {{ $fuente }}; font-size: 13px; font-weight: 600; color: #0D1A35;">{{ $item['codigo'] }}</td>
                                        <td valign="top" style="padding: 12px 10px; {{ $borde }} font-family: {{ $fuente }}; font-size: 13px; color: #0D1A35;">{{ $item['nombre'] }}</td>
                                        <td align="right" valign="top" style="padding: 12px 10px; {{ $borde }} font-family: {{ $fuente }}; font-size: 13px; color: #5C5C5C; white-space: nowrap;">{{ $dinero($item['precio_base']) }}</td>
                                        <td align="right" valign="top" style="padding: 12px 10px; {{ $borde }} font-family: {{ $fuente }}; font-size: 13px; color: #5C5C5C; white-space: nowrap;">{{ $dinero($item['precio_con_descuento']) }}</td>
                                        <td align="right" valign="top" style="padding: 12px 10px; {{ $borde }} font-family: {{ $fuente }}; font-size: 13px; color: #0D1A35;">{{ $item['cantidad'] }}</td>
                                        <td align="right" valign="top" style="padding: 12px 14px 12px 10px; {{ $borde }} font-family: {{ $fuente }}; font-size: 13px; font-weight: 600; color: #0D1A35; white-space: nowrap;">{{ $dinero($item['subtotal']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>

                {{-- Resumen --}}
                <tr>
                    <td style="padding: 28px 32px 36px 32px;">
                        <h2 style="margin: 0 0 12px 0; font-family: {{ $fuente }}; font-size: 18px; line-height: 22px; font-weight: 600; color: #0D1A35;">Resumen</h2>
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border: 1px solid #D9D9D9; border-radius: 16px;">
                            @foreach($resumen as [$etiqueta, $valor])
                                <tr>
                                    <td style="padding: {{ $loop->first ? '16px' : '6px' }} 8px 6px 20px; font-family: {{ $fuente }}; font-size: 14px; line-height: 20px; color: #5C5C5C;">{{ $etiqueta }}</td>
                                    <td align="right" style="padding: {{ $loop->first ? '16px' : '6px' }} 20px 6px 8px; font-family: {{ $fuente }}; font-size: 14px; line-height: 20px; color: #0D1A35; white-space: nowrap;">{{ $valor }}</td>
                                </tr>
                            @endforeach
                            <tr>
                                <td style="padding: 14px 8px 14px 20px; border-top: 1px solid #D9D9D9; background-color: #0D1A35; border-radius: 0 0 0 15px; font-family: {{ $fuente }}; font-size: 16px; line-height: 22px; font-weight: 700; color: #ffffff;">Total</td>
                                <td align="right" style="padding: 14px 20px 14px 8px; border-top: 1px solid #D9D9D9; background-color: #0D1A35; border-radius: 0 0 15px 0; font-family: {{ $fuente }}; font-size: 20px; line-height: 22px; font-weight: 700; color: #ffffff; white-space: nowrap;">{{ $dinero($totales['total']) }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                {{-- Pie: datos de contacto, como el footer de la web --}}
                <tr>
                    <td align="center" style="background-color: #0D1A35; background-image: linear-gradient(180deg, #0D1A35 0%, #060912 100%); padding: 28px 24px;">
                        <p style="margin: 0 0 12px 0; font-family: {{ $fuente }}; font-size: 16px; line-height: 20px; font-weight: 600; color: #ffffff;">Datos de contacto</p>
                        <p style="margin: 0; font-family: {{ $fuente }}; font-size: 14px; line-height: 22px; color: #ffffff;">
                            @if($contacto?->direccion){{ $contacto->direccion }}<br>@endif
                            @if($contacto?->whatssap)WhatsApp: {{ $contacto->whatssap }}<br>@endif
                            @if($contacto?->telefono)Tel: {{ $contacto->telefono }}<br>@endif
                            @if($contacto?->mail)<a href="mailto:{{ $contacto->mail }}" style="color: #ffffff; text-decoration: underline;">{{ $contacto->mail }}</a>@endif
                        </p>
                        <p style="margin: 20px 0 0 0; padding-top: 16px; border-top: 1px solid rgba(255, 255, 255, 0.25); font-family: {{ $fuente }}; font-size: 12px; line-height: 18px; color: rgba(255, 255, 255, 0.8);">
                            © Copyright {{ date('Y') }} <strong>BBS Motion</strong>. Todos los derechos reservados
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
