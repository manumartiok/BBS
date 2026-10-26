@extends('layouts.layout-clientes')

@section('title', 'Carrito de canje')

@section('content')
@php
    $miles = fn ($n) => number_format($n, 0, ',', '.');
    $faltan = max(0, $subtotal - $puntos);
    $puedeContinuar = $productos->isNotEmpty() && $faltan === 0;
    $envioActual = $envioElegido['envio'] ?? 'mercado_flex';
@endphp

<div class="w-full text-black" style="font-family: 'Montserrat', sans-serif;">
    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pt-[49px] pb-[80px]">

        @if(session('error'))
            <div class="mb-[16px] px-[16px] py-[12px] border border-[#FF0000] rounded-[12px] text-[14px] text-[#FF0000]">{{ session('error') }}</div>
        @endif

        <h1 class="mb-[12px] text-[24px] leading-[29px] font-semibold">Seleccione la forma de envío</h1>

        <div class="grid grid-cols-1 lg:grid-cols-[808fr_392fr] gap-x-[24px] gap-y-[24px] items-start">

            {{-- forma de envío --}}
            <div>
                @include('content.web.partials.forma-envio', ['formId' => 'form-envio', 'envioActual' => $envioActual, 'cpActual' => $envioElegido['codigo_postal'] ?? '', 'contacto' => $contacto])

                <a href="{{ route('web-canje') }}"
                   class="inline-flex items-center justify-center mt-[19px] p-[16px] border border-black rounded-[22px] text-[16px] leading-[19px] font-semibold uppercase transition-colors hover:bg-black hover:text-white">
                    Volver
                </a>
            </div>

            {{-- resumen del canje --}}
            <div>
                @include('content.web.partials.canje-resumen', ['conQuitar' => true])

                @if($faltan > 0)
                    <p class="mt-[12px] text-right text-[14px] leading-[18px] text-[#FF0000]">Te faltan {{ $miles($faltan) }} puntos para este canje.</p>
                @endif

                <form id="form-envio" action="{{ route('web-canje.envio') }}" method="POST" class="flex justify-end mt-[24px]">
                    @csrf
                    <button type="submit" id="btn-siguiente" @disabled(!$puedeContinuar)
                            class="p-[16px] bg-black border border-black rounded-[22px] text-[16px] leading-[19px] font-semibold uppercase text-white transition-opacity {{ $puedeContinuar ? 'hover:opacity-80' : 'opacity-40 cursor-not-allowed' }}">
                        Siguiente
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
