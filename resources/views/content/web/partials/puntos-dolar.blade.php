{{-- Puntos del usuario y cotización del dólar, sobre la tabla de productos y del carrito --}}
<div class="flex items-center justify-between gap-[16px] min-h-[21px] mb-[14px] text-[17px] leading-[21px] font-semibold">
    <p class="text-[#0039B4]">Mis puntos: <span id="mis-puntos">{{ number_format(auth('web_usuario')->user()?->puntos ?? 0, 0, ',', '.') }}</span></p>
    <p class="text-[#030200] text-right">
        @if(!empty($dolar))
            Dolar Oficial: ${{ number_format($dolar, 0, ',', '.') }}
        @endif
    </p>
</div>
