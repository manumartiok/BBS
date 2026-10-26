@php
    $home = App\Models\Home::first() ?? new App\Models\Home();

    $home_banners = App\Models\HomeBanner::where('active', true)->orderBy('orden')->get();

    // Secciones de productos según los booleanos del admin: [título, productos]
    $productosHome = fn (string $columna) => App\Models\Producto::with('marca')
        ->where('active', true)
        ->where($columna, true)
        ->orderBy('orden')
        ->get();

    $masNuevo = $productosHome('destacado');
    $ofertas = $productosHome('oferta');
    $masVendido = $productosHome('mas_vendido');

    $categorias = App\Models\Marca::where('active', true)
        ->where('destacado', true)
        ->orderBy('orden')
        ->get();

    $novedades = App\Models\Novedade::where('active', true)
        ->where('destacado', true)
        ->orderBy('orden')
        ->get();

    $descubre = $productosHome('descubre_mas');
@endphp

@extends('layouts.web', ['navbarHome' => true])

@section('title', 'Inicio')
@section('content')

<div class="w-full text-black" style="font-family: 'Montserrat', sans-serif;">

    {{-- hero: la barra de navegación va encima --}}
    <div id="bannerCarousel" class="relative w-full h-[320px] md:h-[560px] mt-[40px] overflow-hidden bg-[#0D1A35]">
        @foreach($home_banners as $index => $banner)
            <div class="carousel-slide absolute inset-0 transition-opacity duration-500 {{ $index === 0 ? 'opacity-100' : 'opacity-0' }}">
                @if (($banner->tipo_medio ?? 'imagen') === 'video')
                    <video class="absolute inset-0 w-full h-full object-cover" autoplay muted loop playsinline>
                        <source src="{{ asset($banner->foto) }}" type="video/mp4">
                    </video>
                @else
                    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset($banner->foto) }}')"></div>
                @endif
            </div> 
        @endforeach

        @if ($home_banners->count() > 1)
            <div class="absolute bottom-[24px] left-0 right-0 flex justify-center gap-[8px] z-10">
                @foreach ($home_banners as $index => $banner)
                    <span class="carousel-dot h-[6px] w-[44px] cursor-pointer transition-all duration-300 {{ $index === 0 ? 'bg-white' : 'bg-white/40' }}" data-index="{{ $index }}"></span>
                @endforeach
            </div>
        @endif
    </div>

    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pb-[80px]">

        {{-- productos (booleanos del admin) --}}
        @foreach([[$home->titulo1, $masNuevo], [$home->titulo2, $ofertas]] as [$titulo, $productos])
            @if($productos->isNotEmpty())
                <section class="mt-[80px]">
                    <h2 class="mb-[23px] text-[32px] leading-[39px] font-semibold">{{ $titulo }}</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-[24px]">
                        @foreach($productos as $producto)
                            @include('content.web.partials.producto-card', ['producto' => $producto])
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach

        {{-- categorías --}}
        @if($categorias->isNotEmpty())
            <section class="mt-[80px]">
                <h2 class="mb-[23px] text-[32px] leading-[39px] font-semibold">{{ $home->titulo3 }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-[24px]">
                    @foreach($categorias as $categoria)
                        <a href="{{ route('productos.categoria', $categoria->id) }}" class="group block w-full max-w-[288px] sm:max-w-none mx-auto">
                            <div class="flex items-center justify-center h-[252px] pt-[21px] pb-[20px] bg-white rounded-[12px] border border-[#D9D9D9] overflow-hidden transition-shadow group-hover:shadow-[0px_2px_10px_0px_rgba(0,0,0,0.1)]">
                                @if(!empty($categoria->foto))
                                    <img src="{{ asset($categoria->foto) }}" alt="{{ $categoria->nombre }}" class="w-full h-[211px] object-contain">
                                @endif
                            </div>
                            <p class="mt-[10px] text-[24px] leading-[29px] font-semibold text-center">{{ $categoria->nombre }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- novedades destacadas --}}
        @if($novedades->isNotEmpty())
            <section class="mt-[80px]">
                <h2 class="mb-[23px] text-[32px] leading-[39px] font-semibold">{{ $home->titulo4 }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-[24px]">
                    @foreach($novedades as $item)
                        @include('content.web.partials.novedade-card', ['item' => $item])
                    @endforeach
                </div>
            </section>
        @endif

        {{-- descubre más (booleano del admin, con su propia card) --}}
        @if($descubre->isNotEmpty())
            <section class="mt-[80px]">
                <h2 class="mb-[23px] text-[32px] leading-[39px] font-semibold">{{ $home->titulo5 }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-[24px]">
                    @foreach($descubre as $producto)
                        @include('content.web.partials.producto-descubre', ['producto' => $producto])
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    {{-- marcas representadas (fijo) --}}
    <section class="w-full bg-[#F6F8FA]">
        <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] py-[40px] min-h-[250px]">
            <h2 class="text-[32px] leading-[39px] font-semibold text-center">Marcas representadas</h2>
            <div class="flex flex-wrap items-center justify-center gap-x-[40px] md:gap-x-[289px] gap-y-[24px] mt-[34px]">
                <img src="{{ asset('assets/img/home/marca-vds.png') }}" alt="VDS" class="w-[232px] h-[95px] object-contain">
                <img src="{{ asset('assets/img/home/marca-agl.png') }}" alt="AGL" class="w-[240px] h-[104px] object-contain">
            </div>
        </div>
    </section>

    {{-- más vendidos --}}
    @if($masVendido->isNotEmpty())
        <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pb-[80px]">
            <section class="mt-[80px]">
                <h2 class="mb-[23px] text-[32px] leading-[39px] font-semibold">{{ $home->titulo6 }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-[24px]">
                    @foreach($masVendido as $producto)
                        @include('content.web.partials.producto-card', ['producto' => $producto])
                    @endforeach
                </div>
            </section>
        </div>
    @else
        <div class="pb-[80px]"></div>
    @endif

</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const carousel = document.getElementById('bannerCarousel');
    const slides = carousel.querySelectorAll('.carousel-slide');
    const dots = carousel.querySelectorAll('.carousel-dot');
    if (slides.length < 2) return;

    let actual = 0;
    let timer;

    function mostrar(i) {
        slides.forEach((s, n) => {
            s.classList.toggle('opacity-100', n === i);
            s.classList.toggle('opacity-0', n !== i);
        });
        dots.forEach((d, n) => {
            d.classList.toggle('bg-white', n === i);
            d.classList.toggle('bg-white/40', n !== i);
        });
        actual = i;
    }

    const iniciar = () => timer = setInterval(() => mostrar((actual + 1) % slides.length), 5000);
    const detener = () => clearInterval(timer);

    dots.forEach(d => d.addEventListener('click', () => { mostrar(parseInt(d.dataset.index, 10)); detener(); iniciar(); }));
    carousel.addEventListener('mouseenter', detener);
    carousel.addEventListener('mouseleave', iniciar);
    iniciar();
});
</script>
@endsection
