@extends('layouts.layout-clientes')

@section('title', 'Detalle de facturación')

@section('content')
<div class="w-full text-black" style="font-family: 'Montserrat', sans-serif;">
    <form action="{{ route('web-canje.realizar') }}" method="POST"
          class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pt-[49px] pb-[80px] grid grid-cols-1 lg:grid-cols-[808fr_392fr] gap-x-[24px] gap-y-[32px] items-start">
        @csrf

        @include('content.web.partials.datos-facturacion', ['valores' => [
            'nombre' => $usuario->razon_social ?: $usuario->usuario,
            'cuit' => $usuario->cuit,
            'email' => $usuario->email,
            'celular' => $usuario->telefono,
            'direccion' => $usuario->domicilio,
            'localidad' => $usuario->localidad,
            'provincia' => $usuario->provincia,
            'codigo_postal' => $usuario->codigo_postal,
        ]])

        {{-- resumen --}}
        <div class="lg:mt-[41px]">
            @include('content.web.partials.canje-resumen', ['conQuitar' => false])

            <section class="mt-[24px] border border-[#D9D9D9] rounded-[12px] px-[13px]">
                <h2 class="h-[61px] pt-[27px] pl-[0px] border-b border-[#D9D9D9] text-[24px] leading-[29px] font-semibold">Forma de envío</h2>
                <div class="flex items-center justify-between gap-[8px] pt-[20px] pb-[19px] text-[20px] leading-[22px] font-medium">
                    <span>{{ $envioTexto }}</span>
                    <a href="{{ route('web-canje.carrito') }}" aria-label="Cambiar la forma de envío" class="flex-shrink-0 hover:opacity-60 transition-opacity">
                        <img src="{{ asset('assets/img/canje/editar.svg') }}" alt="" class="w-[18px] h-[18px] max-w-none">
                    </a>
                </div>
            </section>

            <div class="flex justify-end gap-[24px] mt-[32px]">
                <a href="{{ route('web-canje.carrito') }}"
                   class="inline-flex items-center justify-center p-[16px] border border-black rounded-[22px] text-[16px] leading-[19px] font-semibold uppercase transition-colors hover:bg-black hover:text-white">
                    Volver
                </a>
                <button type="submit" id="btn-realizar-canje"
                        class="p-[16px] bg-black border border-black rounded-[22px] text-[16px] leading-[19px] font-semibold uppercase text-white transition-opacity hover:opacity-80 disabled:opacity-40">
                    Realizar canje
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    // evita confirmar dos veces el mismo canje
    document.querySelector('form').addEventListener('submit', () => {
        document.getElementById('btn-realizar-canje').disabled = true;
    });
</script>
@endsection
