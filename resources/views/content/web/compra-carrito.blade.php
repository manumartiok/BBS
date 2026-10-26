@extends('layouts.web')

@section('title', 'Carrito')

@section('content')
@php
    $columnas = 'grid-cols-[111px_138px_131px_69px_121px_141px_97px]';
@endphp

<div class="w-full text-black" style="font-family: 'Montserrat', sans-serif;">

    {{-- breadcrumb --}}
    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pt-[15px]">
        <p class="text-[12px] leading-[15px]"><a class="font-semibold" href="{{ route('home') }}">Inicio /</a> Carrito</p>
    </div>

    {{-- pasos de la compra --}}
    <div class="mt-[22px]">
        @include('content.web.partials.compra-pasos', ['activo' => 1])
    </div>

    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pt-[55px] pb-[120px]">
        <div class="flex flex-col gap-[24px] lg:grid lg:grid-cols-[808fr_392fr] lg:items-start">

            {{-- productos --}}
            <div>
                <div class="overflow-x-auto">
                    <div class="min-w-[808px]">
                        <div class="grid {{ $columnas }} mb-[10px] text-[16px] leading-[21px] font-medium">
                            <span></span>
                            <span>Descripción</span>
                            <span class="text-right">Precio</span>
                            <span></span>
                            <span class="text-center">Cantidad</span>
                            <span class="text-right">Subtotal</span>
                            <span></span>
                        </div>

                        <div id="compra-items" class="border border-[#D9D9D9] rounded-[12px] overflow-hidden">
                            <p class="px-[24px] py-[40px] text-[16px] text-[#5C5C5C]">Cargando…</p>
                        </div>
                    </div>
                </div>

                <a href="{{ route('productos') }}"
                   class="inline-flex items-center justify-center mt-[30px] p-[16px] border border-black rounded-[22px] text-[16px] leading-[19px] font-semibold uppercase transition-colors hover:bg-black hover:text-white">
                    + Agregar productos
                </a>
            </div>

            {{-- resumen --}}
            <div>
                <section class="border border-[#D9D9D9] rounded-[12px] px-[13px]">
                    <h2 class="h-[69px] pt-[27px] border-b border-[#D9D9D9] text-[24px] leading-[29px] font-semibold pl-[10px]">Pedido</h2>
                    <div class="flex items-baseline justify-between gap-[8px] pt-[24px] pb-[24px] pl-[10px] text-[24px] leading-[29px] font-medium">
                        <span>Subtotal</span>
                        <span id="compra-subtotal">$0</span>
                    </div>
                </section>

                <div class="flex justify-end mt-[24px]">
                    <button type="button" id="compra-siguiente" disabled
                            class="p-[16px] bg-black border border-black rounded-[22px] text-[16px] leading-[19px] font-semibold uppercase text-white transition-opacity hover:opacity-80 disabled:opacity-40 disabled:cursor-not-allowed">
                        Siguiente
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    const URLS = {
        items: @json(route('carrito-publico')),
        actualizar: @json(route('carrito-publico.actualizar')),
        eliminar: @json(route('carrito-publico.eliminar')),
    };
    const ICONOS = {
        papelera: @json(asset('assets/img/carrito/papelera.svg')),
        menos: @json(asset('assets/img/productos/menos.svg')),
        mas: @json(asset('assets/img/productos/mas.svg')),
        separador: @json(asset('assets/img/productos/separador-fila.svg')),
    };
    const COLUMNAS = @json($columnas);

    const lista = document.getElementById('compra-items');
    const subtotal = document.getElementById('compra-subtotal');
    const siguiente = document.getElementById('compra-siguiente');
    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

    const pesos = n => '$' + Math.round(n).toLocaleString('es-AR');
    const pesosDecimales = n => '$' + Number(n).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const escapar = t => String(t ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    function pedir(url, datos) {
        return fetch(url, {
            method: datos ? 'POST' : 'GET',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
            body: datos ? JSON.stringify(datos) : undefined,
        }).then(async r => {
            const data = await r.json().catch(() => ({}));
            if (r.status === 401 && data.redirect) { window.location.href = data.redirect; }
            return data;
        });
    }

    function dibujar(estado) {
        if (!estado || !estado.success) return;

        if (!estado.items.length) {
            lista.innerHTML = '<p class="px-[24px] py-[40px] text-[16px] text-[#5C5C5C]">Tu carrito está vacío.</p>';
        } else {
            lista.innerHTML = estado.items.map(item => `
                <div class="grid ${COLUMNAS} items-center min-h-[107px]" data-producto="${item.producto_id}">
                    <div class="p-[8px]">
                        <div class="flex items-center justify-center w-[91px] h-[91px] p-[4px] border border-[#D9D9D9] rounded-[12px] overflow-hidden">
                            <img src="${escapar(item.foto)}" alt="${escapar(item.nombre)}" class="w-full h-full object-contain">
                        </div>
                    </div>
                    <p class="pr-[16px] text-[16px] leading-[22px] font-medium">${escapar(item.nombre)}</p>
                    <p class="text-right text-[16px] leading-[22px] font-medium whitespace-nowrap">${pesosDecimales(item.precio)}</p>
                    <span></span>
                    <div class="relative grid grid-cols-[40px_41px_40px] w-[121px] h-[34px] border border-[#D9D9D9] rounded-[4px]">
                        <button type="button" class="compra-menos flex items-center justify-center" aria-label="Quitar uno">
                            <img src="${ICONOS.menos}" alt="" class="w-[11px] h-[1px] max-w-none">
                        </button>
                        <span class="flex items-center justify-center font-medium">${item.cantidad}</span>
                        <button type="button" class="compra-mas flex items-center justify-center" aria-label="Agregar uno">
                            <img src="${ICONOS.mas}" alt="" class="w-[11px] h-[11px] max-w-none">
                        </button>
                        <img src="${ICONOS.separador}" alt="" class="absolute left-[40px] top-[-1px] w-[1px] h-[34px] max-w-none pointer-events-none">
                        <img src="${ICONOS.separador}" alt="" class="absolute left-[81px] top-[-1px] w-[1px] h-[34px] max-w-none pointer-events-none">
                    </div>
                    <p class="text-right text-[16px] leading-[22px] font-medium whitespace-nowrap">${pesosDecimales(item.subtotal)}</p>
                    <div class="pl-[60px]">
                        <button type="button" class="compra-quitar hover:opacity-60 transition-opacity" aria-label="Quitar del carrito">
                            <img src="${ICONOS.papelera}" alt="" class="w-[24px] h-[24px] max-w-none">
                        </button>
                    </div>
                </div>`).join('');

            lista.querySelectorAll('[data-producto]').forEach(fila => {
                const id = fila.dataset.producto;
                const item = estado.items.find(i => String(i.producto_id) === id);
                fila.querySelector('.compra-quitar').addEventListener('click', () => pedir(URLS.eliminar, { producto_id: id }).then(dibujar));
                fila.querySelector('.compra-mas').addEventListener('click', () => pedir(URLS.actualizar, { producto_id: id, cantidad: item.cantidad + 1 }).then(dibujar));
                fila.querySelector('.compra-menos').addEventListener('click', () => {
                    if (item.cantidad > 1) pedir(URLS.actualizar, { producto_id: id, cantidad: item.cantidad - 1 }).then(dibujar);
                });
            });
        }

        subtotal.textContent = pesos(estado.subtotal);
        siguiente.disabled = estado.items.length === 0;
    }

    // SIGUIENTE sigue con la forma de envío
    siguiente.addEventListener('click', () => { window.location.href = @json(route('compra.envio')); });

    pedir(URLS.items).then(dibujar);
})();
</script>
@endsection
