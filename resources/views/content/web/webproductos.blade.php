@extends('layouts.layout-clientes')

@section('title', 'Productos')

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

    $descuentosTexto = collect($descuentos)
        ->map(fn ($d) => rtrim(rtrim(number_format($d, 2, ',', '.'), '0'), ',') . '%')
        ->implode(' + ');
@endphp

<div class="w-full text-black" style="font-family: 'Montserrat', sans-serif;">
    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pt-[30px] pb-[80px]">

        {{-- puntos y cotización --}}
        @include('content.web.partials.puntos-dolar')

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
                <tbody id="tbody-productos">
                    @forelse($productos as $producto)
                        @php
                            $precioUsd = (float) $producto->precio;
                            $precio = (float) $producto->precio_pesos;
                            $precioConDesc = $precio * $factorDescuento;
                        @endphp
                        <tr class="border-b border-[#D9D9D9]" data-product-id="{{ $producto->id }}">
                            <td class="py-[16px] align-middle">
                                <div class="flex items-center justify-center w-[80px] h-[83px] p-[2px] border border-[#D9D9D9] rounded-[12px] overflow-hidden">
                                    <img src="{{ $producto->foto_url }}" alt="{{ $producto->nombre }}" class="w-full h-full object-contain">
                                </div>
                            </td>

                            <td class="align-middle pr-[16px]">
                                <p class="font-semibold">{{ $producto->codigo }}</p>
                                <p class="max-w-[195px] break-words">{{ $producto->nombre }}</p>
                            </td>

                            <td class="align-middle text-right whitespace-nowrap">USD {{ number_format($precioUsd, 2, ',', '.') }}</td>

                            <td class="align-middle text-right whitespace-nowrap">${{ number_format($precio, 0, ',', '.') }}</td>

                            <td class="align-middle text-right whitespace-nowrap {{ !empty($descuentosTexto) ? 'text-[#006907]' : '' }}">
                                <p>${{ number_format($precioConDesc, 0, ',', '.') }}</p>
                                @if(!empty($descuentosTexto))
                                    <p class="text-[12px]">({{ $descuentosTexto }})</p>
                                @endif
                            </td>

                            <td class="align-middle pl-[28px]">
                                <div class="relative grid grid-cols-[40px_41px_40px] w-[121px] h-[34px] border border-[#D9D9D9] rounded-[4px]" data-row="{{ $producto->id }}">
                                    <button type="button" class="cantidad-menos flex items-center justify-center" aria-label="Quitar uno">
                                        <img src="{{ asset('assets/img/productos/menos.svg') }}" alt="" class="w-[11px] h-[1px] max-w-none">
                                    </button>
                                    <span class="cantidad-valor flex items-center justify-center font-medium"
                                          data-precio="{{ $precio }}"
                                          data-precio-con-desc="{{ $precioConDesc }}">1</span>
                                    <button type="button" class="cantidad-mas flex items-center justify-center" aria-label="Agregar uno">
                                        <img src="{{ asset('assets/img/productos/mas.svg') }}" alt="" class="w-[11px] h-[11px] max-w-none">
                                    </button>
                                    <img src="{{ asset('assets/img/productos/separador-fila.svg') }}" alt="" class="absolute left-[40px] top-[-1px] w-[1px] h-[34px] max-w-none pointer-events-none">
                                    <img src="{{ asset('assets/img/productos/separador-fila.svg') }}" alt="" class="absolute left-[81px] top-[-1px] w-[1px] h-[34px] max-w-none pointer-events-none">
                                </div>
                            </td>

                            <td class="align-middle text-right whitespace-nowrap">
                                <span class="subtotal" data-row="{{ $producto->id }}">${{ number_format($precioConDesc, 0, ',', '.') }}</span>
                            </td>

                            <td class="align-middle text-right {{ (int) $producto->stock_fisico <= 0 ? 'text-[#FF0000]' : '' }}">{{ (int) $producto->stock_fisico }}</td>

                            <td class="align-middle text-right">{{ (int) $producto->stock_transito }}</td>

                            <td class="align-middle pl-[27px]">
                                <button type="button" aria-label="Agregar al carrito" class="add-to-cart-btn block w-[42px] h-[42px] transition-opacity hover:opacity-80"
                                    data-codigo="{{ $producto->codigo }}" data-descripcion="{{ \Illuminate\Support\Str::limit($producto->nombre, 200) }}">
                                    <img src="{{ asset('assets/img/productos/carrito-boton.svg') }}" alt="" class="w-[42px] h-[42px] max-w-none">
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-[32px] text-center text-[#5C5C5C]">
                                No se encontraron productos con esos filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-[40px]">
            {{ $productos->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function formatPesos(n) {
            return '$' + Number(n).toLocaleString('es-AR', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        }

        function actualizarSubtotal(rowId) {
            const valor = document.querySelector(`[data-row="${rowId}"] .cantidad-valor`);
            const subtotal = document.querySelector(`.subtotal[data-row="${rowId}"]`);
            if (!valor || !subtotal) return;

            subtotal.textContent = formatPesos(parseFloat(valor.dataset.precioConDesc || 0) * parseInt(valor.textContent, 10));
        }

        function cambiarCantidad(rowId, delta) {
            const valor = document.querySelector(`[data-row="${rowId}"] .cantidad-valor`);
            valor.textContent = Math.max(1, parseInt(valor.textContent, 10) + delta);
            actualizarSubtotal(rowId);
        }

        document.querySelectorAll('.cantidad-menos').forEach(b => b.addEventListener('click', () => cambiarCantidad(b.closest('[data-row]').dataset.row, -1)));
        document.querySelectorAll('.cantidad-mas').forEach(b => b.addEventListener('click', () => cambiarCantidad(b.closest('[data-row]').dataset.row, 1)));

        function agregarAlCarrito(btn, productoId, cantidad) {
            const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

            fetch('{{ route('web-carrito.agregar') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ producto_id: productoId, cantidad })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // globito del carrito en la barra de navegación
                    const globo = document.getElementById('cart-count');
                    if (globo && data.carrito) {
                        const total = Object.keys(data.carrito).length;
                        globo.querySelector('.cart-count-num').textContent = total;
                        globo.classList.toggle('hidden', total === 0);
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Producto agregado',
                        text: 'El producto se agrego al carrito.',
                        showConfirmButton: false,
                        timer: 1400
                    });
                    const valor = document.querySelector(`[data-row="${productoId}"] .cantidad-valor`);
                    if (valor) {
                        valor.textContent = 1;
                        actualizarSubtotal(productoId);
                    }
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Error al agregar al carrito', confirmButtonColor: '#0D1A35' });
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo agregar el producto.', confirmButtonColor: '#0D1A35' });
            });
        }

        document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const productoId = this.closest('tr').dataset.productId;
                const cantidad = parseInt(document.querySelector(`[data-row="${productoId}"] .cantidad-valor`).textContent, 10);
                agregarAlCarrito(this, productoId, cantidad);
            });
        });
    });
</script>
@endsection
