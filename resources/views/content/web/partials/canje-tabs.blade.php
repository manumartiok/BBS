{{-- Pestañas del canje de puntos y saldo del usuario.
     Variables: $activa ('productos' | 'carrito' | 'movimiento'), $puntos, $cantidadCarrito --}}
@php
    $pestanias = [
        ['productos',  'Productos',            route('web-canje')],
        ['carrito',    'Carrito' . (($cantidadCarrito ?? 0) > 0 ? ' (' . $cantidadCarrito . ')' : ''), route('web-canje.carrito')],
        ['movimiento', 'Movimiento de puntos', route('web-canje.movimientos')],
    ];
@endphp
<div class="flex items-start justify-between gap-[24px]">
    <div class="flex flex-wrap items-start gap-x-[26px] gap-y-[8px] text-[20px] leading-[24px] uppercase">
        @foreach($pestanias as [$clave, $texto, $url])
            <a href="{{ $url }}"
               class="pb-[2px] border-b transition-colors {{ $activa === $clave ? 'border-black font-semibold text-black' : 'border-[#5C5C5C] text-[#5C5C5C] hover:text-black hover:border-black' }}">{{ $texto }}</a>
        @endforeach
    </div>
    <p class="pt-[29px] text-[17px] leading-[21px] font-semibold text-[#0039B4] whitespace-nowrap">Mis puntos: {{ number_format($puntos, 0, ',', '.') }}</p>
</div>
