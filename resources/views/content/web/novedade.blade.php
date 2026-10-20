@php
    $novedades_ban = App\Models\Banner::seccion('novedades');
    $novedades = App\Models\Novedade::where('active', true)
        ->orderBy('orden')
        ->get(); 
@endphp

@extends('layouts.web')

@section('title', 'Novedades')

@section('content')

{{-- banner  --}}
<div class="w-full h-[300px] bg-cover bg-center letra text-white pt-[120px] pb-[70px]"
     style="background-image: url('{{ $novedades_ban->foto }}');">
    <div class="h-full max-w-[1366px] mx-auto px-[5%] md:px-[71px] flex flex-col justify-between">
        <h3 class="text-[12px]">
            <a class="font-bold" href="{{ route('home') }}">Inicio</a> > Novedades
        </h3>
        <h1 class="text-[32px] font-medium">{{ $novedades_ban->t('texto') }}</h1>
    </div>
</div>

{{-- contenido --}}
<div class="h-auto letra mx-auto px-[5%] md:px-[71px] max-w-[1366px] pt-[80px] pb-[110px]">
    <div class="w-full grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-[24px]">

        @foreach($novedades as $item)
            <div class="group w-full flex flex-col border border-[#DDDDE0] rounded-[8px]">

                <img src="{{ $item->foto }}"
                     class="h-[300px] w-full object-cover"
                     alt="{{ $item->titulo }}">

                <div class="flex flex-col p-[24px]">
                    <h4 class="text-[14px] font-bold text-[#FF9000]">
                        {{ $item->texto_chico }}
                    </h4>

                    <h2 class="my-[10px] text-[24px] font-semibold">
                        {{ $item->titulo }}
                    </h2>

                    <div class="text-[16px] text-[#74716A]"
                         style="
                            display: -webkit-box;
                            -webkit-line-clamp: 2;
                            -webkit-box-orient: vertical;
                            overflow: hidden;
                            text-overflow: ellipsis;
                            line-height: 1.2em;
                            max-height: 2.4em;
                         ">
                        {!! $item->texto !!}
                    </div>

                    <div class="flex justify-between mt-[24px]">
                        <a href="{{ route('novedades-detalle', ['id' => $item->id]) }}"
                           class="text-[16px] text-[#242424]/50 group-hover:text-black transition-colors duration-200">
                            Leer más
                        </a>

                        <a href="{{ route('novedades-detalle', ['id' => $item->id]) }}">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             width="24"
                             height="24"
                             viewBox="0 0 24 24"
                             class="fill-[#414047] group-hover:fill-[#FF9000] transition-colors duration-200">
                            <path d="M12 4L10.59 5.41L16.17 11H4V13H16.17L10.59 18.59L12 20L20 12L12 4Z"/>
                        </svg>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach

    </div>
</div>

@endsection
