@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')

   {{-- cabezal --}}
    <div>
        <h3 class="text-[20px] font-bold text-gray-500">Crear Producto</h3>
        <hr class="mx-6">
    </div>

    {{-- Formulario --}}
    <div class="mx-20 pt-6">
        <div class="border border-rounded p-4">
            <div>
                <h3 class="text-[18px] font-semibold text-gray-600">Nuevo producto</h3>
            </div>
            <hr class="my-3">

            <form method="POST"
                  action="{{ route('adm.producto-store') }}"
                  enctype="multipart/form-data">
                @csrf

                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-100 border border-red-300 text-red-700 rounded">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-4 p-3 bg-green-100 border border-green-300 text-green-700 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @include('content.admin.partials.producto-campos')

                <button type="submit" class="border rounded p-4 bg-white hover:bg-gray-50 transition-colors">
                    Crear
                </button>
            </form>
        </div>
    </div>

    <hr class="my-10">

    {{-- GALERÍA: indicar guardar producto primero --}}
    <div class="mx-20">
        <div class="border border-rounded p-4 bg-white">
            <h3 class="text-[18px] font-semibold text-gray-600 mb-3">Galería de fotos</h3>
            <p class="text-sm text-gray-500">Guarda el producto primero para poder agregar fotos a la galería.</p>
        </div>
    </div>

    @include('includes.modulo-scripts')
    @include('includes.ckeditor-init')
@endsection
