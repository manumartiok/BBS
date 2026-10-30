{{-- Opciones de forma de envío (Mercado Flex con código postal, transporte y retiro en local).
     Variables: $formId (formulario que envía los campos), $envioActual, $cpActual, $contacto --}}
<section class="border border-[#D9D9D9] rounded-[12px] pt-[27px] pb-[13px] pl-[18px] pr-[18px]">

    {{-- Mercado Flex --}}
    <label class="flex items-center gap-[11px] h-[22px] text-[20px] leading-[22px] text-[#222] cursor-pointer">
        <input form="{{ $formId }}" type="radio" name="envio" value="mercado_flex" class="sr-only peer" @checked($envioActual === 'mercado_flex')>
        <img src="{{ asset('assets/img/canje/radio.svg') }}" alt="" class="w-[14px] h-[14px] max-w-none peer-checked:hidden">
        <span class="relative hidden peer-checked:block w-[14px] h-[14px]">
            <img src="{{ asset('assets/img/canje/radio.svg') }}" alt="" class="absolute inset-0 w-[14px] h-[14px] max-w-none">
            <img src="{{ asset('assets/img/canje/radio-punto.svg') }}" alt="" class="absolute left-[2px] top-[2px] w-[10px] h-[10px] max-w-none">
        </span>
        <span>Mercado Flex</span>
    </label>

    <div class="relative mt-[20px] ml-[23px] w-[392px] max-w-full h-[50px] border border-black rounded-[25px]">
        <input type="text" id="codigo-postal" form="{{ $formId }}" name="codigo_postal" value="{{ $cpActual }}" inputmode="numeric" maxlength="10" placeholder="Código postal"
               class="w-full h-full pl-[23px] pr-[140px] bg-transparent rounded-[25px] text-[16px] placeholder:text-black focus:outline-none">
        <button type="button" id="btn-calcular"
                class="absolute right-[-1px] top-[-1px] w-[126px] h-[50px] bg-black rounded-[25px] text-[16px] text-white transition-opacity hover:opacity-80">
            Calcular
        </button>
    </div>

    {{-- Transporte --}}
    <label class="flex items-center gap-[11px] h-[22px] mt-[22px] text-[20px] leading-[22px] text-[#222] cursor-pointer">
        <input form="{{ $formId }}" type="radio" name="envio" value="transporte_terminal" class="sr-only peer" @checked($envioActual === 'transporte_terminal')>
        <img src="{{ asset('assets/img/canje/radio.svg') }}" alt="" class="w-[14px] h-[14px] max-w-none peer-checked:hidden">
        <span class="relative hidden peer-checked:block w-[14px] h-[14px]">
            <img src="{{ asset('assets/img/canje/radio.svg') }}" alt="" class="absolute inset-0 w-[14px] h-[14px] max-w-none">
            <img src="{{ asset('assets/img/canje/radio-punto.svg') }}" alt="" class="absolute left-[2px] top-[2px] w-[10px] h-[10px] max-w-none">
        </span>
        <span>Transporte a la terminal</span>
    </label>

    {{-- Retiro en local --}}
    <label class="flex items-center gap-[11px] h-[22px] mt-[16px] text-[20px] leading-[22px] text-[#222] cursor-pointer">
        <input form="{{ $formId }}" type="radio" name="envio" value="retiro_local" class="sr-only peer" @checked($envioActual === 'retiro_local')>
        <img src="{{ asset('assets/img/canje/radio.svg') }}" alt="" class="w-[14px] h-[14px] max-w-none peer-checked:hidden">
        <span class="relative hidden peer-checked:block w-[14px] h-[14px]">
            <img src="{{ asset('assets/img/canje/radio.svg') }}" alt="" class="absolute inset-0 w-[14px] h-[14px] max-w-none">
            <img src="{{ asset('assets/img/canje/radio-punto.svg') }}" alt="" class="absolute left-[2px] top-[2px] w-[10px] h-[10px] max-w-none">
        </span>
        <span>
            Retiro en local
            @if($contacto?->direccion)
                <a href="https://maps.google.com/?q={{ urlencode($contacto->direccion) }}" target="_blank" rel="noopener noreferrer" class="underline">(ver mapa)</a>
            @endif
        </span>
    </label>

    <div class="mt-[8px] ml-[43px] text-[13px] leading-[22px] text-[#222] whitespace-pre-line">{{ \App\Models\Contacto::textoRetiroLocal() }}</div>
</section>

<script>
    // El cálculo de envío todavía no tiene backend
    document.getElementById('btn-calcular')?.addEventListener('click', function () {
        if (!document.getElementById('codigo-postal').value.trim()) {
            Swal.fire({ icon: 'warning', title: 'Falta el código postal', text: 'Ingresá tu código postal para calcular el envío.', confirmButtonColor: '#0D1A35' });
            return;
        }
        Swal.fire({ icon: 'info', title: 'Próximamente', text: 'El cálculo del envío todavía no está disponible.', confirmButtonColor: '#0D1A35' });
    });
</script>
