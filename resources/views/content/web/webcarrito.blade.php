@extends('layouts.layout-clientes')

@section('title', 'Carrito')

@section('content')
@php
    $descuentos = collect($descuentos ?? [])
        ->map(fn ($d) => (float) $d)
        ->filter(fn ($d) => $d > 0)
        ->values()
        ->all();

    $factorDescuento = 1;
    foreach ($descuentos as $descuento) {
        $factorDescuento *= (1 - ($descuento / 100));
    }

    $descuentoCliente = round((1 - $factorDescuento) * 100, 2);
    $descuentosTexto = collect($descuentos)
        ->map(fn ($d) => rtrim(rtrim(number_format($d, 2, ',', '.'), '0'), ',') . '%')
        ->implode(' + ');
    $productosCarrito = $productosCarrito ?? [];

    // Totales a partir de $productosCarrito
    $subtotalSinDescuento = collect($productosCarrito)->sum(function ($it) {
        $precio = (float) ($it['precio_base'] ?? ($it['producto']->precio_pesos ?? 0));
        return $precio * (int) ($it['cantidad'] ?? 1);
    });
    $subtotalConDescuento = $subtotalSinDescuento * $factorDescuento;
    $descuentoPorItems = $subtotalSinDescuento - $subtotalConDescuento;

    // IVA según la alícuota de cada producto (10,5% o 21%)
    $iva105 = 0;
    $iva21 = 0;
    foreach ($productosCarrito as $it) {
        $precio = (float) ($it['precio_base'] ?? ($it['producto']->precio_pesos ?? 0));
        $baseItem = $precio * $factorDescuento * (int) ($it['cantidad'] ?? 1);
        if ($it['producto']->alicuota_iva === 10.5) {
            $iva105 += $baseItem * 0.105;
        } else {
            $iva21 += $baseItem * 0.21;
        }
    }
    $total = $subtotalConDescuento + $iva105 + $iva21;

    $entregas = ['mercado_flex' => 'Mercado Flex', 'transporte_terminal' => 'Transporte a la terminal', 'retiro_local' => 'Retiro en local (ver mapa)'];
    $formasPago = ['mercado_pago' => 'Mercado Pago', 'transferencia' => 'Transferencia bancaria', 'efectivo' => 'Efectivo (únicamente en local)'];

    $pesos = fn ($n) => '$' . number_format($n, 0, ',', '.');
@endphp

<div class="w-full text-black" style="font-family: 'Montserrat', sans-serif;">
    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pt-[30px] pb-[80px]">

        {{-- puntos y cotización --}}
        @include('content.web.partials.puntos-dolar')

        {{-- productos --}}
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1224px] table-fixed border-collapse text-[16px]">
                <colgroup>
                    <col style="width: 94px">
                    <col style="width: 235px">
                    <col style="width: 93px">
                    <col style="width: 131px">
                    <col style="width: 133px">
                    <col style="width: 150px">
                    <col style="width: 120px">
                    <col style="width: 84px">
                    <col style="width: 113px">
                    <col style="width: 71px">
                </colgroup>
                <thead>
                    <tr class="h-[50px] bg-[#F6F8FA] font-semibold uppercase leading-[19px]">
                        <th></th>
                        <th class="text-left">Producto</th>
                        <th class="text-right">Precio USD</th>
                        <th class="text-right">Precio ARS</th>
                        <th class="text-right">Descuento</th>
                        <th class="pl-[28px]"><div class="w-[121px] text-center">Cantidad</div></th>
                        <th class="text-right">Subtotal</th>
                        <th class="text-right">Stock<br>físico</th>
                        <th class="text-right">Stock<br>tránsito</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="tbody-carrito">
                    @forelse($productosCarrito as $row)
                        @php
                            $producto = $row['producto'];
                            $cantidad = max(1, (int) ($row['cantidad'] ?? 1));
                            $precio = (float) ($row['precio_base'] ?? $producto->precio_pesos);
                            $precioConDesc = $precio * $factorDescuento;
                        @endphp
                        <tr class="border-b border-[#D9D9D9]" data-item-key="{{ $producto->id }}">
                            <td class="py-[16px] align-middle">
                                <div class="flex items-center justify-center w-[80px] h-[83px] p-[2px] border border-[#D9D9D9] rounded-[12px] overflow-hidden">
                                    <img src="{{ $producto->foto_url }}" alt="{{ $producto->nombre }}" class="w-full h-full object-contain">
                                </div>
                            </td>

                            <td class="align-middle pr-[16px]">
                                <p class="font-semibold">{{ $producto->codigo }}</p>
                                <p class="max-w-[195px] break-words">{{ $producto->nombre }}</p>
                            </td>

                            <td class="align-middle text-right whitespace-nowrap">USD {{ number_format((float) $producto->precio, 2, ',', '.') }}</td>

                            <td class="align-middle text-right whitespace-nowrap">{{ $pesos($precio) }}</td>

                            <td class="align-middle text-right whitespace-nowrap {{ !empty($descuentosTexto) ? 'text-[#006907]' : '' }}">
                                <p>{{ $pesos($precioConDesc) }}</p>
                                @if(!empty($descuentosTexto))
                                    <p class="text-[12px]">({{ $descuentosTexto }})</p>
                                @endif
                            </td>

                            <td class="align-middle pl-[28px]">
                                <div class="relative grid grid-cols-[40px_41px_40px] w-[121px] h-[34px] border border-[#D9D9D9] rounded-[4px]">
                                    <button type="button" class="cantidad-menos flex items-center justify-center" aria-label="Quitar uno">
                                        <img src="{{ asset('assets/img/productos/menos.svg') }}" alt="" class="w-[11px] h-[1px] max-w-none">
                                    </button>
                                    <span class="cant-item flex items-center justify-center font-medium"
                                          data-key="{{ $producto->id }}"
                                          data-precio-original="{{ $precio }}"
                                          data-precio-con-desc="{{ $precioConDesc }}"
                                          data-alicuota-iva="{{ $producto->alicuota_iva }}">{{ $cantidad }}</span>
                                    <button type="button" class="cantidad-mas flex items-center justify-center" aria-label="Agregar uno">
                                        <img src="{{ asset('assets/img/productos/mas.svg') }}" alt="" class="w-[11px] h-[11px] max-w-none">
                                    </button>
                                    <img src="{{ asset('assets/img/productos/separador-fila.svg') }}" alt="" class="absolute left-[40px] top-[-1px] w-[1px] h-[34px] max-w-none pointer-events-none">
                                    <img src="{{ asset('assets/img/productos/separador-fila.svg') }}" alt="" class="absolute left-[81px] top-[-1px] w-[1px] h-[34px] max-w-none pointer-events-none">
                                </div>
                            </td>

                            <td class="align-middle text-right whitespace-nowrap">
                                <span class="precio-con-desc-carrito" data-key="{{ $producto->id }}">{{ $pesos($precioConDesc * $cantidad) }}</span>
                            </td>

                            <td class="align-middle text-right {{ (int) $producto->stock_fisico <= 0 ? 'text-[#FF0000]' : '' }}">{{ (int) $producto->stock_fisico }}</td>

                            <td class="align-middle text-right">{{ (int) $producto->stock_transito }}</td>

                            <td class="align-middle pl-[27px]">
                                <button type="button" aria-label="Quitar del carrito" class="btn-eliminar relative block w-[42px] h-[42px] rounded-[6px] bg-gradient-to-r from-[#277AE5] to-[#144BA0] transition-opacity hover:opacity-80" data-key="{{ $producto->id }}">
                                    <img src="{{ asset('assets/img/productos/papelera.svg') }}" alt="" class="absolute left-[12px] top-[11px] w-[18px] h-[20px] max-w-none">
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr id="fila-vacia">
                            <td colspan="10" class="py-[32px] text-center text-[#5C5C5C] border-b border-[#D9D9D9]">El carrito está vacío.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <a href="{{ route('web-productos') }}" class="inline-flex items-center justify-center mt-[30px] p-[16px] border border-black rounded-[22px] text-[16px] leading-[19px] font-semibold uppercase transition-colors hover:bg-black hover:text-white">
            + Agregar productos
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-[24px] gap-y-[24px] mt-[74px]">

            {{-- columna izquierda --}}
            <div class="flex flex-col gap-[24px]">
                <section class="border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                    <h2 class="flex items-center h-[56px] px-[24px] bg-[#F6F8FA] text-[20px] font-semibold">Información importante</h2>
                    <div class="px-[24px] pt-[21px] pb-[36px] text-[16px] leading-[25px]">
                        <p>-IMPORTANTE!!!! LOS PRECIOS ESTAN SUJETOS A MODIFICACION SIN PREVIO AVISO.</p>
                        <p>-EL CIERRE DEL CARRITO NO GARANTIZA PRECIO DE VENTA.</p>
                        <p>-Los precios se encuentran expresados en pesos argentinos (sujeto a modificación sin previo aviso)</p>
                        <p>-El plazo de entrega de la mercadería es de 48/72 hs.</p>
                    </div>
                </section>

                <section class="border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                    <h2 class="flex items-center h-[56px] px-[24px] bg-[#F6F8FA] text-[20px] font-semibold">Escribinos un mensaje</h2>
                    <textarea id="mensaje-pedido"
                        class="block w-full h-[145px] px-[14px] pt-[16px] text-[16px] placeholder:text-black resize-none focus:outline-none"
                        placeholder="Dias especiales de entrega, cambios de domicilio, expresos, requerimientos especiales en la mercaderia, exenciones."></textarea>
                </section>

                <section class="border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                    <h2 class="flex items-center h-[56px] px-[24px] bg-[#F6F8FA] text-[20px] font-semibold">Adjuntar pedido</h2>
                    <label class="flex items-center justify-between h-[48px] pl-[24px] pr-[36px] cursor-pointer">
                        <span class="text-[16px]" id="file-name">Seleccionar archivo</span>
                        <span class="text-[14px] font-bold uppercase">Adjuntar</span>
                        <input type="file" id="archivo-pedido" class="hidden" onchange="document.getElementById('file-name').textContent = this.files[0]?.name || 'Seleccionar archivo'">
                    </label>
                </section>
            </div>

            {{-- columna derecha --}}
            <div class="flex flex-col gap-[24px]">
                <section class="border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                    <h2 class="flex items-center h-[56px] px-[24px] bg-[#F6F8FA] text-[20px] font-semibold">Forma de entrega</h2>
                    <div class="flex flex-col gap-[18px] pt-[21px] pb-[25px] px-[23px]">
                        @foreach($entregas as $valor => $texto)
                            <label class="flex items-center gap-[21px] h-[20px] text-[18px] cursor-pointer">
                                <input type="radio" name="entrega" value="{{ $valor }}" class="sr-only peer" @checked($valor === 'transporte_terminal')>
                                <img src="{{ asset('assets/img/productos/radio.svg') }}" alt="" class="w-[20px] h-[20px] max-w-none peer-checked:hidden">
                                <img src="{{ asset('assets/img/productos/radio-activo.svg') }}" alt="" class="w-[20px] h-[20px] max-w-none hidden peer-checked:block">
                                <span>{{ $texto }}</span>
                            </label>
                            @if($valor === 'retiro_local')
                                <div class="-mt-[8px] ml-[41px] text-[13px] leading-[22px] text-[#222] whitespace-pre-line">{{ \App\Models\Contacto::textoRetiroLocal() }}</div>
                            @endif
                        @endforeach
                    </div>
                </section>

                <section class="border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                    <h2 class="flex items-center h-[56px] px-[24px] bg-[#F6F8FA] text-[20px] font-semibold">Forma de pago</h2>
                    <div class="flex flex-col gap-[18px] pt-[21px] pb-[25px] px-[23px]">
                        @foreach($formasPago as $valor => $texto)
                            <label class="flex items-center gap-[21px] h-[20px] text-[18px] cursor-pointer">
                                <input type="radio" name="forma_pago" value="{{ $valor }}" class="sr-only peer" @checked($valor === 'transferencia')>
                                <img src="{{ asset('assets/img/productos/radio.svg') }}" alt="" class="w-[20px] h-[20px] max-w-none peer-checked:hidden">
                                <img src="{{ asset('assets/img/productos/radio-activo.svg') }}" alt="" class="w-[20px] h-[20px] max-w-none hidden peer-checked:block">
                                <span>{{ $texto }}</span>
                            </label>
                        @endforeach
                    </div>
                </section>

                <section class="border border-[#E5E7EB] rounded-[12px] overflow-hidden">
                    <h2 class="flex items-center h-[56px] px-[24px] bg-[#F6F8FA] text-[20px] font-semibold">Tu pedido</h2>
                    <div class="flex flex-col gap-[15px] pt-[17px] pb-[22px] pl-[24px] pr-[30px] text-[18px] leading-[22px]">
                        <div class="flex justify-between">
                            <span>Subtotal</span>
                            <span id="panel-subtotal-sin-desc">{{ $pesos($subtotalSinDescuento) }}</span>
                        </div>
                        @if($descuentoCliente > 0)
                            <div class="flex justify-between text-[#107219]">
                                <span>Descuento ({{ rtrim(rtrim(number_format($descuentoCliente, 2, ',', '.'), '0'), ',') }}%)</span>
                                <span id="panel-descuento-items">{{ $pesos($descuentoPorItems) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span>Subtotal</span>
                            <span id="panel-subtotal">{{ $pesos($subtotalConDescuento) }}</span>
                        </div>
                        <img src="{{ asset('assets/img/productos/resumen-linea.svg') }}" alt="" class="block w-full h-[1px] max-w-none my-[7px]">
                        <div class="flex justify-between">
                            <span>IVA 10,5%</span>
                            <span id="panel-iva-105">{{ $pesos($iva105) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>IVA 21%</span>
                            <span id="panel-iva-21">{{ $pesos($iva21) }}</span>
                        </div>
                        <div class="flex justify-between items-baseline mt-[1px]">
                            <span class="text-[24px] leading-[29px] font-semibold">Total <span class="text-[15px]">(IVA incluido)</span></span>
                            <span id="panel-total" class="text-[24px] leading-[29px] font-semibold">{{ $pesos($total) }}</span>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        {{-- acciones --}}
        <div class="flex flex-wrap items-center justify-between gap-[16px] mt-[17px]">
            <button id="btn-cancelar-pedido" type="button" class="p-[16px] border border-black rounded-[22px] text-[16px] leading-[19px] font-semibold uppercase transition-colors hover:bg-black hover:text-white">
                Cancelar
            </button>
            <div class="flex flex-wrap items-center gap-[24px]">
                <button id="btn-realizar-presupuesto" type="button" class="p-[16px] border border-black rounded-[22px] text-[16px] leading-[19px] font-semibold uppercase transition-colors hover:bg-black hover:text-white">
                    Realizar presupuesto
                </button>
                <button id="btn-realizar-pedido" type="button" class="w-[246px] max-w-full p-[16px] bg-black border border-black rounded-[22px] text-[16px] leading-[19px] font-semibold uppercase text-white transition-opacity hover:opacity-80">
                    Realizar pedido
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const factorDescuento = {{ json_encode($factorDescuento) }};
        const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

        function formatPesos(n) {
            return '$' + Number(n).toLocaleString('es-AR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        }

        function post(url, datos) {
            return fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify(datos)
            }).then(r => r.json());
        }

        function actualizarGlobo(cantidad) {
            const globo = document.getElementById('cart-count');
            if (!globo) return;
            globo.querySelector('.cart-count-num').textContent = cantidad;
            globo.classList.toggle('hidden', cantidad === 0);
        }

        function filaVacia() {
            const tbody = document.getElementById('tbody-carrito');
            if (!tbody.querySelector('tr[data-item-key]') && !document.getElementById('fila-vacia')) {
                tbody.insertAdjacentHTML('beforeend', '<tr id="fila-vacia"><td colspan="10" class="py-[32px] text-center text-[#5C5C5C] border-b border-[#D9D9D9]">El carrito está vacío.</td></tr>');
            }
        }

        function actualizarSubtotales() {
            let subtotalSinDesc = 0;
            let subtotalConDesc = 0;
            let iva105 = 0;
            let iva21 = 0;
            let filas = 0;

            document.querySelectorAll('#tbody-carrito .cant-item').forEach(el => {
                const cantidad = parseInt(el.textContent, 10);
                const precio = parseFloat(el.dataset.precioOriginal || 0);
                const precioConDesc = parseFloat(el.dataset.precioConDesc || (precio * factorDescuento));

                el.closest('tr').querySelector('.precio-con-desc-carrito').textContent = formatPesos(precioConDesc * cantidad);
                subtotalSinDesc += precio * cantidad;
                subtotalConDesc += precioConDesc * cantidad;
                if (parseFloat(el.dataset.alicuotaIva) === 10.5) {
                    iva105 += precioConDesc * cantidad * 0.105;
                } else {
                    iva21 += precioConDesc * cantidad * 0.21;
                }
                filas++;
            });

            const set = (id, valor) => { const el = document.getElementById(id); if (el) el.textContent = formatPesos(valor); };

            set('panel-subtotal-sin-desc', subtotalSinDesc);
            set('panel-descuento-items', subtotalSinDesc - subtotalConDesc);
            set('panel-subtotal', subtotalConDesc);
            set('panel-iva-105', iva105);
            set('panel-iva-21', iva21);
            set('panel-total', subtotalConDesc + iva105 + iva21);
            actualizarGlobo(filas);
        }

        // cantidad (− / +): se guarda en el servidor
        function cambiarCantidad(boton, delta) {
            const el = boton.closest('tr').querySelector('.cant-item');
            const cantidad = Math.max(1, parseInt(el.textContent, 10) + delta);
            if (cantidad === parseInt(el.textContent, 10)) return;

            el.textContent = cantidad;
            actualizarSubtotales();
            post('{{ route('web-carrito.actualizar') }}', { producto_id: el.dataset.key, cantidad }).catch(err => console.error(err));
        }

        document.querySelectorAll('.cantidad-menos').forEach(b => b.addEventListener('click', () => cambiarCantidad(b, -1)));
        document.querySelectorAll('.cantidad-mas').forEach(b => b.addEventListener('click', () => cambiarCantidad(b, 1)));

        // quitar producto
        document.querySelectorAll('.btn-eliminar').forEach(btn => {
            btn.addEventListener('click', function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Eliminar producto',
                    text: '¿Querés eliminar este producto del carrito?',
                    showCancelButton: true,
                    confirmButtonText: 'Eliminar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#0D1A35'
                }).then(result => {
                    if (!result.isConfirmed) return;

                    post('{{ route('web-carrito.eliminar') }}', { producto_id: this.dataset.key })
                        .then(data => {
                            if (data.success) {
                                this.closest('tr').remove();
                                filaVacia();
                                actualizarSubtotales();
                            }
                        })
                        .catch(err => console.error(err));
                });
            });
        });

        function vaciarTabla() {
            document.querySelectorAll('#tbody-carrito tr[data-item-key]').forEach(row => row.remove());
            filaVacia();
            actualizarSubtotales();
        }

        // cancelar: vacía el carrito
        document.getElementById('btn-cancelar-pedido')?.addEventListener('click', function() {
            Swal.fire({
                icon: 'warning',
                title: 'Cancelar pedido',
                text: '¿Estás seguro de que deseas vaciar el carrito?',
                showCancelButton: true,
                confirmButtonText: 'Vaciar carrito',
                cancelButtonText: 'Volver',
                confirmButtonColor: '#0D1A35'
            }).then(result => {
                if (!result.isConfirmed) return;

                post('{{ route('web-carrito.vaciar') }}', {})
                    .then(data => { if (data.success) vaciarTabla(); })
                    .catch(err => console.error(err));
            });
        });

        // realizar presupuesto: se guarda para verlo en "Mis presupuestos" (el carrito no se vacía)
        document.getElementById('btn-realizar-presupuesto')?.addEventListener('click', function() {
            const entrega = document.querySelector('input[name="entrega"]:checked')?.value || '';
            const formaPago = document.querySelector('input[name="forma_pago"]:checked')?.value || '';

            if (!document.querySelector('#tbody-carrito tr[data-item-key]')) {
                Swal.fire({ icon: 'warning', title: 'Carrito vacío', text: 'Agregá productos para realizar un presupuesto.', confirmButtonColor: '#0D1A35' });
                return;
            }
            if (!entrega || !formaPago) {
                Swal.fire({ icon: 'warning', title: 'Faltan datos', text: 'Elegí la forma de entrega y la forma de pago.', confirmButtonColor: '#0D1A35' });
                return;
            }

            const btn = this;
            btn.disabled = true;

            post('{{ route('web-carrito.presupuesto') }}', {
                entrega,
                forma_pago: formaPago,
                mensaje: document.getElementById('mensaje-pedido')?.value || ''
            })
            .then(data => {
                btn.disabled = false;
                if (!data.success) {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Ocurrió un error.', confirmButtonColor: '#0D1A35' });
                    return;
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Presupuesto realizado',
                    text: 'Lo encontrás en Mis presupuestos.',
                    showCancelButton: true,
                    confirmButtonText: 'Ver mis presupuestos',
                    cancelButtonText: 'Seguir acá',
                    confirmButtonColor: '#0D1A35'
                }).then(result => { if (result.isConfirmed) window.location.href = data.redirect; });
            })
            .catch(() => {
                btn.disabled = false;
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor.', confirmButtonColor: '#0D1A35' });
            });
        });

        // realizar pedido
        document.getElementById('btn-realizar-pedido')?.addEventListener('click', function() {
            const entrega = document.querySelector('input[name="entrega"]:checked')?.value || '';
            const formaPago = document.querySelector('input[name="forma_pago"]:checked')?.value || '';

            if (!entrega) {
                Swal.fire({ icon: 'warning', title: 'Falta seleccionar entrega', text: 'Por favor elegí una opción de entrega.', confirmButtonColor: '#0D1A35' });
                return;
            }
            if (!formaPago) {
                Swal.fire({ icon: 'warning', title: 'Falta forma de pago', text: 'Por favor elegí una forma de pago.', confirmButtonColor: '#0D1A35' });
                return;
            }

            const mensaje = document.getElementById('mensaje-pedido')?.value || '';
            const archivo = document.getElementById('archivo-pedido')?.files[0] || null;

            const formData = new FormData();
            formData.append('entrega', entrega);
            formData.append('forma_pago', formaPago);
            formData.append('mensaje', mensaje);
            formData.append('_token', CSRF);
            if (archivo) formData.append('archivo', archivo);

            const btn = this;
            btn.disabled = true;
            btn.textContent = 'Enviando...';

            fetch('{{ route('web-carrito.completar') }}', {
                method: 'POST',
                body: formData   // NO pongas Content-Type manual; FormData lo maneja solo
            })
            .then(r => r.json())
            .then(data => {
                // Mercado Pago: el pedido queda registrado y se sigue en la pantalla de pago
                if (data.success && data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }
                if (data.success) {
                    const puntosGanados = parseInt(data.puntos_ganados, 10) || 0;
                    const misPuntos = document.getElementById('mis-puntos');
                    if (misPuntos && data.puntos_total !== undefined) {
                        misPuntos.textContent = Number(data.puntos_total).toLocaleString('es-AR');
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Pedido realizado con éxito',
                        text: 'Te enviamos la confirmación por email.' + (puntosGanados > 0 ? ' Sumaste ' + puntosGanados.toLocaleString('es-AR') + ' puntos.' : ''),
                        confirmButtonColor: '#0D1A35'
                    }).then(() => {
                        vaciarTabla();
                        btn.disabled = false;
                        btn.textContent = 'Realizar pedido';
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Ocurrió un error.', confirmButtonColor: '#0D1A35' });
                    btn.disabled = false;
                    btn.textContent = 'Realizar pedido';
                }
            })
            .catch(() => {
                Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor.', confirmButtonColor: '#0D1A35' });
                btn.disabled = false;
                btn.textContent = 'Realizar pedido';
            });
        });

        actualizarSubtotales();
    });
</script>
@endsection
