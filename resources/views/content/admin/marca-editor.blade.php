@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')

   {{-- cabezal --}}
    <div>
        <h3 class="text-[20px] font-bold text-gray-500">Editar Categoría</h3>
        <hr class="mx-6">
    </div>
    
    {{-- Formulario --}}
    <div class="mx-20 pt-6" id="app">
        <div class="border border-rounded p-4">
            <div>
                <h3 class="text-[18px] font-semibold text-gray-600">Editar categoría</h3>
            </div>
            <hr class="my-3">
            
            <form method="POST"
                  action="{{ route('adm.marca-update') }}"
                  enctype="multipart/form-data"
                  @submit.prevent="handleSubmit">
                @csrf

                <input type="hidden" name="marca_id" value="{{ $marca->id ?? '' }}">

                {{-- Foto --}}
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">
                        Foto (recomendado 295x262) / (2MB máximo)
                    </label>

                    @if (!empty($marca->foto))
                        <img :src="foto.foto || '{{ $marca->foto }}'"
                             class="mb-3 w-full max-w-[295px] h-[262px] object-cover rounded">
                    @else
                        <img :src="foto.foto || ''"
                             v-if="foto.foto"
                             class="mb-3 w-full max-w-[295px] h-[262px] object-cover rounded">
                    @endif

                    <input type="file" name="foto" @change="subirFoto"
                           class="w-full border border-gray-300 rounded px-4 py-2">
                </div>

                {{-- Orden --}}
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Orden</label>
                    <input type="text" name="orden" value="{{ $marca->orden ?? '' }}"
                           class="w-full border border-gray-300 rounded px-4 py-2">
                </div>

                {{-- Nombre de la categoría --}}
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Nombre</label>
                    <input type="text" name="nombre" value="{{ $marca->nombre ?? '' }}"
                           class="w-full border border-gray-300 rounded px-4 py-2">
                </div>

                <button type="submit" class="border rounded p-4 bg-white hover:bg-gray-50 transition-colors">
                    Actualizar
                </button>
            </form>
        </div>            
    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (document.getElementById('app')) {
            new Vue({
                el: '#app',
                data: {
                    foto: {
                        foto: ''
                    }
                },
                methods: {
                    subirFoto: function(event) {
                        let file = event.target.files[0];
                        if (file) {
                            let reader = new FileReader();
                            reader.onload = (e) => {
                                this.foto.foto = e.target.result;
                            };
                            reader.readAsDataURL(file);
                        }
                    },
                    handleSubmit: function() {
                        this.$el.querySelector('form').submit();
                    }
                }
            });
        }
    });
</script>
@endpush