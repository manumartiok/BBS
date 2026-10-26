@extends('layouts.layout-clientes')

@section('title', 'Canje de puntos')

@section('content')
@php
    $miles = fn ($n) => number_format($n, 0, ',', '.');
    $marcaActiva = request()->integer('marca');
@endphp

<div class="w-full text-black" style="font-family: 'Montserrat', sans-serif;">
    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pt-[31px] pb-[80px]">

        @include('content.web.partials.canje-tabs', ['activa' => 'productos'])

        <div class="flex flex-col lg:flex-row gap-[24px] mt-[19px]">

            {{-- categorías --}}
            <aside class="w-full lg:w-[288px] flex-shrink-0">
                <ul class="flex flex-col gap-[24px]">
                    @foreach($marcas as $m)
                        @php
                            $activa = $marcaActiva === $m->id;
                        @endphp
                        <li>
                            <details class="group" @if($activa) open @endif>
                                <summary class="flex items-center justify-between gap-[8px] h-[48px] px-[10px] bg-white rounded-[12px] shadow-[0px_2px_10px_0px_rgba(0,0,0,0.1)] text-[16px] uppercase cursor-pointer select-none list-none [&::-webkit-details-marker]:hidden transition-shadow hover:shadow-[0px_2px_14px_0px_rgba(0,0,0,0.2)] {{ $activa ? 'font-semibold' : '' }}">
                                    <span class="truncate">{{ $m->nombre }}</span>
                                    <span class="flex items-center justify-center flex-shrink-0 w-[19px] h-[10.45px]">
                                        <img src="{{ asset('assets/img/productos/chevron.svg') }}" alt="" class="w-[10.45px] h-[18.96px] max-w-none -rotate-90 group-open:rotate-90 transition-transform">
                                    </span>
                                </summary>

                                <div class="mt-[8px] max-h-[240px] overflow-y-auto bg-white rounded-[12px] shadow-[0px_2px_10px_0px_rgba(0,0,0,0.1)]">
                                    <a href="{{ route('web-canje', ['marca' => $m->id]) }}" class="block px-[10px] py-[10px] text-[14px] font-semibold border-b border-[#D9D9D9] hover:bg-[#F6F8FA]">Ver todos</a>
                                    @foreach($m->productos as $p)
                                        <a href="{{ route('web-canje', ['producto' => $p->id]) }}" class="block px-[10px] py-[8px] text-[14px] leading-[18px] hover:bg-[#F6F8FA]">{{ $p->nombre }}</a>
                                    @endforeach
                                </div>
                            </details>
                        </li>
                    @endforeach
                </ul>
            </aside>

            {{-- productos --}}
            <div class="flex-1 min-w-0">
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-[24px]">
                    @forelse($productos as $producto)
                        @php
                            $costo = (int) $producto->puntos;
                            $faltan = max(0, $costo - $puntos);
                        @endphp
                        <article class="relative h-[446px] bg-white border border-[#D9D9D9] rounded-[12px]">
                            @if($faltan > 0)
                                <span class="absolute top-0 left-0 z-10 flex items-center h-[29px] pl-[25px] pr-[32px] bg-black rounded-[12px] text-[12px] leading-none font-semibold text-white whitespace-nowrap">
                                    TE FALTAN {{ $miles($faltan) }} PUNTOS
                                </span>
                            @endif

                            <div class="h-[288px] overflow-hidden rounded-t-[12px]">
                                <img src="{{ $producto->foto_url }}" alt="{{ $producto->nombre }}" class="w-full h-full object-contain">
                            </div>
                            <div class="mx-[12px] border-t border-[#D9D9D9]"></div>

                            <h3 class="mx-[12px] mt-[19px] text-[24px] leading-[29px] font-semibold line-clamp-2">{{ $producto->nombre }}</h3>

                            <div class="absolute inset-x-[12px] bottom-[17px] flex items-end justify-between">
                                <button type="button"
                                        class="btn-canjear h-[29px] w-[80px] rounded-[12px] text-[12px] leading-none font-semibold uppercase transition-opacity {{ $faltan > 0 ? 'bg-[#D9D9D9] text-[#424242] cursor-not-allowed' : 'bg-black text-white hover:opacity-80' }}"
                                        data-producto-id="{{ $producto->id }}"
                                        @disabled($faltan > 0)>
                                    Canjear
                                </button>
                                <div class="text-right">
                                    <p class="text-[12px] leading-[15px] font-semibold text-[#5C5C5C]">PUNTOS</p>
                                    <p class="text-[24px] leading-[29px] font-semibold">{{ $miles($costo) }}</p>
                                </div>
                            </div>
                        </article>
                    @empty
                        <p class="col-span-full text-[16px] text-[#5C5C5C]">No hay productos para canjear.</p>
                    @endforelse
                </div>

                <div class="mt-[40px]">
                    {{ $productos->links() }}
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    @if(session('canje_ok'))
        Swal.fire({ icon: 'success', title: 'Canje realizado', text: @json(session('canje_ok')), confirmButtonColor: '#0D1A35' });
    @endif

    // CANJEAR agrega el producto al carrito de canje
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

    document.querySelectorAll('.btn-canjear:not(:disabled)').forEach(btn => {
        btn.addEventListener('click', () => {
            fetch('{{ route('web-canje.agregar') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ producto_id: btn.dataset.productoId })
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'No se pudo agregar el producto.', confirmButtonColor: '#0D1A35' });
                    return;
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Producto agregado',
                    text: 'Lo encontrás en el carrito de canje.',
                    showCancelButton: true,
                    confirmButtonText: 'Ver carrito',
                    cancelButtonText: 'Seguir viendo',
                    confirmButtonColor: '#0D1A35'
                }).then(result => { if (result.isConfirmed) window.location.href = '{{ route('web-canje.carrito') }}'; });
            })
            .catch(() => Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor.', confirmButtonColor: '#0D1A35' }));
        });
    });
</script>
@endsection
