@extends('layouts.web')

@section('title', 'Finalizar compra')

@section('content')
<div class="w-full text-black" style="font-family: 'Montserrat', sans-serif;">

    {{-- breadcrumb --}}
    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pt-[15px]">
        <p class="text-[12px] leading-[15px]"><a class="font-semibold" href="{{ route('home') }}">Inicio /</a> Carrito</p>
    </div>

    {{-- pasos de la compra --}}
    <div class="mt-[22px]">
        @include('content.web.partials.compra-pasos', ['activo' => 4])
    </div>

    <form action="{{ route('compra.realizar') }}" method="POST"
          class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pt-[48px] pb-[120px] grid grid-cols-1 lg:grid-cols-[808fr_392fr] gap-x-[24px] gap-y-[32px] items-start">
        @csrf

        {{-- datos de facturación --}}
        @include('content.web.partials.datos-facturacion', ['valores' => [
            'nombre' => $usuario->nombre,
            'cuit' => $usuario->cuit,
            'email' => $usuario->email,
            'celular' => $usuario->celular,
            'direccion' => $usuario->direccion,
            'localidad' => $usuario->localidad,
            'provincia' => $usuario->provincia,
            'codigo_postal' => $usuario->codigo_postal,
        ]])

        {{-- pedido, envío y pago --}}
        <div class="lg:mt-[39px]">
            @include('content.web.partials.compra-pedido')

            <section class="mt-[24px] border border-[#D9D9D9] rounded-[12px] px-[13px]">
                <h2 class="h-[61px] pt-[27px] border-b border-[#D9D9D9] text-[24px] leading-[29px] font-semibold">Forma de envío</h2>
                <div class="flex items-center justify-between gap-[8px] pt-[20px] pb-[19px] text-[20px] leading-[22px] font-medium">
                    <span>{{ $envioTexto }}</span>
                    <a href="{{ route('compra.envio') }}" aria-label="Cambiar la forma de envío" class="flex-shrink-0 hover:opacity-60 transition-opacity">
                        <img src="{{ asset('assets/img/canje/editar.svg') }}" alt="" class="w-[18px] h-[18px] max-w-none">
                    </a>
                </div>
            </section>

            <section class="mt-[24px] border border-[#D9D9D9] rounded-[12px] px-[13px]">
                <h2 class="h-[61px] pt-[27px] border-b border-[#D9D9D9] text-[24px] leading-[29px] font-semibold">Forma de pago</h2>
                <div class="flex items-center justify-between gap-[8px] pt-[20px] pb-[19px] text-[20px] leading-[22px] font-medium">
                    <span>{{ $pagoTexto }}</span>
                    <a href="{{ route('compra.pago') }}" aria-label="Cambiar la forma de pago" class="flex-shrink-0 hover:opacity-60 transition-opacity">
                        <img src="{{ asset('assets/img/canje/editar.svg') }}" alt="" class="w-[18px] h-[18px] max-w-none">
                    </a>
                </div>
            </section>

            <div class="flex justify-end gap-[24px] mt-[24px]">
                <a href="{{ route('compra.pago') }}"
                   class="inline-flex items-center justify-center p-[16px] border border-black rounded-[22px] text-[16px] leading-[19px] font-semibold uppercase transition-colors hover:bg-black hover:text-white">
                    Volver
                </a>
                <button type="submit" id="btn-realizar-pedido"
                        class="p-[16px] bg-black border border-black rounded-[22px] text-[16px] leading-[19px] font-semibold uppercase text-white transition-opacity hover:opacity-80 disabled:opacity-40">
                    Realizar pedido
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    // evita confirmar dos veces el mismo pedido
    document.querySelector('form[action="{{ route('compra.realizar') }}"]').addEventListener('submit', () => {
        document.getElementById('btn-realizar-pedido').disabled = true;
    });
</script>
@endsection
