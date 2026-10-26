@php
    $contacto_contenido = App\Models\Contacto::first();
@endphp

@extends('layouts.web')

@section('title', 'Contacto')

@section('content')

<div class="w-full text-black" style="font-family: 'Montserrat', sans-serif;">
    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pt-[15px] pb-[80px]">

        {{-- breadcrumb --}}
        <p class="text-[12px] leading-[15px] mb-[35px]"><a class="font-semibold" href="{{ route('home') }}">Inicio /</a> Contacto</p>

        <div class="flex flex-col gap-[40px] lg:grid lg:grid-cols-[416fr_808fr] lg:gap-0">

            {{-- datos de contacto --}}
            <div>
                <h1 class="text-[24px] font-semibold leading-[29px] mb-[24px]">Datos de contacto</h1>
                <div class="flex flex-col items-start gap-[15px] text-[16px] leading-[20px]">
                    @if($contacto_contenido->direccion)
                    <a href="https://maps.google.com/?q={{ urlencode($contacto_contenido->direccion) }}" target="_blank" rel="noopener noreferrer" class="flex items-start gap-[9px]">
                        <span class="relative block w-[20px] h-[20px] flex-shrink-0">
                            <img src="{{ asset('assets/img/contacto/pin.svg') }}" alt="" class="absolute left-[-1px] top-[-1px] w-[18px] h-[22px] max-w-none">
                        </span>
                        <span>{{ $contacto_contenido->direccion }}</span>
                    </a>
                    @endif

                    @if($contacto_contenido->telefono)
                    <div class="flex items-start gap-[9px]">
                        <span class="relative block w-[20px] h-[20px] flex-shrink-0">
                            <img src="{{ asset('assets/img/contacto/phone.svg') }}" alt="" class="absolute left-[-1px] top-[-1px] w-[22.0008px] h-[22.0019px] max-w-none">
                        </span>
                        <span>{{ $contacto_contenido->telefono }}</span>
                    </div>
                    @endif

                    @if($contacto_contenido->whatssap)
                    <a href="https://wa.me/{{ preg_replace('/\D+/', '', $contacto_contenido->whatssap) }}" target="_blank" rel="noopener noreferrer" class="flex items-start gap-[9px] whitespace-nowrap">
                        <span class="relative block w-[20px] h-[20px] flex-shrink-0">
                            <img src="{{ asset('assets/img/contacto/whatsapp.svg') }}" alt="" class="absolute left-0 top-0 w-[19.9042px] h-[20px] max-w-none">
                        </span>
                        <span>{{ $contacto_contenido->whatssap }}</span>
                    </a>
                    @endif

                    @if($contacto_contenido->mail)
                    <a href="mailto:{{ $contacto_contenido->mail }}" class="flex items-start gap-[9px]">
                        <span class="relative block w-[20px] h-[20px] flex-shrink-0">
                            <img src="{{ asset('assets/img/contacto/mail.svg') }}" alt="" class="absolute left-0 top-[3px] w-[20px] h-[14px] max-w-none">
                        </span>
                        <span class="break-all">{{ $contacto_contenido->mail }}</span>
                    </a>
                    @endif
                </div>
            </div>

            {{-- formulario --}}
            <form id="formContacto" action="{{ route('form.contacto') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-x-[24px] gap-y-[31px] lg:pt-[1px]">
                @csrf
                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

                <div class="flex flex-col gap-[10px]">
                    <label for="nombre" class="text-[16px] leading-[20px]">Nombre</label>
                    <input type="text" id="nombre" name="nombre" required class="w-full h-[50px] px-[24px] text-[16px] bg-transparent border border-black rounded-[25px] focus:outline-none focus:ring-1 focus:ring-black">
                </div>

                <div class="flex flex-col gap-[10px]">
                    <label for="apellido" class="text-[16px] leading-[20px]">Apellido</label>
                    <input type="text" id="apellido" name="apellido" required class="w-full h-[50px] px-[24px] text-[16px] bg-transparent border border-black rounded-[25px] focus:outline-none focus:ring-1 focus:ring-black">
                </div>

                <div class="flex flex-col gap-[10px]">
                    <label for="email" class="text-[16px] leading-[20px]">Email</label>
                    <input type="email" id="email" name="email" required class="w-full h-[50px] px-[24px] text-[16px] bg-transparent border border-black rounded-[25px] focus:outline-none focus:ring-1 focus:ring-black">
                </div>

                <div class="flex flex-col gap-[10px]">
                    <label for="celular" class="text-[16px] leading-[20px]">Teléfono</label>
                    <input type="tel" id="celular" name="celular" inputmode="numeric" pattern="[0-9]*" class="w-full h-[50px] px-[24px] text-[16px] bg-transparent border border-black rounded-[25px] focus:outline-none focus:ring-1 focus:ring-black">
                </div>

                <div class="flex flex-col gap-[10px] md:col-span-2">
                    <label for="mensaje" class="text-[16px] leading-[20px]">Mensaje</label>
                    <textarea id="mensaje" name="mensaje" required class="w-full h-[100px] px-[24px] py-[14px] text-[16px] bg-transparent border border-black rounded-[12px] resize-none focus:outline-none focus:ring-1 focus:ring-black"></textarea>
                </div>

                <div class="flex justify-end mt-[5px] md:col-span-2">
                    <button type="submit" class="h-[44px] px-[16px] bg-black text-white text-[16px] font-semibold uppercase rounded-[22px]">Enviar mensaje</button>
                </div>
            </form>
        </div>

        {{-- mapa --}}
        @if($contacto_contenido->ubicacion)
        <div class="w-full h-[320px] md:h-[566px] mt-[79px]">
            <iframe
                src="{{ $contacto_contenido->ubicacion }}"
                title="Ubicación"
                class="w-full h-full border-0"
                allowfullscreen
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
        @endif

    </div>
</div>

<script src="https://www.google.com/recaptcha/api.js?render={{ env('RECAPTCHA_SITE_KEY') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const formContacto = document.getElementById('formContacto');
    const celularInput = document.getElementById('celular');
    const recaptchaSiteKey = "{{ env('RECAPTCHA_SITE_KEY') }}";

    // Permitir solo números en teléfono
    celularInput.addEventListener('input', function(e) {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    formContacto.addEventListener('submit', function(e) {
        e.preventDefault();

        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;

        submitBtn.disabled = true;
        submitBtn.textContent = 'Enviando...';

        // // 1) Pedir el token a reCAPTCHA
         grecaptcha.ready(function() {
             grecaptcha.execute(recaptchaSiteKey, { action: 'contacto' }).then(function(token) {
                 // 2) Setear el token en el input hidden
                 document.getElementById('g-recaptcha-response').value = token;

                // 3) Recién acá armar el FormData con el token incluido
                const formData = new FormData(formContacto);

                // 4) Hacer el fetch como ya lo tenías
                fetch(formContacto.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Mensaje enviado!',
                            text: data.message || 'Tu consulta ha sido enviada exitosamente.',
                            confirmButtonColor: '#5FBB46'
                        });
                        formContacto.reset();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message || 'Hubo un problema al enviar tu consulta.',
                            confirmButtonColor: '#0A3858'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error. Por favor, intenta nuevamente.',
                        confirmButtonColor: '#0A3858'
                    });
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                });
            });
        });
        });
});
</script>

@endsection
