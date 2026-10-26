@extends('layouts.master')

@section('title', 'Historico de presupuestos')

@section('content')

<div>
    <h3 class="text-[20px] font-bold text-gray-500">Presupuestos</h3>
    <hr class="mx-6">
</div>

<div class="p-4">
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto rounded-2xl shadow">
        <div class="flex items-center px-6 py-3 border rounded gap-4 bg-white">
            <form method="GET" action="{{ route('adm.presupuestos-historico') }}" class="flex items-center gap-2 flex-1">
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Buscar por numero de presupuesto, cliente, email, CUIT o producto..."
                    class="w-full max-w-xl border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                >
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-1.5 rounded-lg transition">
                    <i class="fa-solid fa-magnifying-glass"></i> Buscar
                </button>
                @if(request('q'))
                    <a href="{{ route('adm.presupuestos-historico') }}" class="text-sm text-gray-500 hover:text-red-500 transition">
                        <i class="fa-solid fa-xmark"></i> Limpiar
                    </a>
                @endif
            </form>
        </div>

        <table class="min-w-full border border-gray-200 bg-white">
            <thead class="bg-gray-100">
                <tr class="text-gray-700 text-sm uppercase">
                    <th class="px-4 py-3 text-left">Nro presupuesto</th>
                    <th class="px-4 py-3 text-left">Cliente</th>
                    <th class="px-4 py-3 text-left">Fecha creacion</th>
                    <th class="px-4 py-3 text-left">Vencimiento</th>
                    <th class="px-4 py-3 text-left">Items</th>
                    <th class="px-4 py-3 text-left">Importe</th>
                    <th class="px-4 py-3 text-center">Estado</th>
                    <th class="px-4 py-3 text-center">Acciones</th>
                </tr>
            </thead>

            <tbody class="text-gray-600">
                @forelse($presupuestos as $presupuesto)
                    @php($cliente = $presupuesto->usuario)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-sm">#{{ str_pad($presupuesto->id, 8, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col">
                                <span class="font-medium text-gray-800">
                                    {{ $cliente?->empresa ?: ($cliente?->usuario ?: 'Usuario eliminado') }}
                                </span>
                                @if($cliente?->email)
                                    <span class="text-xs text-gray-400">{{ $cliente->email }}</span>
                                @endif
                                @if($cliente?->cuit)
                                    <span class="text-xs text-gray-400">CUIT: {{ $cliente->cuit }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">{{ $presupuesto->created_at?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">{{ $presupuesto->fecha_vencimiento->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">{{ $presupuesto->items->count() }}</td>
                        <td class="px-4 py-3 font-semibold">$ {{ number_format($presupuesto->total, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="badge {{ $presupuesto->estado === 'vencido' ? 'bg-red-500' : 'bg-green-500' }} text-white px-4 py-2 rounded">
                                {{ $presupuesto->estado_texto }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center space-x-2 font-bold">
                            <a href="{{ route('adm.presupuestos-historico-editor', $presupuesto) }}" class="text-blue-600 hover:underline">Ver</a>
                            <span>|</span>
                            <a href="{{ route('adm.presupuestos-historico-destroy', $presupuesto) }}"
                               onclick="return confirm('Seguro que queres eliminar este presupuesto?')"
                               class="text-red-600 hover:underline">Borrar</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-10 text-gray-400">
                            No se encontraron presupuestos
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($presupuestos->hasPages())
            <div class="flex items-center justify-between px-6 py-3 border-t bg-white text-sm text-gray-600">
                <span>
                    Mostrando {{ $presupuestos->firstItem() }}-{{ $presupuestos->lastItem() }}
                    de {{ $presupuestos->total() }} presupuestos
                </span>
                <div class="flex gap-1">
                    @if($presupuestos->onFirstPage())
                        <span class="px-3 py-1 rounded border border-gray-200 text-gray-300">&lsaquo;</span>
                    @else
                        <a href="{{ $presupuestos->previousPageUrl() }}" class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition">&lsaquo;</a>
                    @endif

                    @foreach($presupuestos->getUrlRange(max(1, $presupuestos->currentPage() - 2), min($presupuestos->lastPage(), $presupuestos->currentPage() + 2)) as $page => $url)
                        @if($page == $presupuestos->currentPage())
                            <span class="px-3 py-1 rounded bg-blue-600 text-white border border-blue-600">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($presupuestos->hasMorePages())
                        <a href="{{ $presupuestos->nextPageUrl() }}" class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition">&rsaquo;</a>
                    @else
                        <span class="px-3 py-1 rounded border border-gray-200 text-gray-300">&rsaquo;</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

@endsection
