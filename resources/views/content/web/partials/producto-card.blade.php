{{-- Card de producto. Variable: $producto (con la relación marca) --}}
<a href="{{ route('productos.detalle', ['id' => $producto->id]) }}"
   class="relative flex flex-col w-full max-w-[288px] sm:max-w-none h-[448px] mx-auto bg-white rounded-[12px] transition-shadow hover:shadow-[0px_2px_10px_0px_rgba(0,0,0,0.1)] after:content-[''] after:absolute after:inset-0 after:rounded-[12px] after:border after:border-[#D9D9D9] after:pointer-events-none">

    {{-- foto --}}
    <div class="flex items-center justify-center flex-shrink-0 w-full h-[288px] overflow-hidden rounded-t-[12px]">
        <img src="{{ $producto->foto_url }}" alt="{{ $producto->nombre }}" class="w-full h-full object-contain">
    </div>

    {{-- oferta --}}
    @if($producto->oferta)
        <span class="absolute left-0 top-0 flex items-center justify-center w-[111px] h-[29px] bg-black text-white text-[12px] font-semibold rounded-[12px]">OFERTA</span>
    @endif

    {{-- divisor --}}
    <img src="{{ asset('assets/img/productos/divider.svg') }}" alt="" class="block flex-shrink-0 w-[calc(100%-24px)] h-[1px] mx-auto max-w-none">

    {{-- datos --}}
    <div class="flex flex-col flex-1 min-h-0 px-[12px] pt-[12px] pb-[12px]">
        <p class="text-[12px] leading-[15px] font-semibold uppercase truncate">{{ $producto->marca->nombre ?? '' }}</p>
        <h2 class="mt-[5px] text-[24px] leading-[29px] font-semibold line-clamp-2 break-words">{{ $producto->nombre }}</h2>

        @if(!is_null($producto->precio_pesos))
            <p class="mt-auto text-[24px] leading-[29px] font-semibold text-right">${{ number_format($producto->precio_pesos, 0, ',', '.') }}</p>
        @endif
    </div>
</a>
