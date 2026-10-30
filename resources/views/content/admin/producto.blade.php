@extends('layouts.master')

@section('title', 'Productos')

@section('content')

{{-- Cabezal --}}
<div class="flex items-center justify-between px-4 pt-4 pb-2">
    <h3 class="text-[20px] font-bold text-gray-500">Productos</h3>
    <div class="flex gap-2">
        <button onclick="document.getElementById('modalFotosMasivas').classList.remove('hidden')"
            class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow transition">
            <i class="fa-solid fa-images mr-1"></i> Fotos Masivas
        </button>
        {{--
        <button onclick="document.getElementById('modalImport').classList.remove('hidden')"
            class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-4 py-2 rounded-lg shadow transition">
            Importar CSV/Excel
        </button>
        --}}
    </div>
</div>
<hr class="mx-6 mb-2">


{{-- Modal Import --}}
<div id="modalImport" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
        <h2 class="text-lg font-bold text-gray-700 mb-1">Importar productos</h2>
        <p class="text-sm text-gray-500 mb-4">
            Columnas esperadas: <em>nombre, codigo, stock_fisico, stock_transito, descripcion, marca, precio, puntos, puntos_compra</em>. Si ya existe un producto con ese nombre, se actualiza.
        </p>
        <form action="{{ route('adm.producto-import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-600 mb-1">Archivo (.csv, .xls, .xlsx, .ods)</label>
                <input type="file" name="archivo" accept=".csv,.xls,.xlsx,.ods" class="w-full" required>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('modalImport').classList.add('hidden')"
                    class="px-4 py-2 rounded border border-gray-300 hover:bg-gray-100">Cancelar</button>
                <button type="submit" class="px-4 py-2 rounded bg-green-600 text-white">Importar</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Fotos Masivas --}}
<div id="modalFotosMasivas" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
        <h2 class="text-lg font-bold text-gray-700 mb-1">Asignación masiva de fotos</h2>
        <p class="text-sm text-gray-500 mb-1">
            Seleccioná las fotos que querés subir. Cada foto se asignará automáticamente al producto cuyo
            <strong>nombre</strong> coincida con el <strong>nombre del archivo</strong> (sin extensión).
        </p>
        <p class="text-xs text-gray-400 mb-4">
            Ejemplo: <em>Taladro 500.jpg</em> → producto con nombre <em>Taladro 500</em>.
            Los productos sin foto coincidente recibirán la imagen por defecto.
        </p>

        <form action="{{ route('adm.producto-fotos-masivas') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-600 mb-1">
                    Fotos (.jpg, .jpeg, .png, .webp, .gif)
                </label>
                <input
                    type="file"
                    name="fotos[]"
                    accept=".jpg,.jpeg,.png,.webp,.gif"
                    multiple
                    required
                    class="w-full text-sm text-gray-500
                        file:mr-3 file:py-1.5 file:px-4
                        file:rounded-lg file:border-0
                        file:text-sm file:font-semibold
                        file:bg-blue-50 file:text-blue-700
                        hover:file:bg-blue-100"
                >
            </div>

            {{-- Resultados (se poblan tras el redirect con session) --}}
            @if(session('fotos_masivas'))
                @php $res = session('fotos_masivas'); @endphp
                <div class="mb-4 text-sm rounded-lg border p-3
                    {{ $res['errores'] > 0 ? 'bg-yellow-50 border-yellow-300 text-yellow-800' : 'bg-green-50 border-green-300 text-green-800' }}">
                    <p class="font-semibold mb-1">Resultado de la última importación:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        <li>{{ $res['asignadas'] }} foto(s) asignadas correctamente</li>
                        <li>{{ $res['default'] }} producto(s) sin coincidencia recibieron la foto por defecto</li>
                        @if($res['sin_producto'] > 0)
                            <li>{{ $res['sin_producto'] }} foto(s) sin producto coincidente (ignoradas)</li>
                        @endif
                        @if($res['errores'] > 0)
                            <li>{{ $res['errores'] }} error(es) durante el proceso</li>
                        @endif
                    </ul>
                </div>
            @endif

            <div class="flex justify-end gap-3">
                <button
                    type="button"
                    onclick="document.getElementById('modalFotosMasivas').classList.add('hidden')"
                    class="px-4 py-2 rounded border border-gray-300 hover:bg-gray-100 text-sm">
                    Cancelar
                </button>
                <button
                    type="submit"
                    class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">
                    <i class="fa-solid fa-upload mr-1"></i> Asignar fotos
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Abrir modal automáticamente si hay resultado de fotos masivas --}}
@if(session('fotos_masivas'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('modalFotosMasivas').classList.remove('hidden');
    });
</script>
@endif


{{-- Listado --}}
<div class="p-4">
    <div class="overflow-x-auto rounded-2xl shadow">
        <div class="flex items-center justify-between px-6 py-3 border rounded gap-4">
            {{-- Buscador --}}
            <form method="GET" action="{{ route('adm.producto') }}" class="flex items-center gap-2 flex-1 flex-wrap">
                <input name="q" value="{{ request('q') }}" placeholder="Buscar por nombre, código o categoría"
                    class="w-full max-w-xs border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">

                <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded">Buscar</button>
            </form>

            <a href="{{ route('adm.producto-creador') }}"
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
                    <th class="px-4 py-3 text-left">Categoría</th>
                    <th class="px-4 py-3 text-left">Código</th>
                    <th class="px-4 py-3 text-left">Stock físico</th>
                    <th class="px-4 py-3 text-left">Precio</th>
                    <th class="px-4 py-3 text-left">IVA</th>
                    <th class="px-4 py-3 text-center">Activo</th>
                    <th class="px-4 py-3 text-center">Destacado</th>
                    <th class="px-4 py-3 text-center">Oferta</th>
                    <th class="px-4 py-3 text-center">Más vendido</th>
                    <th class="px-4 py-3 text-center">Descubre más</th>
                    <th class="px-4 py-3 text-center">Acciones</th>
                </tr>
            </thead>
            <tbody id="producto-tabla" class="text-gray-600">
                @foreach($productos as $item)
                    <tr class="border-t hover:bg-gray-50 @if(!$productos->hasPages()) cursor-move @endif" data-id="{{ $item->id }}">
                        <td class="px-4 py-3">{{ $item->orden }}</td>
                        <td class="px-4 py-3 text-center">
                            <img src="{{ $item->foto_url }}" alt="{{ $item->nombre }}" class="w-[60px] h-[60px] object-cover mx-auto rounded">
                        </td>
                        <td class="px-4 py-3 w-64 max-w-64 truncate">{{ $item->nombre }}</td>
                        <td class="px-4 py-3">{{ $item->marca->nombre ?? '' }}</td>
                        <td class="px-4 py-3">{{ $item->codigo }}</td>
                        <td class="px-4 py-3">{{ $item->stock_fisico }}</td>
                        <td class="px-4 py-3">{{ $item->precio }}</td>
                        <td class="px-4 py-3">{{ $item->iva !== null ? rtrim(rtrim($item->iva, '0'), '.') . '%' : '' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if ($item->active)
                                <a href="{{ route('adm.producto-switch', $item->id) }}" class="badge bg-green-400 text-white px-4 py-2 rounded">Activo</a>
                            @else
                                <a href="{{ route('adm.producto-switch', $item->id) }}" class="badge bg-red-400 text-white px-4 py-2 rounded">Inactivo</a>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($item->destacado)
                                <a href="{{ route('adm.producto-destacado', $item->id) }}" class="badge bg-green-400 text-white px-4 py-2 rounded">Destacado</a>
                            @else
                                <a href="{{ route('adm.producto-destacado', $item->id) }}" class="badge bg-red-400 text-white px-4 py-2 rounded">No destacado</a>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($item->oferta)
                                <a href="{{ route('adm.producto-oferta', $item->id) }}" class="badge bg-green-400 text-white px-4 py-2 rounded">En oferta</a>
                            @else
                                <a href="{{ route('adm.producto-oferta', $item->id) }}" class="badge bg-red-400 text-white px-4 py-2 rounded">Sin oferta</a>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($item->mas_vendido)
                                <a href="{{ route('adm.producto-mas-vendido', $item->id) }}" class="badge bg-green-400 text-white px-4 py-2 rounded">Más vendido</a>
                            @else
                                <a href="{{ route('adm.producto-mas-vendido', $item->id) }}" class="badge bg-red-400 text-white px-4 py-2 rounded">No vendido</a>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($item->descubre_mas)
                                <a href="{{ route('adm.producto-descubre-mas', $item->id) }}" class="badge bg-green-400 text-white px-4 py-2 rounded">Descubre más</a>
                            @else
                                <a href="{{ route('adm.producto-descubre-mas', $item->id) }}" class="badge bg-red-400 text-white px-4 py-2 rounded">No descubre</a>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center space-x-2 font-bold">
                            <a href="{{ route('adm.producto-editor', $item->id) }}" class="text-blue-600 hover:underline">Editar</a>
                            <span>|</span>
                            <a href="{{ route('adm.producto-destroy', $item->id) }}" class="text-red-600 hover:underline">Borrar</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Paginado --}}
        @if($productos->hasPages())
            <div class="flex items-center justify-between px-6 py-3 border-t bg-white text-sm text-gray-600">
                <span>
                    Mostrando {{ $productos->firstItem() }}–{{ $productos->lastItem() }} de {{ $productos->total() }} productos
                </span>
                <div class="flex gap-1">
                    {{-- Anterior --}}
                    @if($productos->onFirstPage())
                        <span class="px-3 py-1 rounded border border-gray-200 text-gray-300">‹</span>
                    @else
                        <a href="{{ $productos->previousPageUrl() }}" class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition">‹</a>
                    @endif

                    {{-- Páginas --}}
                    @foreach($productos->getUrlRange(max(1, $productos->currentPage()-2), min($productos->lastPage(), $productos->currentPage()+2)) as $page => $url)
                        @if($page == $productos->currentPage())
                            <span class="px-3 py-1 rounded bg-blue-600 text-white border border-blue-600">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition">{{ $page }}</a>
                        @endif
                    @endforeach

                    {{-- Siguiente --}}
                    @if($productos->hasMorePages())
                        <a href="{{ $productos->nextPageUrl() }}" class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition">›</a>
                    @else
                        <span class="px-3 py-1 rounded border border-gray-200 text-gray-300">›</span>
                    @endif
                </div>
            </div>
        @endif

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    @if(!$productos->hasPages())
        activarOrdenDragDrop('#producto-tabla', '{{ route('adm.producto-reordenar') }}');
    @else
        // Deshabilitar cursor de arrastre visualmente
        document.querySelectorAll('#producto-tabla tr').forEach(function(tr) {
            tr.classList.remove('cursor-move');
            tr.style.cursor = 'default';
        });
    @endif
});
</script>

@endsection
