{{-- Resumen "Pedido" de la compra del usuario público. Variables: $items, $subtotal --}}
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

    <div class="flex items-baseline justify-between gap-[8px] pt-[24px] pb-[19px] pl-[10px] text-[24px] leading-[29px] font-medium">
        <span>Subtotal</span>
        <span>{{ $pesos($subtotal) }}</span>
    </div>
</section>
