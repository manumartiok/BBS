<div class="mb-4">
    <label class="block text-gray-700 font-medium mb-2">Orden</label>
    <input type="text" name="orden" value="{{ old('orden', $item->orden) }}"
           class="w-full border border-gray-300 rounded px-4 py-2">
</div>

{{-- Nombre --}}
<div class="mb-4">
    <label class="block text-gray-700 font-medium mb-2">Nombre</label>
    <input type="text" name="nombre" value="{{ old('nombre', $item->nombre) }}"
           class="w-full border border-gray-300 rounded px-4 py-2">
</div>

{{-- Foto --}}
<div class="mb-4 campo-archivo">
    <label class="block text-gray-700 font-medium mb-2">Foto (2MB máximo)</label>
    <img src="{{ $item->foto }}" alt=""
         class="vista-previa mb-3 max-w-[300px] max-h-[200px] object-cover rounded {{ empty($item->foto) ? 'hidden' : '' }}">
    <input type="file" name="foto" accept="image/*" onchange="previsualizarImagen(this)"
           class="w-full border border-gray-300 rounded px-4 py-2">
    @if (!empty($item->foto))
        <label class="inline-flex items-center gap-2 mt-2 text-sm text-gray-600">
            <input type="checkbox" name="quitar_foto" value="1"> Quitar Foto
        </label>
    @endif
</div>

{{-- Archivo --}}
<div class="mb-4 campo-archivo">
    <label class="block text-gray-700 font-medium mb-2">Archivo (10MB máximo)</label>
    @if (!empty($item->archivo))
        <a href="{{ $item->archivo }}" target="_blank" class="block mb-2 text-blue-600 hover:underline">Ver archivo actual</a>
    @endif
    <input type="file" name="archivo"
           class="w-full border border-gray-300 rounded px-4 py-2">
    @if (!empty($item->archivo))
        <label class="inline-flex items-center gap-2 mt-2 text-sm text-gray-600">
            <input type="checkbox" name="quitar_archivo" value="1"> Quitar Archivo
        </label>
    @endif
</div>
