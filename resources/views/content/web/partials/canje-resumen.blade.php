{{-- Resumen del canje (productos y subtotal en puntos).
     Variables: $productos, $subtotal, $conQuitar (muestra el link para quitar cada producto) --}}
@php $miles = fn ($n) => number_format($n, 0, ',', '.'); @endphp
<section class="border border-[#D9D9D9] rounded-[12px] px-[13px]">
    <h2 class="h-[69px] pt-[27px] border-b border-[#D9D9D9] text-[24px] leading-[29px] font-semibold pl-[10px]">Canje</h2>

    <div class="flex flex-col gap-[16px] py-[16px] border-b border-[#D9D9D9]">
        @forelse($productos as $producto)
            <div class="flex items-stretch min-h-[95px]">
                <div class="flex items-center justify-center flex-shrink-0 w-[95px] h-[95px] p-[4px] border border-[#D9D9D9] rounded-[4px] overflow-hidden">
                    <img src="{{ $producto->foto_url }}" alt="{{ $producto->nombre }}" class="w-full h-full object-contain">
                </div>
                <div class="flex flex-col justify-between flex-1 min-w-0 pl-[10px]">
                    <p class="pt-[14px] text-[16px] leading-[22px] font-medium">{{ $producto->nombre }}</p>
                    <div class="flex items-center justify-between gap-[8px]">
                        @if(!empty($conQuitar))
                            <form action="{{ route('web-canje.quitar') }}" method="POST">
                                @csrf
                                <input type="hidden" name="producto_id" value="{{ $producto->id }}">
                                <button type="submit" class="text-[12px] leading-[22px] underline hover:opacity-60 transition-opacity">Quitar</button>
                            </form>
                        @else
                            <span></span>
                        @endif
                        <p class="text-[16px] leading-[22px] font-medium text-right">{{ $miles($producto->puntos) }} puntos</p>
                    </div>
                </div>
            </div>
        @empty
            <p class="py-[20px] text-[16px] leading-[22px] text-[#5C5C5C]">Todavía no agregaste productos para canjear.</p>
        @endforelse
    </div>

    <div class="flex items-baseline justify-between gap-[8px] pt-[24px] pb-[19px] pl-[10px] text-[24px] leading-[29px] font-medium">
        <span>Subtotal</span>
        <span>{{ $miles($subtotal) }} puntos</span>
    </div>
</section>
