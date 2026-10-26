@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')

{{-- Cabezal --}}
<div>
    <h3 class="text-[20px] font-bold text-gray-500">Editar Usuarios</h3>
    <hr class="mx-6">
</div>

{{-- Editor --}}
<div class="mx-20 pt-6" id="app">
    <div class="border border-rounded p-4">

        <h3 class="text-[18px] font-semibold text-gray-600">Editar usuario</h3>
        <hr class="my-3">

        {{-- Errores --}}
        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <strong>Errores:</strong>
                <ul class="mt-2">
                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Mensajes --}}
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('adm.web-usuarios-update') }}">
            @csrf

            <input type="hidden" name="usuarios_id" value="{{ $usuario->id }}">

            {{-- Usuario --}}
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Usuario</label>
                <input
                    type="text"
                    name="usuario"
                    value="{{ old('usuario', $usuario->usuario) }}"
                    class="w-full border rounded px-4 py-2"
                >
            </div>

            {{-- Email --}}
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Email</label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $usuario->email) }}"
                    class="w-full border rounded px-4 py-2"
                >
            </div>

            {{-- Datos del usuario --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Razón social</label>
                    <input type="text" name="razon_social"
                        value="{{ old('razon_social', $usuario->razon_social) }}"
                        class="w-full border rounded px-4 py-2">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">CUIT</label>
                    <input type="text" name="cuit"
                        value="{{ old('cuit', $usuario->cuit) }}"
                        class="w-full border rounded px-4 py-2">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Teléfono</label>
                    <input type="text" name="telefono"
                        value="{{ old('telefono', $usuario->telefono) }}"
                        class="w-full border rounded px-4 py-2">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Domicilio</label>
                    <input type="text" name="domicilio"
                        value="{{ old('domicilio', $usuario->domicilio) }}"
                        class="w-full border rounded px-4 py-2">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Código Postal</label>
                    <input type="text" name="codigo_postal"
                        value="{{ old('codigo_postal', $usuario->codigo_postal) }}"
                        class="w-full border rounded px-4 py-2">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Localidad</label>
                    <input type="text" name="localidad"
                        value="{{ old('localidad', $usuario->localidad) }}"
                        class="w-full border rounded px-4 py-2">
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Provincia</label>
                    <input type="text" name="provincia"
                        value="{{ old('provincia', $usuario->provincia) }}"
                        class="w-full border rounded px-4 py-2">
                </div>
            </div>

            {{-- Rol y puntos --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Rol</label>
                    <select name="rol" class="w-full border rounded px-4 py-2">
                        <option value="">Seleccionar rol</option>
                        @foreach(\App\Models\WebUsuario::ROLES as $valor => $texto)
                            <option value="{{ $valor }}" @selected(old('rol', $usuario->rol) === $valor)>{{ $texto }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Puntos</label>
                    <input
                        type="number"
                        name="puntos"
                        min="0"
                        step="1"
                        value="{{ old('puntos', $usuario->puntos ?? 0) }}"
                        class="w-full border rounded px-4 py-2"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                @for ($i = 1; $i <= 5; $i++)
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            Descuento {{ $i }} (%)
                        </label>
                        <input
                            type="number"
                            name="descuento{{ $i }}"
                            min="0"
                            max="100"
                            step="0.01"
                            value="{{ old('descuento'.$i, $usuario->{'descuento'.$i}) }}"
                            class="w-full border rounded px-4 py-2"
                        >
                    </div>
                @endfor
            </div> 

            {{-- Contraseña --}}
            <div class="mb-4">
                <label class="block text-gray-700 font-medium mb-2">Nueva contraseña</label>
                <input
                    type="password"
                    name="contraseña"
                    class="w-full border rounded px-4 py-2"
                >
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-medium mb-2">Repetir nueva contraseña</label>
                <input
                    type="password"
                    name="contraseña_confirmation"
                    class="w-full border rounded px-4 py-2"
                >
            </div>

            <button type="submit" class="border rounded px-6 py-3 bg-white font-semibold">
                Actualizar
            </button>
        </form>
    </div>
</div>

@endsection