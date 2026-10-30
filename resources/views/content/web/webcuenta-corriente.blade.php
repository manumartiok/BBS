@extends('layouts.layout-clientes')

@section('title', 'Cuenta corriente')

@php
    $moneda = fn ($valor) => '$' . number_format((float) $valor, 2, ',', '.');
@endphp

@section('content')
<div class="w-full text-black pt-[71px] pb-[180px]" style="font-family: 'Montserrat', sans-serif;">
    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px]">

        {{-- Saldos --}}
        <div class="flex flex-col sm:flex-row sm:justify-end gap-[24px] mb-[24px]">
            <div class="w-full sm:w-[392px] h-[74px] flex flex-col items-end justify-center gap-[7px] px-[12px] bg-[#F6F8FA] border border-[#D9D9D9] rounded-[12px] text-right">
                <span class="text-[20px] text-[#C92020]">Saldo vencido</span>
                <span class="text-[16px] font-semibold text-[#C92020]">$ {{ number_format((float) $saldoVencido, 2, ',', '.') }}</span>
            </div>
            <div class="w-full sm:w-[392px] h-[74px] flex flex-col items-end justify-center gap-[7px] px-[12px] bg-[#F6F8FA] border border-[#D9D9D9] rounded-[12px] text-right">
                <span class="text-[20px]">Saldo total</span>
                <span class="text-[16px] font-semibold">$ {{ number_format((float) $saldoTotal, 2, ',', '.') }}</span>
            </div>
        </div>

        {{-- Comprobantes --}}
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1224px] table-fixed border-collapse text-[16px]">
                <colgroup>
                    <col style="width: 110px">
                    <col style="width: 199px">
                    <col style="width: 140px">
                    <col style="width: 106px">
                    <col style="width: 166px">
                    <col style="width: 165px">
                    <col style="width: 229px">
                    <col style="width: 109px">
                </colgroup>
                <thead>
                    <tr class="bg-[#F6F8FA] h-[50px] border-b border-[#D9D9D9] uppercase">
                        <th class="text-right font-semibold">Emisión</th>
                        <th class="text-right font-semibold">Vencimiento</th>
                        <th class="text-left font-semibold pl-[80px]">Tipo</th>
                        <th class="text-right font-semibold">N°</th>
                        <th class="text-right font-semibold">Haber</th>
                        <th class="text-right font-semibold">Saldo</th>
                        <th class="text-right font-semibold">Importe origen</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($comprobantes as $comprobante)
                        <tr class="h-[57px] border-b border-[#D9D9D9] leading-[1.5]">
                            <td class="text-right">{{ $comprobante->fecha_emision?->format('d/m/Y') }}</td>
                            <td class="text-right">{{ $comprobante->fecha_vencimiento?->format('d/m/Y') }}</td>
                            <td class="pl-[80px]">{{ $comprobante->tipo }}</td>
                            <td class="text-right whitespace-nowrap">{{ $comprobante->numero }}</td>
                            <td class="text-right">{{ $moneda($comprobante->haber) }}</td>
                            <td class="text-right">{{ $moneda($comprobante->saldo) }}</td>
                            <td class="text-right">{{ $moneda($comprobante->importe) }}</td>
                            <td class="text-right pr-[13px]">
                                <a href="{{ route('web-cuenta-corriente.pdf', $comprobante) }}" title="Descargar comprobante" aria-label="Descargar comprobante {{ $comprobante->tipo }} {{ $comprobante->numero }}" class="inline-flex align-middle">
                                    <img src="{{ asset('assets/img/cuenta-corriente/download.svg') }}" alt="" width="16" height="16" class="max-w-none">
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-gray-600">
                                No hay comprobantes en tu cuenta corriente.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-8">
            {{ $comprobantes->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
@if(session('error'))
    Swal.fire({ icon: 'error', title: 'Error', text: @json(session('error')), confirmButtonColor: '#0D1A35' });
@endif
</script>
@endsection
