{{-- Categoría --}}
<div class="mb-4">
    <label class="block text-gray-700 font-medium mb-2">Categoría</label>
    <select name="marca_id" class="w-full border border-gray-300 rounded px-4 py-2">
        <option value="">-- Seleccionar --</option>
        @foreach($marcas as $marca)
            <option value="{{ $marca->id }}" @selected(old('marca_id', $producto->marca_id) == $marca->id)>{{ $marca->nombre }}</option>
        @endforeach
    </select>
</div>

{{-- Foto --}}
<div class="mb-4 campo-archivo">
    <label class="block text-gray-700 font-medium mb-2">Foto (recomendado 500x500) / (2MB máximo)</label>
    <img src="{{ $producto->foto }}" alt=""
         class="vista-previa mb-3 w-full max-w-[500px] h-[500px] object-cover rounded {{ empty($producto->foto) ? 'hidden' : '' }}">
    <input type="file" name="foto" accept="image/*" onchange="previsualizarImagen(this)"
           class="w-full border border-gray-300 rounded px-4 py-2">
    @if (!empty($producto->foto))
        <label class="inline-flex items-center gap-2 mt-2 text-sm text-gray-600">
            <input type="checkbox" name="quitar_foto" value="1"> Quitar Foto
        </label>
    @endif
</div>

{{-- Orden --}}
<div class="mb-4">
    <label class="block text-gray-700 font-medium mb-2">Orden</label>
    <input type="text" name="orden" value="{{ old('orden', $producto->orden) }}"
           class="w-full border border-gray-300 rounded px-4 py-2">
</div>

{{-- Nombre --}}
<div class="mb-4">
    <label class="block text-gray-700 font-medium mb-2">Nombre</label>
    <input type="text" name="nombre" value="{{ old('nombre', $producto->nombre) }}"
           class="w-full border border-gray-300 rounded px-4 py-2">
</div>

{{-- Código --}}
<div class="mb-4">
    <label class="block text-gray-700 font-medium mb-2">Código</label>
    <input type="text" name="codigo" value="{{ old('codigo', $producto->codigo) }}"
           class="w-full border border-gray-300 rounded px-4 py-2">
</div>

{{-- Stock --}}
<div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <label class="block text-gray-700 font-medium mb-2">Stock físico</label>
        <input type="text" name="stock_fisico" value="{{ old('stock_fisico', $producto->stock_fisico) }}"
               class="w-full border border-gray-300 rounded px-4 py-2">
    </div>
    <div>
        <label class="block text-gray-700 font-medium mb-2">Stock en tránsito</label>
        <input type="text" name="stock_transito" value="{{ old('stock_transito', $producto->stock_transito) }}"
               class="w-full border border-gray-300 rounded px-4 py-2">
    </div>
</div>

{{-- Descripción --}}
<div class="mb-4">
    <label class="block text-gray-700 font-medium mb-2">Descripción</label>
    <textarea name="descripcion" rows="6" class="ckeditor w-full border border-gray-300 rounded px-4 py-2">{{ old('descripcion', $producto->descripcion) }}</textarea>
</div>

{{-- Precio --}}
<div class="mb-4">
    <label class="block text-gray-700 font-medium mb-2">Precio</label>
    <input type="number" step="0.01" name="precio" value="{{ old('precio', $producto->precio) }}"
           class="w-full border border-gray-300 rounded px-4 py-2">
</div>

{{-- IVA --}}
<div class="mb-4">
    <label class="block text-gray-700 font-medium mb-2">IVA (%)</label>
    <input type="number" step="0.01" min="0" max="100" name="iva" value="{{ old('iva', $producto->iva) }}"
           class="w-full border border-gray-300 rounded px-4 py-2">
</div>

{{-- Puntos --}}
<div class="mb-4">
    <label class="block text-gray-700 font-medium mb-2">Puntos</label>
    <input type="number" step="1" min="0" inputmode="numeric" name="puntos" value="{{ old('puntos', $producto->puntos) }}"
           onkeydown="return !['.', ',', 'e', 'E', '+', '-'].includes(event.key)"
           class="w-full border border-gray-300 rounded px-4 py-2">
</div>

{{-- Puntos por compra --}}
<div class="mb-4">
    <label class="block text-gray-700 font-medium mb-2">Puntos por compra</label>
    <input type="number" step="1" min="0" inputmode="numeric" name="puntos_compra" value="{{ old('puntos_compra', $producto->puntos_compra) }}"
           onkeydown="return !['.', ',', 'e', 'E', '+', '-'].includes(event.key)"
           class="w-full border border-gray-300 rounded px-4 py-2">
</div>

{{-- Características --}}
<div class="mb-4">
    <label class="block text-gray-700 font-medium mb-2">Características</label>
    <div id="lista-caracteristicas" class="space-y-3" data-siguiente="{{ count($producto->caracteristicas ?? []) }}">
        @foreach (array_values($producto->caracteristicas ?? []) as $i => $fila)
            <div class="fila-lista border rounded p-3 bg-gray-50">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Nombre</label>
                        <input type="text" name="caracteristicas[{{ $i }}][nombre]" value="{{ $fila['nombre'] ?? '' }}" class="w-full border border-gray-300 rounded px-4 py-2">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Valor</label>
                        <input type="text" name="caracteristicas[{{ $i }}][valor]" value="{{ $fila['valor'] ?? '' }}" class="w-full border border-gray-300 rounded px-4 py-2">
                    </div>
                </div>
                <button type="button" onclick="quitarFila(this)" class="mt-2 text-sm text-red-600 hover:underline">Quitar fila</button>
            </div>
        @endforeach
    </div>

    <template id="lista-caracteristicas-template">
        <div class="fila-lista border rounded p-3 bg-gray-50">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Nombre</label>
                    <input type="text" name="caracteristicas[__i__][nombre]" value="" class="w-full border border-gray-300 rounded px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Valor</label>
                    <input type="text" name="caracteristicas[__i__][valor]" value="" class="w-full border border-gray-300 rounded px-4 py-2">
                </div>
            </div>
            <button type="button" onclick="quitarFila(this)" class="mt-2 text-sm text-red-600 hover:underline">Quitar fila</button>
        </div>
    </template>

    <button type="button" onclick="agregarFila('lista-caracteristicas')"
            class="mt-3 border rounded px-4 py-2 bg-white hover:bg-gray-50">
        <i class="fa-solid fa-plus mr-1"></i> Agregar fila
    </button>
</div>

{{-- Destacado --}}
<div class="mb-4">
    <input type="hidden" name="destacado" value="0">
    <label class="inline-flex items-center gap-2 text-gray-700 font-medium">
        <input type="checkbox" name="destacado" value="1" @checked(old('destacado', $producto->destacado ?? true))> Destacado
    </label>
</div>

{{-- Oferta --}}
<div class="mb-4">
    <input type="hidden" name="oferta" value="0">
    <label class="inline-flex items-center gap-2 text-gray-700 font-medium">
        <input type="checkbox" name="oferta" value="1" @checked(old('oferta', $producto->oferta ?? false))> Oferta
    </label>
</div>

{{-- Más vendido --}}
<div class="mb-4">
    <input type="hidden" name="mas_vendido" value="0">
    <label class="inline-flex items-center gap-2 text-gray-700 font-medium">
        <input type="checkbox" name="mas_vendido" value="1" @checked(old('mas_vendido', $producto->mas_vendido ?? false))> Más vendido
    </label>
</div>

{{-- Descubre más --}}
<div class="mb-4">
    <input type="hidden" name="descubre_mas" value="0">
    <label class="inline-flex items-center gap-2 text-gray-700 font-medium">
        <input type="checkbox" name="descubre_mas" value="1" @checked(old('descubre_mas', $producto->descubre_mas ?? false))> Descubre más
    </label>
</div>
