{{-- Datos de facturación y otra dirección de entrega (formulario de los pasos finales de canje y compra).
     Variable: $valores (nombre, cuit, email, celular, direccion, localidad, provincia, codigo_postal) para precargar --}}
@php
    // [campo, etiqueta, tipo]
    $campos = [
        ['nombre',        'Nombre y apellido', 'text'],
        ['cuit',          'CUIT / CUIL',       'text'],
        ['email',         'Email',             'email'],
        ['celular',       'Celular',           'text'],
        ['direccion',     'Dirección',         'text'],
        ['localidad',     'Localidad',         'text'],
        ['provincia',     'Provincia',         'text'],
        ['codigo_postal', 'Código postal',     'text'],
    ];
    $camposEntrega = [
        ['entrega_direccion',     'Dirección'],
        ['entrega_localidad',     'Localidad'],
        ['entrega_provincia',     'Provincia'],
        ['entrega_codigo_postal', 'Código postal'],
    ];
    $otraDireccion = (bool) old('otra_direccion');
    $claseInput = 'w-full h-[50px] px-[23px] bg-transparent border border-black rounded-[25px] text-[16px] focus:outline-none focus:ring-1 focus:ring-black';
@endphp

<div>
    <h1 class="mb-[31px] text-[24px] leading-[29px] font-semibold">Detalle facturación</h1>

    @if(session('error'))
        <div class="mb-[24px] px-[16px] py-[12px] border border-[#FF0000] rounded-[12px] text-[14px] text-[#FF0000]">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-[24px] px-[16px] py-[12px] border border-[#FF0000] rounded-[12px] text-[14px] text-[#FF0000]">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-[24px] gap-y-[34px]">
        @foreach($campos as [$nombre, $etiqueta, $tipo])
            <div>
                <label for="{{ $nombre }}" class="block mb-[4px] text-[16px] leading-[19px]">{{ $etiqueta }}</label>
                <input type="{{ $tipo }}" id="{{ $nombre }}" name="{{ $nombre }}" value="{{ old($nombre, $valores[$nombre] ?? '') }}" required class="{{ $claseInput }}">
            </div>
        @endforeach
    </div>

    <div class="mt-[30px]">
        <label for="mensaje" class="block mb-[4px] text-[16px] leading-[19px]">Mensaje</label>
        <textarea id="mensaje" name="mensaje" rows="4" maxlength="2000"
                  class="block w-full h-[106px] px-[23px] py-[14px] bg-transparent border border-black rounded-[12px] text-[16px] resize-none focus:outline-none focus:ring-1 focus:ring-black">{{ old('mensaje') }}</textarea>
    </div>

    {{-- otra dirección de entrega --}}
    <label class="flex items-center gap-[10px] mt-[72px] text-[16px] leading-[19px] cursor-pointer w-fit">
        <input type="checkbox" id="otra-direccion" name="otra_direccion" value="1" class="sr-only peer" @checked($otraDireccion)>
        <span class="block w-[16px] h-[16px] border border-black rounded-[3px] peer-checked:hidden"></span>
        <img src="{{ asset('assets/img/canje/checkbox-activo.svg') }}" alt="" class="hidden peer-checked:block w-[16px] h-[16px] max-w-none">
        <span>¿Otra dirección de entrega?</span>
    </label>

    <div id="entrega-otra" class="grid grid-cols-1 sm:grid-cols-2 gap-x-[24px] gap-y-[34px] mt-[27px] {{ $otraDireccion ? '' : 'hidden' }}">
        @foreach($camposEntrega as [$nombre, $etiqueta])
            <div>
                <label for="{{ $nombre }}" class="block mb-[4px] text-[16px] leading-[19px]">{{ $etiqueta }}</label>
                <input type="text" id="{{ $nombre }}" name="{{ $nombre }}" value="{{ old($nombre) }}" class="{{ $claseInput }}">
            </div>
        @endforeach
    </div>
</div>

<script>
    (function () {
        const otraDireccion = document.getElementById('otra-direccion');
        const entregaOtra = document.getElementById('entrega-otra');
        const exigir = () => entregaOtra.querySelectorAll('input').forEach(i => i.required = otraDireccion.checked);

        otraDireccion.addEventListener('change', () => {
            entregaOtra.classList.toggle('hidden', !otraDireccion.checked);
            exigir();
        });
        exigir();
    })();
</script>
