@php
    // Variables recibidas: $producto, $related
    // Foto general primero, luego la galería (sin duplicados)
    $allFotos = collect();
    $tieneFoto = !empty($producto->foto) && $producto->foto !== '/default.png';
    if ($tieneFoto) {
        $allFotos->push((object) ['foto' => $producto->foto]);
    }
    foreach ($producto->fotos ?? [] as $f) {
        if (!$tieneFoto || $f->foto != $producto->foto) {
            $allFotos->push($f);
        }
    }
    // Sin ninguna foto se muestra la imagen por defecto
    if ($allFotos->isEmpty()) {
        $allFotos->push((object) ['foto' => 'assets/img/default.png']);
    }

    $caracteristicas = collect($producto->caracteristicas ?? [])
        ->filter(fn ($c) => !empty($c['nombre']) || !empty($c['valor']))
        ->values();
    // dos columnas: la primera lleva la mitad más uno si la cantidad es impar
    $columnas = $caracteristicas->chunk(max(1, (int) ceil($caracteristicas->count() / 2)));
@endphp

@extends('layouts.web')

@section('title', $producto->nombre ?? 'Producto')

@section('content')

<div class="w-full text-black" style="font-family: 'Montserrat', sans-serif;">
    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pt-[15px] pb-[60px]">

        {{-- breadcrumb --}}
        <p class="text-[12px] leading-[15px] mb-[38px]"><a class="font-semibold" href="{{ route('home') }}">Inicio /</a> <a href="{{ route('productos') }}">Productos</a></p>

        {{-- producto --}}
        <div class="flex flex-col lg:flex-row gap-[24px] lg:gap-[26px]">

            {{-- miniaturas --}}
            @if($allFotos->count() > 1)
                <div class="order-2 lg:order-1 flex lg:flex-col gap-[24px] lg:w-[80px] flex-shrink-0 overflow-auto lg:max-h-[500px]">
                    @foreach($allFotos as $foto)
                        <button type="button" data-src="{{ asset($foto->foto) }}" aria-label="Ver foto {{ $loop->iteration }}"
                                class="thumb flex items-center justify-center flex-shrink-0 w-[80px] h-[62px] p-[3px] bg-white rounded-[12px] border border-[#D9D9D9] overflow-hidden">
                            <img src="{{ asset($foto->foto) }}" alt="{{ $producto->nombre }}" class="w-full h-full object-contain">
                        </button>
                    @endforeach
                </div>
            @endif

            {{-- foto principal --}}
            <div class="order-1 lg:order-2 flex items-center justify-center w-full lg:flex-1 lg:max-w-[500px] aspect-square p-[32px] bg-white rounded-[12px] border border-[#D9D9D9]">
                <img id="main-image" src="{{ asset($allFotos->first()->foto) }}" alt="{{ $producto->nombre }}" class="w-full h-full object-contain">
            </div>

            {{-- datos --}}
            <div class="order-3 flex flex-col w-full lg:flex-1 lg:max-w-[496px] lg:min-h-[500px] lg:ml-[16px] lg:pt-[12px] lg:pb-[36px]">
                <p class="text-[16px] leading-[20px] font-semibold">{{ $producto->marca->nombre ?? '' }}</p>
                <h1 class="mt-[7px] text-[32px] leading-[39px] font-semibold break-words">{{ $producto->nombre }}</h1>

                <img src="{{ asset('assets/img/productos/titulo-linea.svg') }}" alt="" class="block w-full max-w-none h-[1px] mt-[16px]">

                @if(!empty($producto->descripcion))
                    <p class="mt-[22px] text-[16px] leading-[24px] whitespace-pre-line">{!! $producto->descripcion !!}</p>
                @endif

                <div class="flex flex-col items-end gap-[24px] mt-[32px] lg:mt-auto">
                    @if(!is_null($producto->precio_pesos))
                        <p class="text-[32px] leading-[39px] font-semibold text-right">${{ number_format($producto->precio_pesos, 2, ',', '.') }}</p>
                    @endif

                    <div class="flex items-center gap-[24px]">
                        {{-- cantidad --}}
                        <div class="relative grid grid-cols-[40px_41px_40px] w-[121px] h-[42px] border border-[#D9D9D9] rounded-[4px]">
                            <button type="button" id="cantidad-menos" aria-label="Quitar uno" class="flex items-center justify-center">
                                <img src="{{ asset('assets/img/productos/menos.svg') }}" alt="" class="w-[11px] h-[1px] max-w-none">
                            </button>
                            <span id="cantidad" class="flex items-center justify-center text-[16px] font-semibold">1</span>
                            <button type="button" id="cantidad-mas" aria-label="Agregar uno" class="flex items-center justify-center">
                                <img src="{{ asset('assets/img/productos/mas.svg') }}" alt="" class="w-[11px] h-[11px] max-w-none">
                            </button>
                            <img src="{{ asset('assets/img/productos/separador.svg') }}" alt="" class="absolute left-[40px] top-[-1px] w-[1px] h-[42px] max-w-none pointer-events-none">
                            <img src="{{ asset('assets/img/productos/separador.svg') }}" alt="" class="absolute left-[81px] top-[-1px] w-[1px] h-[42px] max-w-none pointer-events-none">
                        </div>

                        {{-- agregar al carrito --}}
                        <button type="button" id="agregar-carrito" aria-label="Agregar al carrito" class="block w-[42px] h-[42px] transition-opacity hover:opacity-80">
                            <img src="{{ asset('assets/img/productos/carrito.svg') }}" alt="" class="w-[42px] h-[42px] max-w-none">
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- el diseño sangra estas secciones 104px a la izquierda en desktop --}}
        <div class="lg:pl-[104px]">

            {{-- características técnicas --}}
            @if($caracteristicas->isNotEmpty())
                <section class="mt-[56px]">
                    <h2 class="text-[32px] leading-[39px] font-semibold mb-[24px]">Características técnicas</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-[24px]">
                        @foreach($columnas as $columna)
                            <div>
                                @foreach($columna as $c)
                                    <div class="flex items-center justify-between gap-[16px] min-h-[49px] px-[18px] py-[12px] text-[16px] {{ $loop->odd ? 'bg-[#F6F8FA]' : '' }}">
                                        <span class="leading-[24px]">{{ $c['nombre'] ?? '' }}</span>
                                        <span class="leading-[20px] text-right whitespace-pre-line">{{ $c['valor'] ?? '' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- productos relacionados --}}
            @if($related->isNotEmpty())
                <section class="mt-[58px]">
                    <h2 class="text-[32px] leading-[39px] font-semibold mb-[28px]">Productos relacionados</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-[repeat(3,288px)] gap-[24px]">
                        @foreach($related as $r)
                            @include('content.web.partials.producto-card', ['producto' => $r])
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // galería
    const main = document.getElementById('main-image');
    const thumbs = Array.from(document.querySelectorAll('.thumb'));
    const marcar = (t) => thumbs.forEach(x => x.classList.toggle('border-black', x === t));
    if (main && thumbs.length) {
        marcar(thumbs[0]);
        thumbs.forEach(t => t.addEventListener('click', () => {
            main.src = t.dataset.src;
            marcar(t);
        }));
    }

    // cantidad
    const cantidadEl = document.getElementById('cantidad');
    let cantidad = 1;
    const mostrar = () => cantidadEl.textContent = cantidad;
    document.getElementById('cantidad-menos').addEventListener('click', () => { cantidad = Math.max(1, cantidad - 1); mostrar(); });
    document.getElementById('cantidad-mas').addEventListener('click', () => { cantidad += 1; mostrar(); });

    // carrito público: sin sesión manda al registro/login público; con sesión suma al carrito y abre el panel
    const logueado = @json(auth('usuario_publico')->check());
    document.getElementById('agregar-carrito').addEventListener('click', function () {
        if (!logueado) {
            window.location.href = @json(route('web-acceso'));
            return;
        }

        window.CarritoPublico.agregar({{ $producto->id }}, cantidad).then(data => {
            if (!data.success) {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Error al agregar al carrito', confirmButtonColor: '#0D1A35' });
            }
        }).catch(() => Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo agregar el producto.', confirmButtonColor: '#0D1A35' }));
    });
});
</script>
@endsection
