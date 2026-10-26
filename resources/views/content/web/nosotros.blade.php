@php
    $nosotros = App\Models\Nosotro::first() ?? new App\Models\Nosotro();
@endphp

@extends('layouts.web')

@section('title', 'Nosotros')

@section('content')

<div class="w-full text-black" style="font-family: 'Montserrat', sans-serif;">
    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pt-[18px] pb-[80px]">

        {{-- breadcrumb --}}
        <p class="text-[12px] mb-[38px]"><a class="font-semibold" href="{{ route('home') }}">Inicio /</a> Nosotros</p>

        {{-- quiénes somos --}}
        <div class="flex flex-col lg:flex-row gap-[24px] mb-[80px]">
            {{-- foto --}}
            <div class="w-full lg:flex-1">
                @if (!empty($nosotros->foto))
                    <img src="{{ $nosotros->foto }}" alt="" class="w-full h-[320px] md:h-[464px] object-cover rounded-[12px]">
                @endif
            </div>
            {{-- texto --}}
            <div class="w-full lg:flex-1 lg:pt-[34px]">
                <h1 class="text-[32px] font-semibold leading-[1.2] mb-[10px]">{{ $nosotros->texto1 }}</h1>
                <div class="text-[16px] leading-[24px] [&_p+p]:mt-[16px]">{!! $nosotros->descripcion !!}</div>
            </div>
        </div>

        {{-- por qué elegirnos --}}
        <h2 class="text-[32px] font-semibold leading-[1.2] mb-[23px]">{{ $nosotros->texto2 }}</h2>

        <div class="flex flex-col lg:flex-row gap-[24px]">
            @foreach ([1, 2, 3] as $i)
                <div class="w-full lg:flex-1 bg-white border border-[#D9D9D9] rounded-[12px] flex flex-col items-center text-center px-[24px] pt-[28px] pb-[24px] lg:h-[380px]">
                    <div class="w-[60px] h-[60px] flex-shrink-0">
                        @if (!empty($nosotros->{'icono' . $i}))
                            <img src="{{ $nosotros->{'icono' . $i} }}" alt="" class="w-[60px] h-[60px] object-contain">
                        @endif
                    </div>
                    <h3 class="text-[20px] font-semibold leading-[1.2] mt-[32px] mb-[11px]">{{ $nosotros->{'nombre_icono' . $i} }}</h3>
                    <div class="text-[16px] leading-[24px] [&_p+p]:mt-[16px]">{!! $nosotros->{'texto_icono' . $i} !!}</div>
                </div>
            @endforeach
        </div>

        {{-- video --}}
        @if (!empty($nosotros->video))
            <video autoplay loop muted playsinline class="block w-full aspect-[1224/700] object-cover mt-[80px]">
                <source src="{{ $nosotros->video }}" type="video/mp4">
                Tu navegador no soporta el video.
            </video>
        @endif

    </div>
</div>

@endsection
