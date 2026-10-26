@php
    $info = App\Models\Info::first();

    // Campos del formulario: mismo estilo (pastilla con borde negro)
    $campo = 'w-full h-[50px] px-[20px] border border-black rounded-[25px] bg-transparent text-[16px] focus:outline-none focus:ring-1 focus:ring-black';
    $etiqueta = 'block mb-[1px] text-[16px] leading-[24px] text-[#111010]';
@endphp

@extends('layouts.layout-clientes')

@section('title', 'Info de pagos')

@section('content')

<div class="w-full text-black" style="font-family: 'Montserrat', sans-serif;">
    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pt-[40px] lg:pt-[71px] pb-[80px] lg:pb-[120px]">
        <div class="flex flex-col lg:flex-row gap-[24px] lg:gap-[23px]">

            {{-- datos bancarios --}}
            <div class="w-full lg:w-[393px] lg:flex-shrink-0">
                <h1 class="text-[24px] leading-[29px] font-semibold">Cuentas bancarias para efectuar el depósito:</h1>

                @if($info?->texto1)
                    <div class="mt-[27px] text-[16px] leading-[24px]">{!! $info->texto1 !!}</div>
                @endif
            </div>

            {{-- formulario --}}
            <div class="w-full lg:flex-1 bg-[#F6F8FA] rounded-[12px] px-[19px] pt-[30px] pb-[21px]">
                @if(session('success'))
                    <div class="mb-4 text-[#107219] font-semibold">{{ session('success') }}</div>
                @endif

                <form action="{{ route('info-pago.enviar') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-[26px] gap-y-[24px]">
                        <div>
                            <label class="{{ $etiqueta }}" for="fecha">Fecha*</label>
                            <input type="date" id="fecha" name="fecha" class="{{ $campo }}" required>
                        </div>
                        <div>
                            <label class="{{ $etiqueta }}" for="importe">Importe*</label>
                            <input type="number" step="0.01" id="importe" name="importe" class="{{ $campo }}" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-x-[21px] gap-y-[24px] mt-[24px]">
                        <div>
                            <label class="{{ $etiqueta }}" for="banco">Banco*</label>
                            <input type="text" id="banco" name="banco" class="{{ $campo }}" required>
                        </div>
                        <div>
                            <label class="{{ $etiqueta }}" for="sucursal">Sucursal*</label>
                            <input type="text" id="sucursal" name="sucursal" class="{{ $campo }}" required>
                        </div>
                        <div>
                            <label class="{{ $etiqueta }}" for="facturas_canceladas">Facturas canceladas</label>
                            <input type="text" id="facturas_canceladas" name="facturas_canceladas" class="{{ $campo }}">
                        </div>
                    </div>

                    <div class="mt-[24px]">
                        <label class="{{ $etiqueta }}" for="observaciones">Observaciones / Aclaraciones</label>
                        <textarea id="observaciones" name="observaciones" class="w-full h-[170px] p-[16px] border border-black rounded-[12px] bg-transparent text-[16px] resize-none focus:outline-none focus:ring-1 focus:ring-black"></textarea>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-end gap-[16px] md:gap-[12px] mt-[24px]">
                        <div class="w-full md:w-[374px] md:flex-shrink-0">
                            <label class="{{ $etiqueta }}" for="archivo-input">Adjuntar archivo*</label>
                            <label for="archivo-input" class="flex items-center justify-between h-[50px] pl-[18px] pr-[17px] border border-black rounded-[25px] cursor-pointer">
                                <span id="nombre-archivo" class="text-[14px] truncate">Seleccionar archivo</span>
                                <img src="{{ asset('assets/img/productos/subir.svg') }}" alt="" class="w-[20px] h-[20px] max-w-none flex-shrink-0">
                            </label>
                            <input type="file" name="archivo" id="archivo-input" class="sr-only" required>
                        </div>

                        <p class="text-[16px] text-[#111010] md:pb-[7px]">* campos obligatorios</p>

                        <button type="submit" class="md:ml-auto p-[16px] bg-black border border-black rounded-[22px] text-[16px] leading-[19px] font-semibold uppercase text-white transition-opacity hover:opacity-80">
                            Enviar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const inputArchivo = document.getElementById('archivo-input');
    const nombreArchivo = document.getElementById('nombre-archivo');

    inputArchivo.addEventListener('change', function () {
        nombreArchivo.textContent = this.files && this.files.length > 0 ? this.files[0].name : 'Seleccionar archivo';
    });
});
</script>
@endsection
