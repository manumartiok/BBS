{{-- Card de "Descubre más". Variable: $producto --}}
<a href="{{ route('productos.detalle', ['id' => $producto->id]) }}" class="flex flex-col min-h-[477px] px-[12px] bg-white rounded-[12px] border border-[#D9D9D9] transition-shadow hover:shadow-[0px_4px_15px_0px_rgba(0,0,0,0.1)]">
    <div class="flex items-center justify-center h-[302px] flex-shrink-0">
        <img src="{{ $producto->foto_url }}" alt="{{ $producto->nombre }}" class="w-[343px] max-w-full h-[252px] object-contain">
    </div>
    <img src="{{ asset('assets/img/home/descubre-linea.svg') }}" alt="" class="block w-full h-[1px] max-w-none">
    <h3 class="mt-[14px] text-[24px] leading-[29px] font-semibold line-clamp-2 break-words">{{ $producto->nombre }}</h3>
    @if(!empty($producto->descripcion))
        <p class="mt-[8px] text-[16px] leading-[20px] line-clamp-2">{{ strip_tags($producto->descripcion) }}</p>
    @endif
    <div class="flex items-center mt-auto pb-[12px]">
        <span class="w-[102px] text-[12px] font-semibold leading-[15px]">VER PRODUCTO</span>
        <img src="{{ asset('assets/img/novedades/arrow.svg') }}" alt="" class="w-[19px] h-[14px] max-w-none">
    </div>
</a>
