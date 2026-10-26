@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')

   {{-- cabezal --}}
    <div>
        <h3 class="text-[20px] font-bold text-gray-500">Crear Catalogo</h3>
        <hr class="mx-6">
    </div>

    {{-- Formulario --}}
    <div class="mx-20 pt-6">
        <div class="border border-rounded p-4">
            <div>
                <h3 class="text-[18px] font-semibold text-gray-600">Nuevo registro</h3>
            </div>
            <hr class="my-3">

            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('adm.catalogo-store') }}" enctype="multipart/form-data">
                @csrf

                @include('content.admin.partials.catalogo-campos')

                <button type="submit" class="border rounded p-4 bg-white hover:bg-gray-50 transition-colors">Crear</button>
            </form>
        </div>
    </div>

    @include('includes.modulo-scripts')
@endsection
