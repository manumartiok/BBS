@extends('layouts.web')

@section('title', 'Pagar con Mercado Pago')

@section('content')
@php
    $numero = str_pad($pedido->id, 8, '0', STR_PAD_LEFT);
    // mismo formato que pide el resumen "Pedido"
    $items = $pedido->items->map(fn ($item) => [
        'foto' => $item->producto?->foto_url ?? asset('assets/img/default.png'),
        'nombre' => $item->nombre,
        'cantidad' => $item->cantidad,
        'subtotal' => $item->subtotal,
    ]);
@endphp

<div class="w-full text-black" style="font-family: 'Montserrat', sans-serif;">

    {{-- breadcrumb --}}
    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pt-[15px]">
        <p class="text-[12px] leading-[15px]"><a class="font-semibold" href="{{ route('home') }}">Inicio /</a> Carrito</p>
    </div>

    {{-- pasos de la compra --}}
    <div class="mt-[22px]">
        @include('content.web.partials.compra-pasos', ['activo' => 4])
    </div>

    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pt-[39px] pb-[120px]">

        <h1 class="mb-[12px] text-[24px] leading-[29px] font-semibold">Pagá tu pedido #{{ $numero }}</h1>

        <div class="grid grid-cols-1 lg:grid-cols-[808fr_392fr] gap-x-[24px] gap-y-[24px] items-start">

            {{-- pago --}}
            <section class="border border-[#D9D9D9] rounded-[12px] pt-[27px] pb-[27px] px-[18px]">
                @if($resultado === 'rechazado')
                    <p class="mb-[20px] p-[12px] rounded-[8px] bg-[#FDECEC] text-[16px] leading-[22px] text-[#B42318]">
                        El pago no se pudo completar. Podés intentarlo de nuevo con otro medio de pago.
                    </p>
                @elseif($resultado === 'pendiente')
                    <p class="mb-[20px] p-[12px] rounded-[8px] bg-[#FFF6E5] text-[16px] leading-[22px] text-[#8A5A00]">
                        Tu pago está pendiente de acreditación. Te vamos a avisar por mail cuando se apruebe.
                    </p>
                @endif

                <p class="text-[20px] leading-[22px] text-[#222]">
                    Total a pagar: <strong>${{ number_format($pedido->total, 2, ',', '.') }}</strong>
                </p>
                <p class="mt-[8px] text-[14px] leading-[22px] text-[#555]">
                    Vas a poder pagar con tu cuenta de Mercado Pago, dinero en cuenta, tarjetas guardadas o en cuotas.
                    Desde el celular se abre la app de Mercado Pago.
                </p>

                @if($preferencia)
                    <div id="mp-wallet" class="mt-[24px] max-w-[420px]"></div>
                    {{-- por si el botón no carga (bloqueador de scripts, etc.) --}}
                    <a href="{{ $preferencia->init_point }}" @unless($retornoAutomatico) target="_blank" rel="noopener" @endunless
                       class="inline-block mt-[8px] text-[14px] leading-[22px] underline">
                        ¿No ves el botón? Pagá en Mercado Pago
                    </a>
                    <a href="{{ route('compra.mp.pagar', $pedido) }}"
                       class="block w-fit mt-[4px] text-[14px] leading-[22px] underline">
                        ¿Ya pagaste? Verificar el pago
                    </a>
                @else
                    <p class="mt-[24px] p-[12px] rounded-[8px] bg-[#FDECEC] text-[16px] leading-[22px] text-[#B42318]">
                        No pudimos conectar con Mercado Pago. <a href="{{ route('compra.mp.pagar', $pedido) }}" class="underline font-semibold">Intentá nuevamente</a>.
                    </p>
                @endif
            </section>

            {{-- resumen --}}
            <div>
                @include('content.web.partials.compra-pedido', [
                    'items' => $items,
                    'subtotal' => $pedido->subtotal,
                    'iva105' => $pedido->iva_105,
                    'iva21' => $pedido->iva_21,
                    'total' => $pedido->total,
                ])
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if($preferencia)
    <script src="https://sdk.mercadopago.com/js/v2"></script>
    <script>
        // Botón de Checkout Pro (Wallet Brick) con la preferencia del pedido. Sin retorno automático
        // (sitio sin https, en local) se abre en otra pestaña y el pago se verifica al recargar esta
        const mp = new MercadoPago(@json($publicKey), { locale: 'es-AR' });
        mp.bricks().create('wallet', 'mp-wallet', {
            initialization: { preferenceId: @json($preferencia->id), redirectMode: @json($retornoAutomatico ? 'self' : 'blank') },
        });
    </script>
@endif
@endsection
