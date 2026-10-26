{{-- Pasos de la compra del usuario público. Variable: $activo (1 a 4) --}}
@php
    // [número, título, subtítulo]
    $pasos = [
        [1, 'Carrito de la compra', 'Administre sus artículos'],
        [2, 'Forma de envío',       'Seleccione forma de envío'],
        [3, 'Forma de pago',        'Seleccione forma de pago'],
        [4, 'Finalizar compra',     'Revise la orden'],
    ];
@endphp
<div class="bg-[#F6F8FA]">
    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px]">
        <ol class="grid grid-cols-2 gap-x-[24px] gap-y-[12px] py-[16px] lg:grid-cols-[repeat(3,312px)_1fr] lg:gap-0 lg:h-[77px] lg:py-0 lg:pl-[15px] lg:items-center">
            @foreach($pasos as [$numero, $titulo, $subtitulo])
                <li class="flex items-center gap-[8px] {{ $numero === $activo ? 'text-black' : 'text-[#808080]' }}" @if($numero === $activo) aria-current="step" @endif>
                    <span class="text-[24px] leading-[31px] font-semibold">{{ str_pad($numero, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="flex flex-col">
                        <span class="text-[14px] leading-[17px] font-semibold">{{ $titulo }}</span>
                        <span class="text-[10px] leading-[12px]">{{ $subtitulo }}</span>
                    </span>
                </li>
            @endforeach
        </ol>
    </div>
</div>
