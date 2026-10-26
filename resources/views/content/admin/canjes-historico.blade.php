@extends('layouts.master')

@section('title', 'Historico de canjes')

@section('content')

<div>
    <h3 class="text-[20px] font-bold text-gray-500">Canjes</h3>
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
            <form method="GET" action="{{ route('adm.canjes-historico') }}" class="flex items-center gap-2 flex-1">
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Buscar por numero de canje, cliente, email, CUIT, guia o producto..."
                    class="w-full max-w-xl border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                >
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-1.5 rounded-lg transition">
                    <i class="fa-solid fa-magnifying-glass"></i> Buscar
                </button>
                @if(request('q'))
                    <a href="{{ route('adm.canjes-historico') }}" class="text-sm text-gray-500 hover:text-red-500 transition">
                        <i class="fa-solid fa-xmark"></i> Limpiar
                    </a>
                @endif
            </form>
        </div>

        <table class="min-w-full border border-gray-200 bg-white">
            <thead class="bg-gray-100">
                <tr class="text-gray-700 text-sm uppercase">
                    <th class="px-4 py-3 text-left">Nro canje</th>
                    <th class="px-4 py-3 text-left">Cliente</th>
                    <th class="px-4 py-3 text-left">Fecha</th>
                    <th class="px-4 py-3 text-left">Productos</th>
                    <th class="px-4 py-3 text-right">Puntos</th>
                    <th class="px-4 py-3 text-left">Guia</th>
                    <th class="px-4 py-3 text-center">Estado</th>
                    <th class="px-4 py-3 text-center">Acciones</th>
                </tr>
            </thead>

            <tbody class="text-gray-600">
                @forelse($canjes as $canje)
                    @php($cliente = $canje->usuario)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-sm">#{{ str_pad($canje->id, 8, '0', STR_PAD_LEFT) }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col">
                                <span class="font-medium text-gray-800">{{ $canje->nombre }}</span>
                                <span class="text-xs text-gray-400">{{ $canje->email }}</span>
                                @if($cliente?->usuario)
                                    <span class="text-xs text-gray-400">Usuario: {{ $cliente->usuario }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">{{ $canje->created_at?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">
                            @foreach($canje->items as $item)
                                <div class="text-sm">{{ $item->nombre }}</div>
                            @endforeach
                        </td>
                        <td class="px-4 py-3 text-right font-semibold">{{ number_format($canje->puntos, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">{{ $canje->guia_transporte ?: '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('adm.canjes-historico-switch', $canje) }}"
                               class="badge {{ $canje->estado_badge }} text-white px-4 py-2 rounded hover:opacity-90 transition">
                                {{ $canje->estado_texto }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-center space-x-2 font-bold">
                            <a href="{{ route('adm.canjes-historico-editor', $canje) }}" class="text-blue-600 hover:underline">Editar</a>
                            <span>|</span>
                            <a href="{{ route('adm.canjes-historico-destroy', $canje) }}"
                               onclick="return confirm('Seguro que queres eliminar este canje? Los puntos del cliente no se devuelven.')"
                               class="text-red-600 hover:underline">Borrar</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-10 text-gray-400">
                            No se encontraron canjes
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($canjes->hasPages())
            <div class="flex items-center justify-between px-6 py-3 border-t bg-white text-sm text-gray-600">
                <span>
                    Mostrando {{ $canjes->firstItem() }}-{{ $canjes->lastItem() }}
                    de {{ $canjes->total() }} canjes
                </span>
                <div class="flex gap-1">
                    @if($canjes->onFirstPage())
                        <span class="px-3 py-1 rounded border border-gray-200 text-gray-300">&lsaquo;</span>
                    @else
                        <a href="{{ $canjes->previousPageUrl() }}" class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition">&lsaquo;</a>
                    @endif

                    @foreach($canjes->getUrlRange(max(1, $canjes->currentPage() - 2), min($canjes->lastPage(), $canjes->currentPage() + 2)) as $page => $url)
                        @if($page == $canjes->currentPage())
                            <span class="px-3 py-1 rounded bg-blue-600 text-white border border-blue-600">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($canjes->hasMorePages())
                        <a href="{{ $canjes->nextPageUrl() }}" class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition">&rsaquo;</a>
                    @else
                        <span class="px-3 py-1 rounded border border-gray-200 text-gray-300">&rsaquo;</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

@endsection
