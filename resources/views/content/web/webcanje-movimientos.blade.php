@extends('layouts.layout-clientes')

@section('title', 'Movimiento de puntos')

@section('content')
<div class="w-full text-black" style="font-family: 'Montserrat', sans-serif;">
    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pt-[31px] pb-[180px]">

        @include('content.web.partials.canje-tabs', ['activa' => 'movimiento'])

        <div class="overflow-x-auto mt-[31px]">
            <table class="w-full min-w-[1224px] table-fixed border-collapse text-[16px]">
                <colgroup>
                    <col style="width: 101px">
                    <col style="width: 249px">
                    <col style="width: 178px">
                    <col style="width: 197px">
                    <col style="width: 240px">
                    <col style="width: 130px">
                    <col style="width: 129px">
                </colgroup>
                <thead>
                    <tr class="bg-[#F6F8FA] h-[50px] border-b border-[#D9D9D9] font-semibold uppercase">
                        <th></th>
                        <th class="text-left font-semibold">Producto</th>
                        <th class="text-right font-semibold">Puntos canjeados</th>
                        <th class="text-left font-semibold pl-[57px]">Fecha</th>
                        <th class="text-left font-semibold">N° guía transporte</th>
                        <th class="text-left font-semibold">Estado</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($canjes as $canje)
                        <tr class="h-[115px] border-b border-[#D9D9D9]">
                            <td>
                                <div class="w-[80px] h-[83px] border border-[#D9D9D9] rounded-[12px] flex items-center justify-center">
                                    <img src="{{ asset('assets/img/pedidos/file-text.svg') }}" alt="" class="w-[43px] h-[43px]">
                                </div>
                            </td>
                            <td class="pr-[16px]">
                                @foreach($canje->items as $item)
                                    <p class="leading-[22px]">{{ $item->nombre }}</p>
                                @endforeach
                            </td>
                            <td class="text-right">{{ number_format($canje->puntos, 0, ',', '.') }}</td>
                            <td class="pl-[57px]">{{ $canje->created_at?->format('d/m/Y') }}</td>
                            <td>
                                @if($canje->guia_transporte)
                                    <span class="inline-flex items-center gap-[2px]">
                                        <span>{{ $canje->guia_transporte }}</span>
                                        <button type="button" class="btn-copiar-guia" data-guia="{{ $canje->guia_transporte }}" title="Copiar" aria-label="Copiar número de guía">
                                            <img src="{{ asset('assets/img/pedidos/copy.svg') }}" alt="" class="w-[12.2px] h-[15px] max-w-none">
                                        </button>
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td style="color: {{ $canje->estado_color }}">{{ $canje->estado_texto }}</td>
                            <td class="text-right">
                                <a href="{{ route('web-canje.ver', $canje) }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center justify-center w-[119px] p-[16px] border border-black rounded-[22px] font-semibold uppercase whitespace-nowrap leading-none hover:bg-black hover:text-white transition">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-gray-600">
                                Todavia no realizaste canjes.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            {{ $canjes->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.querySelectorAll('.btn-copiar-guia').forEach(btn => {
    btn.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(btn.dataset.guia);
            btn.title = 'Copiado';
            setTimeout(() => btn.title = 'Copiar', 1500);
        } catch (e) {
            // sin permiso de portapapeles: no hacemos nada
        }
    });
});
</script>
@endsection
