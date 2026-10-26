@extends('layouts.web')

@section('title', $novedade->titulo)

@section('content')

<div class="w-full text-black" style="font-family: 'Montserrat', sans-serif;">
    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pt-[15px] pb-[110px]">

        {{-- breadcrumb --}}
        <p class="text-[12px] leading-[15px] mb-[41px]">
            <a class="font-semibold" href="{{ route('home') }}">Inicio /</a>
            <a class="font-semibold" href="{{ route('novedades') }}">Novedades /</a>
            {{ $novedade->titulo }}
        </p>

        {{-- contenido --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-[48px]">

            {{-- imagen --}}
            <div class="w-full">
                @if(!empty($novedade->foto))
                    <img src="{{ $novedade->foto }}"
                         class="w-full h-[300px] md:h-[420px] object-cover border border-[#DDDDE0] rounded-[12px]"
                         alt="{{ $novedade->titulo }}">
                @else
                    <div class="w-full h-[300px] md:h-[420px] bg-[#F2F2F2] border border-[#DDDDE0] rounded-[12px]"></div>
                @endif
            </div>

            {{-- texto --}}
            <div class="flex flex-col justify-center">

                <h4 class="text-[14px] font-bold  mb-[8px]">
                    {{ $novedade->texto_chico }}
                </h4>

                <h2 class="text-[32px] font-semibold mb-[16px]">
                    {{ $novedade->titulo }}
                </h2>

                <div class="text-[16px] text-[#74716A] leading-relaxed">
                    {!! $novedade->texto !!}
                </div>

            </div>

        </div>

    </div>
</div>

@endsection
