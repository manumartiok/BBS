@php
    $catalogos = App\Models\Catalogo::where('active', true)
        ->orderBy('orden')
        ->get();
@endphp

@extends('layouts.web')

@section('title', 'Catálogo')

@section('content')

<div class="w-full text-black" style="font-family: 'Montserrat', sans-serif;">
    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pt-[15px] pb-[80px]">

        {{-- breadcrumb --}}
        <p class="text-[12px] leading-[15px] mb-[41px]"><a class="font-semibold" href="{{ route('home') }}">Inicio /</a> Catálogo</p>

        {{-- catálogos --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-[24px]">
            @foreach($catalogos as $item)
                <div class="relative flex flex-col sm:flex-row sm:min-h-[291px] bg-white rounded-[12px] after:content-[''] after:absolute after:inset-0 after:rounded-[12px] after:border after:border-[#D9D9D9] after:pointer-events-none">

                    {{-- foto --}}
                    <div class="flex items-center justify-center flex-shrink-0 w-full h-[240px] sm:w-[288px] sm:h-auto px-[7px] bg-[#F6F8FA] rounded-t-[12px] sm:rounded-r-none sm:rounded-l-[12px]">
                        @if(!empty($item->foto))
                            {{-- multiply: las fotos con fondo blanco se funden con el gris del panel --}}
                            <img src="{{ asset($item->foto) }}" alt="{{ $item->nombre }}" class="w-[274px] h-[201px] max-w-full object-contain mix-blend-multiply">
                        @endif
                    </div>

                    {{-- datos --}}
                    <div class="flex flex-col flex-1 min-w-0 p-[24px] sm:px-[25px] sm:pt-[77.6px]">
                        <h2 class="text-[24px] font-semibold leading-[29px]">{{ $item->nombre }}</h2>

                        {{-- divisor (el svg original es vertical, se rota 90°) --}}
                        <div class="relative w-[219.5px] h-[1px] mt-[13px]">
                            <img src="{{ asset('assets/img/catalogo/divider.svg') }}" alt="" class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 rotate-90 w-[1px] h-[219.5px] max-w-none">
                        </div>

                        @if(!empty($item->archivo))
                        <div class="flex items-center gap-[12px] ml-[7px] mt-[29.5px]">
                            <a href="{{ asset($item->archivo) }}" target="_blank" rel="noopener noreferrer" aria-label="Ver catálogo" class="block w-[20px] h-[14px] transition-opacity hover:opacity-60">
                                <img src="{{ asset('assets/img/catalogo/eye.svg') }}" alt="" class="w-[20.005px] h-[13.99px] max-w-none">
                            </a>
                            <a href="{{ asset($item->archivo) }}" download aria-label="Descargar catálogo" class="relative block w-[14px] h-[14px] transition-opacity hover:opacity-60">
                                <img src="{{ asset('assets/img/catalogo/download.svg') }}" alt="" class="absolute left-[-1px] top-[-1px] w-[16px] h-[16px] max-w-none">
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</div>

@endsection
