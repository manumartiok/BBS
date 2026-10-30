@extends('layouts.layout-clientes')

@section('title', 'Pagar con Mercado Pago')

@section('content')
@php
    $numero = str_pad($pedido->id, 8, '0', STR_PAD_LEFT);
    $pesos = fn ($n) => '$' . number_format((float) $n, 2, ',', '.');
    $totales = $pedido->totales ?? [];
@endphp

<div class="w-full text-black pt-[71px] pb-[180px]" style="font-family: 'Montserrat', sans-serif;">
    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px]">

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
                    Total a pagar: <strong>{{ $pesos($pedido->total) }}</strong>
                </p>
                <p class="mt-[8px] text-[14px] leading-[22px] text-[#555]">
                    Vas a poder pagar con tu cuenta de Mercado Pago, dinero en cuenta, tarjetas guardadas o en cuotas.
                    Desde el celular se abre la app de Mercado Pago.
                    @if($pedido->puntos_ganados > 0)
                        Cuando se apruebe el pago sumás {{ number_format($pedido->puntos_ganados, 0, ',', '.') }} puntos.
                    @endif
                </p>

                @if($preferencia)
                    <div id="mp-wallet" class="mt-[24px] max-w-[420px]"></div>
                    {{-- por si el botón no carga (bloqueador de scripts, etc.) --}}
                    <a href="{{ $preferencia->init_point }}" @unless($retornoAutomatico) target="_blank" rel="noopener" @endunless
                       class="inline-block mt-[8px] text-[14px] leading-[22px] underline">
                        ¿No ves el botón? Pagá en Mercado Pago
                    </a>
                    <a href="{{ route('web-pedidos.mp.pagar', $pedido) }}"
                       class="block w-fit mt-[4px] text-[14px] leading-[22px] underline">
                        ¿Ya pagaste? Verificar el pago
                    </a>
                @else
                    <p class="mt-[24px] p-[12px] rounded-[8px] bg-[#FDECEC] text-[16px] leading-[22px] text-[#B42318]">
                        No pudimos conectar con Mercado Pago. <a href="{{ route('web-pedidos.mp.pagar', $pedido) }}" class="underline font-semibold">Intentá nuevamente</a>.
                    </p>
                @endif

                <a href="{{ route('web-pedidos') }}"
                   class="inline-flex items-center justify-center mt-[30px] p-[16px] border border-black rounded-[22px] text-[16px] leading-[19px] font-semibold uppercase transition-colors hover:bg-black hover:text-white">
                    Mis pedidos
                </a>
            </section>

            {{-- resumen --}}
            <section class="border border-[#D9D9D9] rounded-[12px] px-[13px]">
                <h2 class="h-[69px] pt-[27px] border-b border-[#D9D9D9] text-[24px] leading-[29px] font-semibold pl-[10px]">Pedido</h2>

                <div class="flex flex-col gap-[12px] py-[16px] border-b border-[#D9D9D9] pl-[10px]">
                    @foreach($pedido->items as $item)
                        <div class="flex items-start justify-between gap-[8px] text-[16px] leading-[22px]">
                            <div class="min-w-0">
                                <p class="font-medium">{{ $item->nombre }}</p>
                                <p class="text-[12px] text-[#808080]">{{ $item->codigo }} · Cantidad: {{ $item->cantidad }}</p>
                            </div>
                            <span class="flex-shrink-0 font-medium">{{ $pesos($item->subtotal) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="flex flex-col gap-[12px] pt-[24px] pb-[19px] pl-[10px] leading-[22px]">
                    <div class="flex items-baseline justify-between gap-[8px] text-[18px]">
                        <span>Subtotal</span>
                        <span>{{ $pesos($pedido->subtotal) }}</span>
                    </div>
                    @if(isset($totales['iva_105']))
                        <div class="flex items-baseline justify-between gap-[8px] text-[18px]">
                            <span>IVA 10,5%</span>
                            <span>{{ $pesos($totales['iva_105']) }}</span>
                        </div>
                        <div class="flex items-baseline justify-between gap-[8px] text-[18px]">
                            <span>IVA 21%</span>
                            <span>{{ $pesos($totales['iva_21'] ?? 0) }}</span>
                        </div>
                    @else
                        <div class="flex items-baseline justify-between gap-[8px] text-[18px]">
                            <span>IVA</span>
                            <span>{{ $pesos($pedido->iva) }}</span>
                        </div>
                    @endif
                    <div class="flex items-baseline justify-between gap-[8px] pt-[12px] border-t border-[#D9D9D9] text-[24px] leading-[29px] font-semibold">
                        <span>Total <span class="text-[15px]">(IVA incluido)</span></span>
                        <span>{{ $pesos($pedido->total) }}</span>
                    </div>
                </div>
            </section>
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
