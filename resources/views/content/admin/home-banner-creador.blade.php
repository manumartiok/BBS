@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')

   {{-- cabezal --}}
    <div>
        <h3 class="text-[20px] font-bold text-gray-500">Editar banner Home</h3>
        <hr class="mx-6">
    </div>
    
    {{-- Editor --}}
    <div class="mx-20 pt-6" id="app">
        <div class="border border-rounded p-4">
            <div>
                <h3 class="text-[18px] font-semibold  text-gray-600">Editar banner</h3>
            </div>
            <hr class="my-3">
            
            <form method="POST" action="{{ route('adm.home-ban-update') }}" enctype="multipart/form-data" @submit.prevent="handleSubmit">
                @csrf
                <input type="hidden" name="home_banners_id" value="{{ $home_banner->id ?? '' }}">

                <div>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2" for="foto">
                            Foto o Video (Imagen: 1366x626 recomendado | Foto: 2MB máximo | Video: 50MB máximo)
                        </label>

                        @if (!empty($home_banner->foto))
                            <div class="mb-3">
                                @if (($home_banner->tipo_medio ?? 'imagen') === 'video')
                                    <video class="w-full max-w-[1366px] h-[700px] object-cover" controls>
                                        <source src="{{ asset($home_banner->foto) }}" type="video/mp4">
                                        Tu navegador no soporta el tag de video.
                                    </video>
                                @else
                                    <img src="{{ asset($home_banner->foto) }}" alt="Foto" class="w-full max-w-[1366px] h-[626px] object-cover">
                                @endif
                            </div>
                        @endif

                        <input type="file" name="foto" id="foto" accept="image/*,video/*"
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"/>
                        <p class="text-sm text-gray-500 mt-1">
                            Formatos aceptados: JPG, PNG, GIF, WebP, MP4, WebM, MOV
                        </p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2" for="titulo">Orden</label>
                        <input type="text" id="orden" name="orden" placeholder="Orden" value="{{ $home_banner->orden ?? '' }}"
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    {{-- Título y texto (deshabilitados por ahora)
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2" for="titulo">Título</label>
                        <input type="text" id="titulo" name="titulo" placeholder="Título" value="{{ $home_banner->titulo ?? '' }}"
                            class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2" for="texto">Texto</label>
                        <textarea id="texto" name="texto" rows="4" placeholder="Texto"
                            class="ckeditor w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    --}}
                </div>
                <button type="submit" class="border rounded p-4 bg-white hover:bg-gray-100">Actualizar</button>
            </form>
        </div>            
    </div>
    @push('scripts')
   @include('includes.ckeditor-init')
@endpush
@endsection