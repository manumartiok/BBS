@php
    $productos_ban = $productos_ban ?? App\Models\Banner::seccion('productos');
    $marcas = $marcas ?? App\Models\Marca::where('active', true)->orderBy('orden')->get();
    $hayFiltros = $hayFiltros ?? (request()->filled('buscar') || request()->filled('codigo') || request()->filled('marca'));
@endphp

@extends('layouts.web')

@section('title', 'Productos')

@section('content')

<div class="w-full h-[280px] bg-cover bg-center letra text-white "
     style="background-image: url('{{ $productos_ban->foto }}');">
    <div class="h-full max-w-[1366px] mx-auto px-[5%] md:px-[71px] pb-[70px] pt-[24px] flex flex-col justify-between">
        <h3 class="text-[12px]">
            <a class="font-bold" href="{{ route('home') }}">Inicio</a> > Productos
        </h3>
        <h1 class="text-[40px] font-bold">{{ $productos_ban->t('texto') }}</h1>
    </div>
</div>

<div class="h-auto letra mx-auto px-[5%] md:px-[71px] max-w-[1366px] pt-[40px] pb-[60px]">
    <x-product-search :action="route('productos')" :marcas="$marcas" />

    @if($hayFiltros)
        <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-[20px]">
            @forelse($productos as $producto)
                <a href="{{ route('productos.detalle', ['id' => $producto->id]) }}" class="border border-gray-200 rounded-[13px] overflow-hidden hover:shadow-lg transition-shadow duration-300 bg-white">
                    <div class="w-full h-[240px] flex items-center justify-center overflow-hidden p-3">
                        @if(!empty($producto->foto))
                            <img src="{{ asset($producto->foto) }}" alt="{{ $producto->nombre }}" class="w-full h-full object-contain">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                Sin imagen
                            </div>
                        @endif
                    </div>

                    <hr class="border-gray-200">

                    <div class="p-4">
                        @if(!empty($producto->codigo))
                            <div class="text-[15px] text-[#A60000] font-bold">{{ $producto->codigo }}</div>
                        @endif
                        <h3 class="text-[16px] font-semibold text-gray-700">
                            {{ $producto->nombre }}
                        </h3>
                        @if($producto->marca)
                            <p class="text-[13px] text-gray-500 mt-1">{{ $producto->marca->nombre }}</p>
                        @endif
                    </div>
                </a>
            @empty
                <div class="col-span-full w-full p-8 bg-white border border-gray-200 rounded-[8px]">
                    <p class="text-gray-600 text-sm">No se encontraron productos con esos filtros.</p>
                </div>
            @endforelse
        </div>

        @if(method_exists($productos, 'links'))
            <div class="mt-[40px]">
                {{ $productos->links() }}
            </div>
        @endif
    @else
        <div class="w-full grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-[20px]">
            @foreach($marcas as $marca)
                <a href="{{ route('productos.categoria', $marca->id) }}" class="border border-gray-200 rounded-[13px] overflow-hidden hover:shadow-lg transition-shadow duration-300">
                    <div class="w-full h-[262px] overflow-hidden ">
                                @if(!empty($marca->foto))
                            <img src="{{ asset($marca->foto) }}"
                                alt="{{ $marca->nombre }}"
                                class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-400">
                                Sin imagen
                            </div>
                        @endif
                    </div>

                    <hr class="border-gray-200">

                    <div class="p-4">
                        <h3 class="text-[18px] font-semibold text-gray-700 text-center">
                            {{ $marca->nombre }}
                        </h3>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>

@endsection
