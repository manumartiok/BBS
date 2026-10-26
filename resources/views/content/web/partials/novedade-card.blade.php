{{-- Card de novedad. Variable: $item --}}
    @php
        // El texto viene del editor (HTML): en la card se muestra como resumen de texto plano
        $resumen = html_entity_decode(strip_tags(preg_replace('/<\/(p|div|li|h[1-6])>/i', '$0 ', $item->texto ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    @endphp
    <a href="{{ route('novedades-detalle', ['id' => $item->id]) }}"
       class="relative flex flex-col min-h-[480px] bg-white rounded-[12px] transition-shadow duration-200 hover:shadow-[0px_4px_15px_0px_rgba(0,0,0,0.1)] after:content-[''] after:absolute after:inset-0 after:rounded-[12px] after:border after:border-[#D9D9D9] after:pointer-events-none">

        {{-- foto --}}
        <div class="h-[280px] flex-shrink-0 rounded-t-[12px] overflow-hidden bg-[#F2F2F2]">
            @if(!empty($item->foto))
                <img src="{{ $item->foto }}" alt="{{ $item->titulo }}" class="w-full h-full object-cover">
            @endif
        </div>

        <div class="flex flex-col flex-grow px-[12px] pt-[16px] pb-[12px]">
            <span class="text-[12px] font-semibold leading-[15px]">{{ $item->texto_chico }}</span>
            <h2 class="mt-[2px] text-[24px] font-semibold leading-[29px]">{{ $item->titulo }}</h2>
            <p class="mt-[10px] text-[16px] leading-[20px] line-clamp-2">{{ $resumen }}</p>

            <div class="flex items-center mt-auto">
                <span class="w-[76px] text-[12px] font-semibold leading-[15px]">LEER MÁS</span>
                <img src="{{ asset('assets/img/novedades/arrow.svg') }}" alt="" class="w-[19px] h-[14px] max-w-none">
            </div>
        </div>
    </a>
