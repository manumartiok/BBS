{{-- Listado de productos: categorías a la izquierda y grilla de cards a la derecha.
     Variables: $marcas, $productos, $marca (categoría activa, opcional), $dolar (opcional) --}}
<div class="w-full text-black" style="font-family: 'Montserrat', sans-serif;">
    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pt-[15px] pb-[80px]">

        {{-- breadcrumb --}}
        <p class="text-[12px] leading-[15px] mb-[26px]">
            <a class="font-semibold" href="{{ route('home') }}">Inicio /</a>
            @if(!empty($marca))
                <a class="font-semibold" href="{{ route('productos') }}">Productos /</a> {{ $marca->nombre }}
            @else
                Productos
            @endif
        </p>

        {{-- cotización --}}
        <p class="min-h-[21px] mb-[24px] text-[17px] leading-[21px] font-semibold text-[#030200] text-right">
            @if(!empty($dolar))
                Dolar Oficial: ${{ number_format($dolar, 0, ',', '.') }}
            @endif
        </p>

        <div class="flex flex-col lg:flex-row gap-[24px]">

            {{-- categorías --}}
            <aside class="w-full lg:w-[288px] flex-shrink-0">
                <ul class="flex flex-col gap-[24px]">
                    @foreach($marcas as $m)
                        @php($activa = !empty($marca) && $marca->id == $m->id)
                        <li>
                            <details class="group" @if($activa) open @endif>
                                <summary class="flex items-center justify-between gap-[8px] h-[48px] px-[10px] bg-white rounded-[12px] shadow-[0px_2px_10px_0px_rgba(0,0,0,0.1)] text-[16px] uppercase cursor-pointer select-none list-none [&::-webkit-details-marker]:hidden transition-shadow hover:shadow-[0px_2px_14px_0px_rgba(0,0,0,0.2)] {{ $activa ? 'font-semibold' : '' }}">
                                    <span class="truncate">{{ $m->nombre }}</span>
                                    <span class="flex items-center justify-center flex-shrink-0 w-[19px] h-[10.45px]">
                                        <img src="{{ asset('assets/img/productos/chevron.svg') }}" alt="" class="w-[10.45px] h-[18.96px] max-w-none -rotate-90 group-open:rotate-90 transition-transform">
                                    </span>
                                </summary>

                                {{-- productos de la categoría: alto máximo, scroll si lo supera --}}
                                <div class="mt-[8px] max-h-[240px] overflow-y-auto bg-white rounded-[12px] shadow-[0px_2px_10px_0px_rgba(0,0,0,0.1)]">
                                    <a href="{{ route('productos.categoria', $m->id) }}" class="hover:scale-100 block px-[10px] py-[10px] text-[14px] font-semibold border-b border-[#D9D9D9] hover:bg-[#F6F8FA]">Ver todos</a>
                                    @foreach($m->productos as $p)
                                        <a href="{{ route('productos.detalle', ['id' => $p->id]) }}" class="hover:scale-100 block px-[10px] py-[8px] text-[14px] leading-[18px] hover:bg-[#F6F8FA]">{{ $p->nombre }}</a>
                                    @endforeach
                                </div>
                            </details>
                        </li>
                    @endforeach
                </ul>
            </aside>

            {{-- productos --}}
            <div class="flex-1 min-w-0">
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-[24px]">
                    @forelse($productos as $producto)
                        @include('content.web.partials.producto-card', ['producto' => $producto])
                    @empty
                        <p class="col-span-full text-[16px] text-[#5C5C5C]">No se encontraron productos.</p>
                    @endforelse
                </div>

                @if(method_exists($productos, 'links'))
                    <div class="mt-[40px]">
                        {{ $productos->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
