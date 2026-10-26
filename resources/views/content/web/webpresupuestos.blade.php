@extends('layouts.layout-clientes')

@section('title', 'Mis presupuestos')

@section('content')
<div class="w-full text-black pt-[71px] pb-[180px]" style="font-family: 'Montserrat', sans-serif;">
    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px]">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1224px] table-fixed border-collapse text-[16px]">
                <colgroup>
                    <col style="width: 110px">
                    <col style="width: 81px">
                    <col style="width: 184px">
                    <col style="width: 213px">
                    <col style="width: 274px">
                    <col style="width: 92px">
                    <col style="width: 93px">
                    <col style="width: 177px">
                </colgroup>
                <thead>
                    <tr class="bg-[#F6F8FA] h-[50px] border-b border-[#D9D9D9] font-semibold uppercase">
                        <th></th>
                        <th class="text-right font-semibold">N° pres.</th>
                        <th class="text-right font-semibold">Creación</th>
                        <th class="text-right font-semibold">Vencimiento</th>
                        <th class="text-left font-semibold pl-[95px]">Estado</th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($presupuestos as $presupuesto)
                        <tr class="h-[115px] border-b border-[#D9D9D9]">
                            <td>
                                <div class="w-[80px] h-[83px] border border-[#D9D9D9] rounded-[12px] flex items-center justify-center">
                                    <img src="{{ asset('assets/img/pedidos/file-text.svg') }}" alt="" class="w-[43px] h-[43px]">
                                </div>
                            </td>
                            <td class="text-right">{{ $presupuesto->id }}</td>
                            <td class="text-right">{{ $presupuesto->created_at?->format('d/m/Y') }}</td>
                            <td class="text-right">{{ $presupuesto->fecha_vencimiento->format('d/m/Y') }}</td>
                            <td class="pl-[95px]" style="color: {{ $presupuesto->estado_color }}">{{ $presupuesto->estado_texto }}</td>
                            <td>
                                <a href="{{ route('web-presupuestos.ver', $presupuesto) }}" target="_blank" rel="noopener"
                                   class="inline-flex items-center justify-center p-[16px] border border-black rounded-[22px] font-semibold uppercase whitespace-nowrap leading-none hover:bg-black hover:text-white transition">
                                    Ver
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('web-presupuestos.pdf', $presupuesto) }}"
                                   class="inline-flex items-center justify-center p-[16px] border border-black rounded-[22px] font-semibold uppercase whitespace-nowrap leading-none hover:bg-black hover:text-white transition">
                                    PDF
                                </a>
                            </td>
                            <td class="text-right">
                                <form action="{{ route('web-presupuestos.pedido', $presupuesto) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center justify-center p-[16px] bg-black text-white border border-black rounded-[22px] font-semibold uppercase whitespace-nowrap leading-none hover:bg-white hover:text-black transition">
                                        Pasar a pedido
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-gray-600">
                                Todavia no realizaste presupuestos.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            {{ $presupuestos->links() }}
        </div>
    </div>
</div>
@endsection
