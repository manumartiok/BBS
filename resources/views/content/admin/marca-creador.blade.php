@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')

   {{-- cabezal --}}
    <div>
        <h3 class="text-[20px] font-bold text-gray-500">Crear Categoría</h3>
        <hr class="mx-6">
    </div>
    
    {{-- Formulario --}}
    <div class="mx-20 pt-6">
        <div class="border border-rounded p-4">
            <div>
                <h3 class="text-[18px] font-semibold text-gray-600">Nueva categoría</h3>
            </div>
            <hr class="my-3">
            
            <form method="POST"
                  action="{{ route('adm.marca-store') }}"
                  enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">
                        Foto (recomendado 295x262) / (2MB máximo)
                    </label>
                    <input type="file" name="foto"
                           class="w-full border border-gray-300 rounded px-4 py-2">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Orden</label>
                    <input type="text" name="orden"
                           class="w-full border border-gray-300 rounded px-4 py-2">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">Nombre</label>
                    <input type="text" name="nombre"
                           class="w-full border border-gray-300 rounded px-4 py-2">
                </div>

                <button type="submit" class="border rounded p-4 bg-white hover:bg-gray-50 transition-colors">
                    Crear
                </button>
            </form>
        </div>            
    </div>

@endsection