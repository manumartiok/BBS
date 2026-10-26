@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')

   {{-- cabezal --}}
    <div>
        <h3 class="text-[20px] font-bold text-gray-500">Catalogos</h3>
        <hr class="mx-6">
    </div>

   {{-- Listado --}}
   <div class="p-4">
        <div class="overflow-x-auto rounded-2xl shadow">
            <div class="flex justify-end px-6 py-3 border rounded">
                <a href="{{ route('adm.catalogo-creador') }}"
                   class="hover:text-gray-400 hover:scale-105 transition-transform duration-300">
                    <i class="fa-solid fa-plus"></i>
                </a>
            </div>

            <table class="min-w-full border border-gray-200 bg-white">
                <thead class="bg-gray-100">
                    <tr class="text-gray-700 text-sm uppercase">
                        <th class="px-4 py-3 text-left">Orden</th>
                        <th class="px-4 py-3 text-center">Foto</th>
                        <th class="px-4 py-3 text-left">Nombre</th>
                        <th class="px-4 py-3 text-center">Activo</th>
                        <th class="px-4 py-3 text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody id="catalogo-tabla" class="text-gray-600">
                    @foreach($items as $item)
                        <tr class="border-t hover:bg-gray-50 cursor-move" data-id="{{ $item->id }}">
                            <td class="px-4 py-3 text-left">
                                {{ $item->orden }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if (!empty($item->foto))
                                    <img src="{{ asset($item->foto) }}" alt="" class="w-[60px] h-[60px] object-cover mx-auto rounded">
                                @endif
                            </td>
                            <td class="px-4 py-3 text-left">
                                {{ $item->nombre }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('adm.catalogo-switch', $item->id) }}"
                                   class="badge {{ $item->active ? 'bg-green-400' : 'bg-red-400' }} text-white px-4 py-2 rounded">
                                    {{ $item->active ? 'Activo' : 'Inactivo' }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('adm.catalogo-editor', $item->id) }}" class="text-blue-600 hover:underline font-bold">Editar</a>
                                <span>|</span>
                                <a href="{{ route('adm.catalogo-destroy', $item->id) }}" class="text-red-600 hover:underline font-bold"
                                   onclick="return confirm('¿Eliminar este registro?')">Borrar</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        activarOrdenDragDrop('#catalogo-tabla', '{{ route('adm.catalogo-reordenar') }}');
    });
</script>

@endsection
