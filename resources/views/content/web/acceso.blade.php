@extends('layouts.web')

@section('title', 'Iniciar sesión')

@section('content')
@php
    $claseInput = 'w-full h-[50px] px-[23px] bg-transparent border border-black rounded-[25px] text-[16px] focus:outline-none focus:ring-1 focus:ring-black';

    // [campo, etiqueta, tipo]
    $camposRegistro = [
        ['nombre',        'Nombre y apellido', 'text'],
        ['cuit',          'CUIT / CUIL',       'text'],
        ['email',         'Email',             'email'],
        ['celular',       'Celular',           'tel'],
        ['direccion',     'Dirección',         'text'],
        ['localidad',     'Localidad',         'text'],
        ['provincia',     'Provincia',         'text'],
        ['codigo_postal', 'Código postal',     'text'],
        ['password',      'Contraseña',        'password'],
        ['password_confirmation', 'Repetir contraseña', 'password'],
    ];

    // Los valores anteriores se recuperan solo en el formulario que falló
    $erroresLogin = $errors->getBag('login');
    $erroresRegistro = $errors->getBag('registro');
@endphp

<div class="w-full text-black" style="font-family: 'Montserrat', sans-serif;">
    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pt-[15px] pb-[80px]">

        {{-- breadcrumb --}}
        <p class="text-[12px] leading-[15px] mb-[31px]"><a class="font-semibold" href="{{ route('home') }}">Inicio /</a> Iniciar sesión</p>

        <div class="flex flex-col gap-[56px] lg:grid lg:grid-cols-[392fr_816fr] lg:gap-x-[24px] lg:items-start">

            {{-- iniciar sesión --}}
            <form action="{{ route('acceso.login') }}" method="POST" class="flex flex-col">
                @csrf
                <h1 class="mb-[46px] text-[24px] leading-[29px] font-semibold">Iniciar sesión</h1>

                @if($erroresLogin->any())
                    <div class="mb-[24px] px-[16px] py-[12px] border border-[#FF0000] rounded-[12px] text-[14px] text-[#FF0000]">
                        @foreach($erroresLogin->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="flex flex-col gap-[41px]">
                    <div>
                        <label for="login-usuario" class="block mb-[1px] text-[16px] leading-[19px]">Email o nombre</label>
                        <input type="text" id="login-usuario" name="login" value="{{ $erroresLogin->any() ? old('login') : '' }}" autocomplete="username" required class="{{ $claseInput }}">
                    </div>
                    <div>
                        <label for="login-password" class="block mb-[1px] text-[16px] leading-[19px]">Contraseña</label>
                        <input type="password" id="login-password" name="password" required class="{{ $claseInput }}">
                    </div>
                </div>

                <div class="flex justify-center mt-[27px]">
                    <button type="submit"
                            class="p-[16px] bg-black border border-black rounded-[22px] text-[16px] leading-[19px] font-semibold uppercase text-white transition-opacity hover:opacity-80">
                        Iniciar sesión
                    </button>
                </div>
            </form>

            {{-- registrarse --}}
            <form action="{{ route('acceso.registro') }}" method="POST">
                @csrf
                <h2 class="mb-[46px] text-[24px] leading-[29px] font-semibold">Registrarse</h2>

                @if($erroresRegistro->any())
                    <div class="mb-[24px] px-[16px] py-[12px] border border-[#FF0000] rounded-[12px] text-[14px] text-[#FF0000]">
                        @foreach($erroresRegistro->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-[24px] gap-y-[41px]">
                    @foreach($camposRegistro as [$nombre, $etiqueta, $tipo])
                        <div>
                            <label for="registro-{{ $nombre }}" class="block mb-[1px] text-[16px] leading-[19px]">{{ $etiqueta }}</label>
                            <input type="{{ $tipo }}" id="registro-{{ $nombre }}" name="{{ $nombre }}"
                                   @if($tipo !== 'password') value="{{ $erroresRegistro->any() ? old($nombre) : '' }}" @endif
                                   required class="{{ $claseInput }}">
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-end mt-[36px]">
                    <button type="submit"
                            class="p-[16px] bg-black border border-black rounded-[22px] text-[16px] leading-[19px] font-semibold uppercase text-white transition-opacity hover:opacity-80">
                        Registrarse
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
