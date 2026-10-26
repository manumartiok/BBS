@extends('layouts.master')

@section('title', 'Historico de pedidos')

@section('content')

<div>
    <h3 class="text-[20px] font-bold text-gray-500">Pedidos</h3>
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
            <form method="GET" action="{{ route('adm.pedidos-historico') }}" class="flex items-center gap-2 flex-1">
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Buscar por numero de pedido, cliente, email, CUIT o producto..."
                    class="w-full max-w-xl border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                >
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-1.5 rounded-lg transition">
                    <i class="fa-solid fa-magnifying-glass"></i> Buscar
                </button>
                @if(request('q'))
                    <a href="{{ route('adm.pedidos-historico') }}" class="text-sm text-gray-500 hover:text-red-500 transition">
                        <i class="fa-solid fa-xmark"></i> Limpiar
                    </a>
                @endif
            </form>
        </div>

        <table class="min-w-full border border-gray-200 bg-white">
            <thead class="bg-gray-100">
                <tr class="text-gray-700 text-sm uppercase">
                    <th class="px-4 py-3 text-left">Nro pedido</th>
                    <th class="px-4 py-3 text-left">Cliente</th>
                    <th class="px-4 py-3 text-left">Fecha creacion</th>
                    <th class="px-4 py-3 text-left">Fecha entrega</th>
                    <th class="px-4 py-3 text-left">Items</th>
                    <th class="px-4 py-3 text-left">Importe</th>
                    <th class="px-4 py-3 text-center">Estado</th>
                    <th class="px-4 py-3 text-center">Acciones</th>
                </tr>
            </thead>

            <tbody class="text-gray-600">
                @forelse($pedidos as $pedido)
                    @php
                        $cliente = $pedido->cliente ?: $pedido->usuario;
                        $estadoTexto = $pedido->estado_texto;
                        $estadoClass = $pedido->estado_badge;
                    @endphp
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-sm">#{{ str_pad($pedido->id, 8, '0', STR_PAD_LEFT) }}</td>
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
                        <td class="px-4 py-3">{{ $pedido->created_at?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">{{ $pedido->fecha_entrega?->format('d/m/Y') ?: 'Sin definir' }}</td>
                        <td class="px-4 py-3">{{ $pedido->items->count() }}</td>
                        <td class="px-4 py-3 font-semibold">$ {{ number_format($pedido->total, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('adm.pedidos-historico-switch', $pedido) }}"
                               class="badge {{ $estadoClass }} text-white px-4 py-2 rounded hover:opacity-90 transition">
                                {{ $estadoTexto }}
                            </a>
                        </td>
                        <td class="px-4 py-3 text-center space-x-2 font-bold">
                            <a href="{{ route('adm.pedidos-historico-editor', $pedido) }}" class="text-blue-600 hover:underline">Editar</a>
                            <span>|</span>
                            <a href="{{ route('adm.pedidos-historico-destroy', $pedido) }}"
                               onclick="return confirm('Seguro que queres eliminar este pedido?')"
                               class="text-red-600 hover:underline">Borrar</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-10 text-gray-400">
                            No se encontraron pedidos
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($pedidos->hasPages())
            <div class="flex items-center justify-between px-6 py-3 border-t bg-white text-sm text-gray-600">
                <span>
                    Mostrando {{ $pedidos->firstItem() }}-{{ $pedidos->lastItem() }}
                    de {{ $pedidos->total() }} pedidos
                </span>
                <div class="flex gap-1">
                    @if($pedidos->onFirstPage())
                        <span class="px-3 py-1 rounded border border-gray-200 text-gray-300">&lsaquo;</span>
                    @else
                        <a href="{{ $pedidos->previousPageUrl() }}" class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition">&lsaquo;</a>
                    @endif

                    @foreach($pedidos->getUrlRange(max(1, $pedidos->currentPage() - 2), min($pedidos->lastPage(), $pedidos->currentPage() + 2)) as $page => $url)
                        @if($page == $pedidos->currentPage())
                            <span class="px-3 py-1 rounded bg-blue-600 text-white border border-blue-600">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($pedidos->hasMorePages())
                        <a href="{{ $pedidos->nextPageUrl() }}" class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition">&rsaquo;</a>
                    @else
                        <span class="px-3 py-1 rounded border border-gray-200 text-gray-300">&rsaquo;</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

@endsection
