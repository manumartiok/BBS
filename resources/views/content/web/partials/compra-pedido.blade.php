{{-- Resumen "Pedido" de la compra del usuario público. Variables: $items, $subtotal; con $total, $iva105 e $iva21 (paso 4) muestra el IVA y el total --}}
@php $pesos = fn ($n) => '$' . number_format($n, 0, ',', '.'); @endphp
<section class="border border-[#D9D9D9] rounded-[12px] px-[13px]">
    <h2 class="h-[69px] pt-[27px] border-b border-[#D9D9D9] text-[24px] leading-[29px] font-semibold pl-[10px]">Pedido</h2>

    <div class="flex flex-col gap-[16px] py-[16px] border-b border-[#D9D9D9]">
        @foreach($items as $item)
            <div class="flex items-stretch min-h-[95px]">
                <div class="flex items-center justify-center flex-shrink-0 w-[95px] h-[95px] p-[4px] border border-[#D9D9D9] rounded-[4px] overflow-hidden">
                    <img src="{{ $item['foto'] }}" alt="{{ $item['nombre'] }}" class="w-full h-full object-contain">
                </div>
                <div class="flex flex-col justify-between flex-1 min-w-0 pl-[10px]">
                    <div class="pt-[14px]">
                        <p class="text-[16px] leading-[22px] font-medium">{{ $item['nombre'] }}</p>
                        <p class="text-[12px] leading-[22px] font-medium text-[#808080]">Cantidad: {{ $item['cantidad'] }}</p>
                    </div>
                    <p class="text-[16px] leading-[22px] font-medium text-right">{{ $pesos($item['subtotal']) }}</p>
                </div>
            </div>
        @endforeach
    </div>

    @if(isset($total))
        {{-- paso 4: subtotal + IVA de cada alícuota = total --}}
        <div class="flex flex-col gap-[12px] pt-[24px] pb-[19px] pl-[10px] leading-[22px]">
            <div class="flex items-baseline justify-between gap-[8px] text-[20px] font-medium">
                <span>Subtotal</span>
                <span>{{ $pesos($subtotal) }}</span>
            </div>
            <div class="flex items-baseline justify-between gap-[8px] text-[18px]">
                <span>IVA 10,5%</span>
                <span>{{ $pesos($iva105) }}</span>
            </div>
            <div class="flex items-baseline justify-between gap-[8px] text-[18px]">
                <span>IVA 21%</span>
                <span>{{ $pesos($iva21) }}</span>
            </div>
            <div class="flex items-baseline justify-between gap-[8px] pt-[12px] border-t border-[#D9D9D9] text-[24px] leading-[29px] font-semibold">
                <span>Total <span class="text-[15px]">(IVA incluido)</span></span>
                <span>{{ $pesos($total) }}</span>
            </div>
        </div>
    @else
        <div class="flex items-baseline justify-between gap-[8px] pt-[24px] pb-[19px] pl-[10px] text-[24px] leading-[29px] font-medium">
            <span>Subtotal</span>
            <span>{{ $pesos($subtotal) }}</span>
        </div>
    @endif
</section>
