@php
    $marcas = $marcas ?? App\Models\Marca::where('active', true)->orderBy('orden')->get();
    $buscar = $buscar ?? request('buscar');
@endphp

@extends('layouts.web')

@section('title', $marca->nombre ?? 'Productos')

@section('content')

<div class="w-full h-[280px] bg-cover bg-center letra text-white"
     style="background-image: url('{{ $productos_ban->foto }}');">
    <div class="h-full max-w-[1366px] mx-auto px-[5%] md:px-[71px] pb-[70px] pt-[24px] flex flex-col justify-between">
            <h3 class="text-[12px]">
            <a class="font-bold" href="{{ route('home') }}">Inicio</a> > <a class="font-bold" href="{{ route('productos') }}">Productos</a> > {{ $marca->nombre }}
        </h3>
        <h1 class="text-[40px] font-bold">{{ $productos_ban->t('texto') }}</h1>
    </div>
</div>

<div class="h-auto letra mx-auto px-[5%] md:px-[71px] max-w-[1366px] pt-[40px] pb-[60px]">
    <div class="w-full flex flex-col md:flex-row gap-[40px]">
        <aside class="w-full md:w-1/4">
            <form method="GET" action="{{ route('productos.categoria', ['id' => $marca->id]) }}" class="w-full flex mb-[12px]">
                <input
                    type="text"
                    name="buscar"
                    value="{{ $buscar }}"
                    placeholder="Buscar..."
                    class="w-full h-[40px] border border-[#D9D9D9] rounded-l-[13px] px-3 focus:outline-none text-sm"
                >
                <button type="submit" class="bg-[#A30000] text-white px-4 h-[40px] rounded-r-[13px] flex items-center justify-center hover:scale-100 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.603 10.601Z" />
                    </svg>
                </button>
            </form>

            @if(!empty($buscar))
                <a href="{{ route('productos.categoria', ['id' => $marca->id]) }}" class="inline-flex mb-[20px] text-[13px] font-semibold text-[#A30000] hover:underline">
                    Limpiar busqueda
                </a>
            @endif

            <div class="w-full">
                <ul class="text-[20px]">
                    @foreach($marcas as $m)
                        <li class="border-b border-gray-100 py-2 first:pt-0">
                            <a href="{{ route('productos.categoria', ['id' => $m->id]) }}" class="transition-colors block {{ isset($marca) && $marca->id == $m->id ? 'font-semibold' : '' }}">
                                Linea {{ $m->nombre }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>

        <main class="w-full md:w-3/4">
            @if($productos->count() > 0)
                <div class="w-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-[22px]">
                    @foreach($productos as $producto)
                        <a href="{{ route('productos.detalle', ['id' => $producto->id]) }}" class="border border-gray-100 rounded-[8px] overflow-hidden bg-white flex flex-col justify-between transition-all duration-300 hover:shadow-md">
                            <div class="w-full h-[240px] flex items-center justify-center overflow-hidden">
                                @if(!empty($producto->foto))
                                    <img src="{{ asset($producto->foto) }}" alt="{{ $producto->nombre }}" class="w-full h-full object-contain">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-300 text-xs">Sin imagen</div>
                                @endif
                            </div>

                            <hr class="flex border border-[#D8D8D8] mx-[14px]">
                            <div class="flex flex-col p-[14px]">
                                @if(!empty($producto->codigo))
                                    <div class="text-[15px] text-[#A60000] font-bold">
                                        {{ $producto->codigo }}
                                    </div>
                                @endif

                                <h3 class="text-[16px] font-bold leading-tight">
                                    {{ $producto->nombre }}
                                </h3>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="w-full p-8 bg-white border border-gray-200 rounded-[8px]">
                    <p class="text-gray-600 text-sm">
                        @if(!empty($buscar))
                            No se encontraron productos para esta marca con esa busqueda.
                        @else
                            No hay productos para esta marca.
                        @endif
                    </p>
                </div>
            @endif
        </main>
    </div>
</div>

@endsection
