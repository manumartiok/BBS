@extends('layouts.master')

@section('title', 'Home')

@section('content')

{{-- Cabezal --}}
<div>
    <h3 class="text-[20px] font-bold text-gray-500">Home</h3>
    <hr class="mx-6">
</div>

{{-- Editor --}}
<div class="mx-20 pt-6">
    <div class="border rounded p-4">
        <div>
            <h3 class="text-[18px] font-semibold text-gray-600">Editar Home</h3>
        </div>

        <hr class="my-3">

        <form method="POST"
              action="{{ route('adm.home-update') }}">
            @csrf

            {{-- Título 1 --}}
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2" for="titulo1">
                    Título 1 (Lo más nuevo)
                </label>
                <input type="text"
                       id="titulo1"
                       name="titulo1"
                       value="{{ old('titulo1', $home->titulo1) }}"
                       class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">

                @error('titulo1')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Título 2 --}}
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2" for="titulo2">
                    Título 2 (Nuestras ofertas)
                </label>
                <input type="text"
                       id="titulo2"
                       name="titulo2"
                       value="{{ old('titulo2', $home->titulo2) }}"
                       class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">

                @error('titulo2')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Título 3 --}}
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2" for="titulo3">
                    Título 3 (Categorías)
                </label>
                <input type="text"
                       id="titulo3"
                       name="titulo3"
                       value="{{ old('titulo3', $home->titulo3) }}"
                       class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">

                @error('titulo3')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Título 4 --}}
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2" for="titulo4">
                    Título 4 (Novedades destacadas)
                </label>
                <input type="text"
                       id="titulo4"
                       name="titulo4"
                       value="{{ old('titulo4', $home->titulo4) }}"
                       class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">

                @error('titulo4')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Título 5 --}}
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2" for="titulo5">
                    Título 5 (Descubre más)
                </label>
                <input type="text"
                       id="titulo5"
                       name="titulo5"
                       value="{{ old('titulo5', $home->titulo5) }}"
                       class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">

                @error('titulo5')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Título 6 --}}
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2" for="titulo6">
                    Título 6 (Lo más vendido)
                </label>
                <input type="text"
                       id="titulo6"
                       name="titulo6"
                       value="{{ old('titulo6', $home->titulo6) }}"
                       class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">

                @error('titulo6')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-4">
                <button type="submit"
                        class="border rounded px-6 py-3 bg-white transition">
                    Actualizar
                </button>
            </div>

        </form>
    </div>
</div>

@endsection
