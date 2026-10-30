@extends('layouts.master')

@section('title', 'Cuentas corrientes')

@php
    $moneda = fn ($valor) => '$ ' . number_format((float) $valor, 2, ',', '.');
@endphp

@section('content')

<div>
    <h3 class="text-[20px] font-bold text-gray-500">Cuentas corrientes</h3>
    <hr class="mx-6">
</div>

<div class="p-4">
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    {{-- Totales (de los comprobantes filtrados) --}}
    {{-- <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div class="border rounded-2xl bg-white shadow px-6 py-4">
            <p class="text-sm text-gray-500">Saldo total</p>
            <p class="text-2xl font-bold text-gray-800">{{ $moneda($saldoTotal) }}</p>
        </div>
        <div class="border rounded-2xl bg-white shadow px-6 py-4">
            <p class="text-sm text-gray-500">Saldo vencido</p>
            <p class="text-2xl font-bold text-red-600">{{ $moneda($saldoVencido) }}</p>
        </div>
        <div class="border rounded-2xl bg-white shadow px-6 py-4">
            <p class="text-sm text-gray-500">Última sincronización con Contabilium</p>
            <p class="text-2xl font-bold text-gray-800">
                {{ $ultimaSincronizacion ? \Illuminate\Support\Carbon::parse($ultimaSincronizacion)->format('d/m/Y H:i') : 'Nunca' }}
            </p>
            <p class="text-xs text-gray-400 mt-1">Se actualiza con <code>php artisan sincronizar</code></p>
        </div>
    </div> --}}

    <div class="overflow-x-auto rounded-2xl shadow">
        <div class="flex items-center px-6 py-3 border rounded gap-4 bg-white">
            <form method="GET" action="{{ route('adm.cuentas-corrientes') }}" class="flex flex-wrap items-center gap-2 flex-1">
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Buscar por cliente, número, tipo, CUIT o email..."
                    class="w-full max-w-xl border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                >
                <select name="estado" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">Todos</option>
                    <option value="pendientes" @selected(request('estado') === 'pendientes')>Con saldo pendiente</option>
                    <option value="vencidos" @selected(request('estado') === 'vencidos')>Vencidos</option>
                </select>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-1.5 rounded-lg transition">
                    <i class="fa-solid fa-magnifying-glass"></i> Buscar
                </button>
                @if(request('q') || request('estado'))
                    <a href="{{ route('adm.cuentas-corrientes') }}" class="text-sm text-gray-500 hover:text-red-500 transition">
                        <i class="fa-solid fa-xmark"></i> Limpiar
                    </a>
                @endif
            </form>
        </div>

        <table class="min-w-full border border-gray-200 bg-white">
            <thead class="bg-gray-100">
                <tr class="text-gray-700 text-sm uppercase">
                    <th class="px-4 py-3 text-left">Cliente</th>
                    <th class="px-4 py-3 text-left">Tipo</th>
                    <th class="px-4 py-3 text-left">N°</th>
                    <th class="px-4 py-3 text-left">Emisión</th>
                    <th class="px-4 py-3 text-left">Vencimiento</th>
                    <th class="px-4 py-3 text-right">Importe origen</th>
                    <th class="px-4 py-3 text-right">Haber</th>
                    <th class="px-4 py-3 text-right">Saldo</th>
                    <th class="px-4 py-3 text-center">Estado</th>
                    <th class="px-4 py-3 text-center">PDF</th>
                </tr>
            </thead>

            <tbody class="text-gray-600">
                @forelse($comprobantes as $comprobante)
                    @php
                        $pendiente = $comprobante->saldo != 0;
                        $vencido = $pendiente && $comprobante->fecha_vencimiento?->lt(today());
                    @endphp
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex flex-col">
                                <span class="font-medium text-gray-800">{{ $comprobante->razon_social ?: 'Sin razón social' }}</span>
                                @if($comprobante->usuario)
                                    <a href="{{ route('adm.web-usuarios-editor', $comprobante->usuario->id) }}" class="text-xs text-blue-600 hover:underline">
                                        Usuario web: {{ $comprobante->usuario->email ?: $comprobante->usuario->usuario }}
                                    </a>
                                    @if($comprobante->usuario->cuit)
                                        <span class="text-xs text-gray-400">CUIT: {{ $comprobante->usuario->cuit }}</span>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400">Sin usuario web vinculado</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">{{ $comprobante->tipo }}</td>
                        <td class="px-4 py-3 font-mono text-sm whitespace-nowrap">{{ $comprobante->numero }}</td>
                        <td class="px-4 py-3">{{ $comprobante->fecha_emision?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">{{ $comprobante->fecha_vencimiento?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">{{ $moneda($comprobante->importe) }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">{{ $moneda($comprobante->haber) }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap font-semibold">{{ $moneda($comprobante->saldo) }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($vencido)
                                <span class="bg-red-500 text-white text-xs px-2 py-1 rounded">Vencido</span>
                            @elseif($pendiente)
                                <span class="bg-yellow-500 text-white text-xs px-2 py-1 rounded">Pendiente</span>
                            @else
                                <span class="bg-green-500 text-white text-xs px-2 py-1 rounded">Pagado</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('adm.cuentas-corrientes-pdf', $comprobante) }}" class="text-blue-600 hover:text-blue-800" title="Descargar PDF">
                                <i class="fa-solid fa-download"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-10 text-gray-400">
                            No se encontraron comprobantes
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($comprobantes->hasPages())
            <div class="flex items-center justify-between px-6 py-3 border-t bg-white text-sm text-gray-600">
                <span>
                    Mostrando {{ $comprobantes->firstItem() }}-{{ $comprobantes->lastItem() }}
                    de {{ $comprobantes->total() }} comprobantes
                </span>
                <div class="flex gap-1">
                    @if($comprobantes->onFirstPage())
                        <span class="px-3 py-1 rounded border border-gray-200 text-gray-300">&lsaquo;</span>
                    @else
                        <a href="{{ $comprobantes->previousPageUrl() }}" class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition">&lsaquo;</a>
                    @endif

                    @foreach($comprobantes->getUrlRange(max(1, $comprobantes->currentPage() - 2), min($comprobantes->lastPage(), $comprobantes->currentPage() + 2)) as $page => $url)
                        @if($page == $comprobantes->currentPage())
                            <span class="px-3 py-1 rounded bg-blue-600 text-white border border-blue-600">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($comprobantes->hasMorePages())
                        <a href="{{ $comprobantes->nextPageUrl() }}" class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition">&rsaquo;</a>
                    @else
                        <span class="px-3 py-1 rounded border border-gray-200 text-gray-300">&rsaquo;</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
