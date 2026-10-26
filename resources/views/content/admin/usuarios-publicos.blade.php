@extends('layouts.master')

@section('title', 'Usuarios de la zona pública')

@section('content')

<div class="flex items-center justify-between px-4 pt-4 pb-2">
    <h3 class="text-[20px] font-bold text-gray-500">Usuarios de la zona pública</h3>
</div>
<hr class="mx-6 mb-2">

<div class="p-4">
    <div class="overflow-x-auto rounded-2xl shadow">

        {{-- Buscador --}}
        <div class="flex items-center px-6 py-3 border rounded gap-4 bg-white">
            <form method="GET" action="{{ route('adm.usuarios-publicos') }}" class="flex items-center gap-2 flex-1">
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Buscar por nombre, email, CUIT, celular, localidad, provincia o CP..."
                    class="w-full max-w-xl border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                >
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-1.5 rounded-lg transition">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                @if(request('q'))
                    <a href="{{ route('adm.usuarios-publicos') }}" class="text-sm text-gray-500 hover:text-red-500 transition">
                        <i class="fa-solid fa-xmark"></i> Limpiar
                    </a>
                @endif
            </form>
        </div>

        <table class="min-w-full border border-gray-200 bg-white">
            <thead class="bg-gray-100">
                <tr class="text-gray-700 text-sm uppercase">
                    <th class="px-4 py-3 text-left">Nombre</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">CUIT / CUIL</th>
                    <th class="px-4 py-3 text-left">Celular</th>
                    <th class="px-4 py-3 text-left">Dirección</th>
                    <th class="px-4 py-3 text-left">Localidad</th>
                    <th class="px-4 py-3 text-left">Provincia</th>
                    <th class="px-4 py-3 text-right">Pedidos</th>
                    <th class="px-4 py-3 text-left">Registro</th>
                </tr>
            </thead>

            <tbody class="text-gray-600">
                @forelse($usuarios as $item)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $item->nombre }}</td>
                        <td class="px-4 py-3">{{ $item->email }}</td>
                        <td class="px-4 py-3">{{ $item->cuit }}</td>
                        <td class="px-4 py-3">{{ $item->celular }}</td>
                        <td class="px-4 py-3">{{ $item->direccion }} <span class="text-xs text-gray-400">({{ $item->codigo_postal }})</span></td>
                        <td class="px-4 py-3">{{ $item->localidad }}</td>
                        <td class="px-4 py-3">{{ $item->provincia }}</td>
                        <td class="px-4 py-3 text-right">
                            @if($item->pedidos_count)
                                <a href="{{ route('adm.pedidos-publicos', ['q' => $item->email]) }}" class="text-blue-600 hover:underline font-semibold">{{ $item->pedidos_count }}</a>
                            @else
                                <span class="text-gray-400">0</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $item->created_at?->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-10 text-gray-400">No se encontraron usuarios</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($usuarios->hasPages())
            <div class="flex items-center justify-between px-6 py-3 border-t bg-white text-sm text-gray-600">
                <span>
                    Mostrando {{ $usuarios->firstItem() }}–{{ $usuarios->lastItem() }}
                    de {{ $usuarios->total() }} usuarios
                </span>
                <div class="flex gap-1">
                    @if($usuarios->onFirstPage())
                        <span class="px-3 py-1 rounded border border-gray-200 text-gray-300">‹</span>
                    @else
                        <a href="{{ $usuarios->previousPageUrl() }}" class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition">‹</a>
                    @endif

                    @foreach($usuarios->getUrlRange(max(1, $usuarios->currentPage() - 2), min($usuarios->lastPage(), $usuarios->currentPage() + 2)) as $page => $url)
                        @if($page == $usuarios->currentPage())
                            <span class="px-3 py-1 rounded bg-blue-600 text-white border border-blue-600">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($usuarios->hasMorePages())
                        <a href="{{ $usuarios->nextPageUrl() }}" class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition">›</a>
                    @else
                        <span class="px-3 py-1 rounded border border-gray-200 text-gray-300">›</span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

@endsection
