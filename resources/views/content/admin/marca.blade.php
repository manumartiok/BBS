@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')

   {{-- cabezal --}}
    <div>
        <h3 class="text-[20px] font-bold text-gray-500">Categorías</h3>
        <hr class="mx-6">
    </div>

   {{-- Listado --}}
   <div class="p-4">
        <div class="overflow-x-auto rounded-2xl shadow">
            <div class="flex justify-end px-6 py-3 border rounded">
                <a href="{{ route('adm.marca-creador') }}"
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
                        <th class="px-4 py-3 text-center">Destacado</th>
                    </tr>
                </thead>

                <tbody id="marca-tabla" class="text-gray-600">
                    @foreach($marcas as $item)
                        <tr class="border-t hover:bg-gray-50 cursor-move" data-id="{{ $item->id }}">
                            <td class="px-4 py-3">{{ $item->orden }}</td>
                            <td class="px-4 py-3 text-center">
                                @if (!empty($item->foto))
                                     <img src="{{ asset($item->foto) }}"
                                         alt="{{ $item->nombre }}"
                                         class="w-[60px] h-[60px] object-cover mx-auto rounded">
                                @endif
                            </td>
                               <td class="px-4 py-3 w-64 max-w-64 truncate">{{ $item->nombre }}</td>
                            <td class="px-4 py-3 text-center">
                                @if ($item->active)
                                    <a href="{{ route('adm.marca-switch', $item->id) }}"
                                       class="badge bg-green-400 text-white px-4 py-2 rounded">
                                        Activo
                                    </a>
                                @else
                                    <a href="{{ route('adm.marca-switch', $item->id) }}"
                                       class="badge bg-red-400 text-white px-4 py-2 rounded">
                                        Inactivo
                                    </a>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center space-x-2 font-bold">
                                <a href="{{ route('adm.marca-editor', $item->id) }}"
                                   class="text-blue-600 hover:underline">Editar</a>
                                <span>|</span>
                                <a href="{{ route('adm.marca-destroy', $item->id) }}"
                                   class="text-red-600 hover:underline">Borrar</a>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($item->destacado)
                                    <a href="{{ route('adm.marca-destacado', $item->id) }}"
                                       class="badge bg-green-400 text-white px-4 py-2 rounded">
                                        Destacado
                                    </a>
                                @else
                                    <a href="{{ route('adm.marca-destacado', $item->id) }}"
                                       class="badge bg-red-400 text-white px-4 py-2 rounded">
                                        No destacado
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        activarOrdenDragDrop('#marca-tabla', '{{ route('adm.marca-reordenar') }}');
    });
</script>

@endsection