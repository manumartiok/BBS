@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')

   {{-- cabezal --}}
    <div>
        <h3 class="text-[20px] font-bold text-gray-500">Editar Contacto</h3>
        <hr class="mx-6">
    </div>

    {{-- Editor --}}
    <div class="mx-20 pt-6" id="app">
        <div class="border border-rounded p-4">
            <div>
                <h3 class="text-[18px] font-semibold text-gray-600">Editar contacto</h3>
            </div>
            <hr class="my-3">

            <form method="POST"
                  action="{{ route('adm.contacto.update') }}">
                @csrf

                <input type="hidden"
                       name="contactos_id"
                       value="{{ $contacto->id ?? '' }}">

                 {{-- CAMPOS SUPERIORES (1 COLUMNA) --}}
                <div>

                    {{-- Mapa --}}
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Mapa (iframe)</label>
                        <input type="text" name="ubicacion" value="{{ $contacto->ubicacion ?? '' }}"
                            class="w-full border border-gray-300 rounded px-4 py-2">
                    </div>


                </div>

                {{-- DATOS DE CONTACTO --}}
                <div class="grid grid-cols-2 gap-6">

                    {{-- Dirección --}}
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Dirección</label>
                        <input type="text"
                               name="direccion"
                               value="{{ $contacto->direccion ?? '' }}"
                               class="w-full border border-gray-300 rounded px-4 py-2">
                    </div>

                    {{-- Mail --}}
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Mail</label>
                        <input type="email"
                               name="mail"
                               value="{{ $contacto->mail ?? '' }}"
                               class="w-full border border-gray-300 rounded px-4 py-2">
                    </div>

                    {{-- Teléfono --}}
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Teléfono</label>
                        <input type="text"
                               name="telefono"
                               value="{{ $contacto->telefono ?? '' }}"
                               class="w-full border border-gray-300 rounded px-4 py-2">
                    </div>

                    {{-- WhatsApp --}}
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">WhatsApp</label>
                        <input type="text"
                               name="whatssap"
                               value="{{ $contacto->whatssap ?? '' }}"
                               class="w-full border border-gray-300 rounded px-4 py-2">
                    </div>

                    {{-- Instagram --}}
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Instagram</label>
                        <input type="text"
                            name="instagram"
                            value="{{ $contacto->instagram ?? '' }}"
                            placeholder="https://www.instagram.com/usuario"
                            class="w-full border border-gray-300 rounded px-4 py-2">
                    </div>

                    {{-- Facebook --}}
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">Facebook</label>
                        <input type="text"
                            name="facebook"
                            value="{{ $contacto->facebook ?? '' }}"
                            placeholder="https://www.facebook.com/pagina"
                            class="w-full border border-gray-300 rounded px-4 py-2">
                    </div>

                    {{-- YouTube --}}
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">YouTube</label>
                        <input type="text"
                            name="youtube"
                            value="{{ $contacto->youtube ?? '' }}"
                            placeholder="https://www.youtube.com/@canal"
                            class="w-full border border-gray-300 rounded px-4 py-2">
                    </div>

                    {{-- LinkedIn --}}
                    <div class="mb-4">
                        <label class="block text-gray-700 font-medium mb-2">LinkedIn</label>
                        <input type="text"
                            name="linkedin"
                            value="{{ $contacto->linkedin ?? '' }}"
                            placeholder="https://www.linkedin.com/company/empresa"
                            class="w-full border border-gray-300 rounded px-4 py-2">
                    </div>
                </div>

                {{-- Botón --}}
                <button type="submit"
                        class="border rounded p-4 bg-white hover:bg-gray-100 mt-4">
                    Actualizar
                </button>

            </form>
        </div>
    </div>
@endsection
