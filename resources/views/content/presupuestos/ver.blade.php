@php
    $logos = App\Models\Logo::first();
    $contacto = App\Models\Contacto::first();

    $pesos = fn ($n) => '$' . number_format($n, 0, ',', '.');
    $porcentaje = fn ($n) => rtrim(rtrim(number_format($n, 2, ',', '.'), '0'), ',') . '%';

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
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presupuesto N° {{ $presupuesto->id }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-white text-black" style="font-family: 'Montserrat', sans-serif;">
<div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pb-[60px]">

    {{-- cabecera --}}
    <header class="flex items-start justify-between gap-[24px] pt-[33px] pb-[29px] border-b border-[#D9D9D9]">
        <img src="{{ $logos?->foto_nav }}" alt="BBS Motion" class="block w-[140px] sm:w-[184px] h-[51.739px] object-contain object-left">
        <div class="text-right text-[16px] leading-[25px]">
            <p>Presupuesto N° {{ $presupuesto->id }}</p>
            <p>Fecha: {{ $presupuesto->created_at?->format('d/m/Y') }}</p>
        </div>
    </header>

    {{-- título y dólar --}}
    <div class="flex items-start justify-between gap-[24px] pt-[43px]">
        <h1 class="text-[24px] leading-[29px] font-semibold">N° presupuesto {{ $presupuesto->id }}</h1>
        @if($dolar)
            <div class="text-right">
                <p class="text-[16px] leading-[25px]">Dolar BNA</p>
                <p class="mt-[8px] text-[24px] leading-[25px] font-semibold">${{ number_format($dolar, 0, ',', '.') }}</p>
            </div>
        @endif
    </div>

    {{-- productos --}}
    <div class="overflow-x-auto mt-[32px]">
        <table class="w-full min-w-[1224px] table-fixed border-collapse text-[16px]">
            <colgroup>
                <col style="width: 94px">
                <col style="width: 286px">
                <col style="width: 153px">
                <col style="width: 196px">
                <col style="width: 396px">
                <col style="width: 99px">
            </colgroup>
            <thead>
                <tr class="h-[50px] bg-[#F6F8FA] font-semibold uppercase border-b border-[#D9D9D9]">
                    <th></th>
                    <th class="text-left font-semibold">Producto</th>
                    <th class="text-right font-semibold">Precio x unidad</th>
                    <th class="text-right font-semibold">Descuento</th>
                    <th class="text-center font-semibold">Cantidad</th>
                    <th class="text-right font-semibold">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($presupuesto->items as $item)
                    @php
                        $producto = $item->producto;
                        $descuentoItem = $item->precio_base > 0 ? round((1 - $item->precio_con_descuento / $item->precio_base) * 100, 2) : 0;
                    @endphp
                    <tr class="fila-item h-[115px] border-b border-[#D9D9D9]"
                        data-precio-base="{{ $item->precio_base }}"
                        data-precio-con-desc="{{ $item->precio_con_descuento }}">
                        <td>
                            <div class="flex items-center justify-center w-[80px] h-[83px] p-[2px] border border-[#D9D9D9] rounded-[12px] overflow-hidden">
                                <img src="{{ $producto?->foto_url ?? asset('assets/img/default.png') }}" alt="{{ $item->nombre }}" class="w-full h-full object-contain">
                            </div>
                        </td>
                        <td class="align-middle pl-[14px] pr-[16px]">
                            <p class="font-semibold">{{ $item->codigo }}</p>
                            <p class="max-w-[195px] break-words">{{ $item->nombre }}</p>
                        </td>
                        <td class="text-right whitespace-nowrap">{{ $pesos($item->precio_base) }}</td>
                        <td class="text-right whitespace-nowrap {{ $descuentoItem > 0 ? 'text-[#006907]' : '' }}">
                            <p>{{ $pesos($item->precio_con_descuento) }}</p>
                            @if($descuentoItem > 0)
                                <p class="text-[12px]">({{ $porcentaje($descuentoItem) }})</p>
                            @endif
                        </td>
                        <td>
                            <div class="relative mx-auto grid grid-cols-[40px_41px_40px] w-[121px] h-[34px] border border-[#D9D9D9] rounded-[4px]">
                                <button type="button" class="cantidad-menos flex items-center justify-center" aria-label="Quitar uno">
                                    <img src="{{ asset('assets/img/productos/menos.svg') }}" alt="" class="w-[11px] h-[1px] max-w-none">
                                </button>
                                <span class="cant-item flex items-center justify-center font-medium">{{ (int) $item->cantidad }}</span>
                                <button type="button" class="cantidad-mas flex items-center justify-center" aria-label="Agregar uno">
                                    <img src="{{ asset('assets/img/productos/mas.svg') }}" alt="" class="w-[11px] h-[11px] max-w-none">
                                </button>
                                <img src="{{ asset('assets/img/productos/separador-fila.svg') }}" alt="" class="absolute left-[40px] top-[-1px] w-[1px] h-[34px] max-w-none">
                                <img src="{{ asset('assets/img/productos/separador-fila.svg') }}" alt="" class="absolute left-[81px] top-[-1px] w-[1px] h-[34px] max-w-none">
                            </div>
                        </td>
                        <td class="subtotal-item text-right whitespace-nowrap">{{ $pesos($item->subtotal) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-[32px] text-center text-[#5C5C5C] border-b border-[#D9D9D9]">Este presupuesto no tiene productos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- información y totales --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-[24px] mt-[69px]">
        <section class="border border-[#E5E7EB] rounded-[12px] overflow-hidden">
            <h2 class="flex items-center h-[56px] px-[24px] bg-[#F6F8FA] text-[20px] font-semibold">Información importante</h2>
            <div class="px-[24px] pt-[21px] pb-[36px] text-[16px] leading-[25px]">
                <p>-Venta sujeta a disponibilidad de stock</p>
                <p>-Los precios se encuentran expresados en pesos argentinos (sujeto a modificación sin previo aviso)</p>
                <p>-El plazo de entrega se coordina con la empresa</p>
            </div>
        </section>

        <section class="border border-[#E5E7EB] rounded-[12px] overflow-hidden">
            <h2 class="flex items-center h-[56px] px-[24px] bg-[#F6F8FA] text-[20px] font-semibold">Tu presupuesto</h2>
            <div class="flex flex-col gap-[15px] pt-[17px] pb-[22px] px-[24px] text-[18px] leading-[22px]">
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span id="panel-subtotal-sin-desc">{{ $pesos($subtotalSinDescuento) }}</span>
                </div>
                @if($descuentoTotal > 0)
                    <div class="flex justify-between text-[#107219]">
                        <span>Descuento ({{ $porcentaje($descuentoTotal) }})</span>
                        <span id="panel-descuento">{{ $pesos($descuentoItems) }}</span>
                    </div>
                @endif
                <div class="flex justify-between">
                    <span>Subtotal</span>
                    <span id="panel-subtotal">{{ $pesos($presupuesto->subtotal) }}</span>
                </div>
                <hr class="border-[#D9D9D9] my-[7px]">
                <div class="flex justify-between">
                    <span>IVA 21%</span>
                    <span id="panel-iva">{{ $pesos($presupuesto->iva) }}</span>
                </div>
                <div class="flex justify-between items-baseline mt-[1px]">
                    <span class="text-[24px] leading-[29px] font-semibold">Total <span class="text-[15px]">(IVA incluido)</span></span>
                    <span id="panel-total" class="text-[24px] leading-[29px] font-semibold">{{ $pesos($presupuesto->total) }}</span>
                </div>
            </div>
        </section>
    </div>

    {{-- pie --}}
    <footer class="mt-[80px] pt-[24px] border-t border-[#D9D9D9] text-[16px] leading-[25px]">
        <p>{{ $datosEmpresa }}</p>
    </footer>
</div>

{{-- Las cantidades se pueden cambiar para simular otro monto, pero no se guardan --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const formatPesos = n => '$' + Math.round(n).toLocaleString('es-AR');
    const set = (id, valor) => { const el = document.getElementById(id); if (el) el.textContent = formatPesos(valor); };

    function recalcular() {
        let subtotalSinDesc = 0;
        let subtotalConDesc = 0;

        document.querySelectorAll('.fila-item').forEach(fila => {
            const cantidad = parseInt(fila.querySelector('.cant-item').textContent, 10);
            const precioBase = parseFloat(fila.dataset.precioBase);
            const precioConDesc = parseFloat(fila.dataset.precioConDesc);

            fila.querySelector('.subtotal-item').textContent = formatPesos(precioConDesc * cantidad);
            subtotalSinDesc += precioBase * cantidad;
            subtotalConDesc += precioConDesc * cantidad;
        });

        const iva = subtotalConDesc * 0.21;
        set('panel-subtotal-sin-desc', subtotalSinDesc);
        set('panel-descuento', subtotalSinDesc - subtotalConDesc);
        set('panel-subtotal', subtotalConDesc);
        set('panel-iva', iva);
        set('panel-total', subtotalConDesc + iva);
    }

    function cambiarCantidad(boton, delta) {
        const el = boton.closest('.fila-item').querySelector('.cant-item');
        const actual = parseInt(el.textContent, 10);
        const nueva = Math.max(1, actual + delta);
        if (nueva === actual) return;

        el.textContent = nueva;
        recalcular();
    }

    document.querySelectorAll('.cantidad-menos').forEach(b => b.addEventListener('click', () => cambiarCantidad(b, -1)));
    document.querySelectorAll('.cantidad-mas').forEach(b => b.addEventListener('click', () => cambiarCantidad(b, 1)));
});
</script>
</body>
</html>
