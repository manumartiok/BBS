@extends('layouts.layout-clientes')

@section('title', 'Mis pedidos')

@section('content')
<div class="w-full text-black pt-[71px] pb-[180px]" style="font-family: 'Montserrat', sans-serif;">
    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px]">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1224px] table-fixed border-collapse text-[16px]">
                <colgroup>
                    <col style="width: 110px">
                    <col style="width: 88px">
                    <col style="width: 204px">
                    <col style="width: 217px">
                    <col style="width: 120px">
                    <col style="width: 182px">
                    <col style="width: 157px">
                    <col style="width: 146px">
                </colgroup>
                <thead>
                    <tr class="bg-[#F6F8FA] h-[50px] border-b border-[#D9D9D9] font-semibold uppercase">
                        <th></th>
                        <th class="text-right font-semibold">N° pedido</th>
                        <th class="text-left font-semibold pl-[60px]">Fecha</th>
                        <th class="text-left font-semibold">N° guía transporte</th>
                        <th class="text-right font-semibold">Importe</th>
                        <th class="text-left font-semibold pl-[60px]">Estado</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pedidos as $pedido)
                        @php
                            $total = (float) $pedido->total;
                            $importe = '$' . number_format($total, fmod($total, 1) == 0 ? 0 : 2, ',', '.');
                        @endphp
                        <tr class="h-[115px] border-b border-[#D9D9D9]">
                            <td>
                                <div class="w-[80px] h-[83px] border border-[#D9D9D9] rounded-[12px] flex items-center justify-center">
                                    <img src="{{ asset('assets/img/pedidos/file-text.svg') }}" alt="" class="w-[43px] h-[43px]">
                                </div>
                            </td>
                            <td class="text-right">{{ $pedido->id }}</td>
                            <td class="pl-[60px]">{{ $pedido->created_at?->format('d/m/Y') }}</td>
                            <td>
                                @if($pedido->guia_transporte)
                                    <span class="inline-flex items-center gap-[2px]">
                                        <span>{{ $pedido->guia_transporte }}</span>
                                        <button type="button" class="btn-copiar-guia" data-guia="{{ $pedido->guia_transporte }}" title="Copiar" aria-label="Copiar número de guía">
                                            <img src="{{ asset('assets/img/pedidos/copy.svg') }}" alt="" class="w-[12.2px] h-[15px] max-w-none">
                                        </button>
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-right">{{ $importe }}</td>
                            <td class="pl-[60px]" style="color: {{ $pedido->estado_color }}">{{ $pedido->estado_texto }}</td>
                            <td>
                                <a href="{{ route('web-pedidos.pdf', $pedido) }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center justify-center p-[16px] border border-black rounded-[22px] font-semibold uppercase whitespace-nowrap leading-none hover:bg-black hover:text-white transition">
                                    Ver online
                                </a>
                            </td>
                            <td class="text-right">
                                <form action="{{ route('web-pedidos.recomprar', $pedido) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center justify-center p-[16px] bg-black text-white border border-black rounded-[22px] font-semibold uppercase whitespace-nowrap leading-none hover:bg-white hover:text-black transition">
                                        Recomprar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-gray-600">
                                Todavia no realizaste pedidos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            {{ $pedidos->links() }}
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
