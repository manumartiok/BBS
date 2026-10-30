@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')

   {{-- cabezal --}}
    <div>
        <h3 class="text-[20px] font-bold text-gray-500">Editar Producto</h3>
        <hr class="mx-6">
    </div>

    {{-- Formulario --}}
    <div class="mx-20 pt-6">
        <div class="border border-rounded p-4">
            <div>
                <h3 class="text-[18px] font-semibold text-gray-600">Editar producto</h3>
            </div>
            <hr class="my-3">

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST"
                  action="{{ route('adm.producto-update') }}"
                  enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="producto_id" value="{{ $producto->id ?? '' }}">

                @include('content.admin.partials.producto-campos')

                <button type="submit" class="border rounded p-4 bg-white hover:bg-gray-50 transition-colors">
                    Actualizar
                </button>
            </form>
        </div>
    </div>

    <hr class="my-10">

    {{-- GALERÍA DE FOTOS --}}
    <div class="mx-20">
        <form id="form-galeria" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="productos_id" value="{{ $producto->id }}">

            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Agregar fotos para la galería (recomendado 500x500)</label>
                <input type="file" name="fotos[]" id="fotos" multiple accept="image/*" class="w-full border border-gray-300 rounded px-4 py-2" />
                <p class="text-sm text-gray-500 mt-1" id="file-count">No hay archivos seleccionados</p>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Orden inicial</label>
                <input type="number" name="orden" value="0" class="w-full border border-gray-300 rounded px-4 py-2">
                <p class="text-sm text-gray-500 mt-1">Las fotos se agregarán con orden consecutivo</p>
            </div>

            <button type="submit" id="btn-subir-fotos" class="border rounded p-4 bg-white hover:bg-gray-50">Agregar Fotos</button>
            <span id="loading-galeria" class="ml-3 text-gray-600 hidden">Subiendo...</span>
        </form>

        <div class="p-4 mt-6">
            <div class="overflow-x-auto rounded-2xl shadow">
                <table class="min-w-full border border-gray-200 bg-white">
                    <thead class="bg-gray-100">
                        <tr class="text-gray-700 text-sm uppercase">
                            <th class="px-4 py-3 text-left">Orden</th>
                            <th class="px-4 py-3 text-center">Foto</th>
                            <th class="px-4 py-3 text-center">Activo</th>
                            <th class="px-4 py-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="galeria-fotos" class="text-gray-600">
                        @foreach($producto->fotos as $foto)
                            <tr class="border-t hover:bg-gray-50 cursor-move" data-id="{{ $foto->id }}">
                                <td class="px-4 py-3">{{ $foto->orden }}</td>
                                <td class="px-4 py-3 text-center">
                                    <img src="{{ asset($foto->foto) }}" alt="" class="w-[120px] h-[94px] object-cover mx-auto rounded">
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($foto->active)
                                        <a href="{{ route('adm.producto-fotos-switch', $foto->id) }}" class="badge bg-green-400 text-white px-4 py-2 rounded">Activo</a>
                                    @else
                                        <a href="{{ route('adm.producto-fotos-switch', $foto->id) }}" class="badge bg-red-400 text-white px-4 py-2 rounded">Inactivo</a>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('adm.producto-fotos-destroy', $foto->id) }}" class="text-red-600 hover:underline btn-borrar-foto">Borrar</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('includes.modulo-scripts')
    @include('includes.ckeditor-init')
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const formGaleria = document.getElementById('form-galeria');
    if (!formGaleria) return;

    const btnSubmit = document.getElementById('btn-subir-fotos');
    const loading = document.getElementById('loading-galeria');
    const fileCount = document.getElementById('file-count');

    document.getElementById('fotos')?.addEventListener('change', function () {
        const count = this.files.length;
        fileCount.textContent = count === 0 ? 'No hay archivos seleccionados' : (count === 1 ? '1 archivo seleccionado' : count + ' archivos seleccionados');
    });

    formGaleria.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        btnSubmit.disabled = true;
        loading.classList.remove('hidden');

        fetch('{{ route('adm.producto-fotos-store') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // recargar la página para actualizar la galería
                location.reload();
            } else {
                alert('Error al subir las fotos');
            }
        })
        .catch(() => alert('Error al subir las fotos'))
        .finally(() => { btnSubmit.disabled = false; loading.classList.add('hidden'); });
    });

    // borrar foto
    document.querySelectorAll('.btn-borrar-foto').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            if (!confirm('¿Deseas eliminar esta foto?')) return;
            const url = this.getAttribute('href');
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.json())
                .then(data => {
                    if (data.success) location.reload();
                })
                .catch(() => alert('Error al eliminar la foto'));
        });
    });

    // activar reorden
    activarOrdenDragDrop('#galeria-fotos', '{{ route('adm.producto-fotos-reordenar') }}');
});
</script>
@endpush
