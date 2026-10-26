@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')
<div id="app">
    {{-- Cabezal --}}
    <div>
        <h3 class="text-[20px] font-bold text-gray-500">Info de Pago</h3>
        <hr class="mx-6">
    </div>

    {{-- Editor --}}
    <div class="mx-20 pt-6">
        <div class="border rounded p-4">
            <div>
                <h3 class="text-[18px] font-semibold text-gray-600">
                    Editar Información
                </h3>
            </div>

            <hr class="my-3">

            <form method="POST" action="{{ route('adm.info-update') }}">
                @csrf

                {{-- TEXTO --}}
                <div class="mb-6">
                    <label class="block text-gray-700 font-medium mb-2">
                        Texto
                    </label>
                    <textarea name="texto1" class="ckeditor w-full rounded" rows="6">
                        {!! $info->texto1 ?? '' !!}
                    </textarea>
                </div>

                <button type="submit" class="border rounded px-6 py-3 bg-white">
                    Actualizar
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@include('includes.ckeditor-init')
@endpush
