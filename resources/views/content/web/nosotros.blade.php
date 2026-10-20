@php
    $bn = App\Models\Banner::seccion('nosotros');
     $nosotros = App\Models\Nosotro::first(); 
@endphp

@extends('layouts.web')

@section('title', 'Nosotros')

@section('content')

    
{{-- banner  --}}

<div class="w-full h-[300px] bg-cover bg-center letra text-white pt-[120px] pb-[70px]" style="background-image: url('{{ $bn->foto }}');">
    <div class="h-full max-w-[1366px] mx-auto px-[5%] md:px-[71px] flex flex-col justify-between">
        <h3 class="text-[12px]"><a class="font-bold" href="{{ route('home') }}" >Inicio</a> > Nosotros</h3>
        <h1 class="text-[32px] font-medium">{{ $bn->t('texto') }}</h1>
    </div>
</div>

{{-- contenido  --}}
<div>
    {{-- nosotros  --}}
    <div class="h-full letra max-w-[1366px] mx-auto px-[5%] md:px-[71px] pt-[65px] pb-[85px] flex flex-col justify-center">
        <div class="flex flex-col lg:flex-row w-full gap-[45px] text-center lg:text-start">
            {{-- Foto  --}}
            <div class=" w-full lg:min-w-[600px] lg:w-1/2 flex flex-grow ">
                <img src="{{$nosotros->foto}}" alt="" class="w-full h-[480px] border rounded-[10px]">
            </div>
            {{-- texto  --}}
            <div class="w-full lg:w-1/2 flex flex-col md:mt-[45px]">
                <p class="text-[32px] max-h-[32px] font-semibold md:ml-[16px] mb-[28px] ">{{$nosotros->texto1}}</p>
                <h2 class="text-[16px] text-[#74716A]">{!! $nosotros->descripcion !!}</h2>
            </div>
        </div>
    </div>
    {{-- valores  --}}
    <div class="h-full w-full bg-[#F5F5F5]">
        <div class="letra max-w-[1366px] mx-auto px-[5%] md:px-[71px] pt-[40px] pb-[75px] flex flex-col">
            {{-- encabezado  --}}
            <div class="flex flex-col text-center lg:text-start">
                <h2 class="text-[32px] font-medium  mb-[24px]">{{$nosotros->texto2}}</h2>
            </div>
            {{-- detalles  --}}
            <div class="flex flex-col gap-[30px] mb-[30px]">

                <div class="w-full flex flex-col lg:flex-row gap-[24px]">
                    <div class="w-full lg:w-1/3 border rounded-[8px] flex flex-col items-center text-center bg-[#FFFFFF]  h-[392px]  px-[20px] pt-[100px]">
                        <div class="flex-shrink-0">
                            <img src="{{$nosotros->icono1}}" alt="" class="mx-auto">
                        </div>
                        <h1 class="text-[20px] font-semibold text-[#242424] mt-[40px] mb-[10px]">{{$nosotros->nombre_icono1}}</h1>
                        <h3 class="text-[16px] text-[#74716A]">{!! $nosotros->texto_icono1 !!}</h3>
                    </div>
                    
                    <div class="w-full lg:w-1/3 border rounded-[8px] flex flex-col items-center text-center bg-[#FFFFFF]  h-[392px]  px-[20px] pt-[100px]">
                        <div class="flex-shrink-0">
                            <img src="{{$nosotros->icono2}}" alt="" class="mx-auto">
                        </div>
                        <h1 class="text-[20px] font-semibold text-[#242424] mt-[40px] mb-[10px]">{{$nosotros->nombre_icono2}}</h1>
                        <h3 class="text-[16px] text-[#74716A]">{!! $nosotros->texto_icono2 !!}</h3>
                    </div>
                    
                    <div class="w-full lg:w-1/3 border rounded-[8px] flex flex-col items-center text-center bg-[#FFFFFF]  h-[392px]  px-[20px] pt-[100px]">
                        <div class="flex-shrink-0">
                            <img src="{{$nosotros->icono3}}" alt="" class="mx-auto">
                        </div>
                        <h1 class="text-[20px] font-semibold text-[#242424] mt-[40px] mb-[10px]">{{$nosotros->nombre_icono3}}</h1>
                        <h3 class="text-[16px] text-[#74716A]">{!! $nosotros->texto_icono3 !!}</h3>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</div>

@endsection

