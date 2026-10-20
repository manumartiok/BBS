@php
    $contacto_ban = App\Models\Banner::seccion('contacto');
    $contacto_contenido = App\Models\Contacto::first();
@endphp

@extends('layouts.web')

@section('title', 'Contacto')

@section('content')

{{-- banner --}}
<div class="w-full h-[300px] bg-cover bg-center letra text-white pt-[120px] pb-[70px]"
     style="background-image: url('{{ $contacto_ban->foto }}');">
    <div class="h-full max-w-[1366px] mx-auto px-[5%] md:px-[71px] flex flex-col justify-between">
        <h3 class="text-[12px]">
            <a class="font-bold" href="{{ route('home') }}">Inicio</a> > Contacto
        </h3>
        <h1 class="text-[32px] font-medium">{{ $contacto_ban->t('texto') }}</h1>
    </div>
</div>

{{-- contenido --}}
<div class=" letra max-w-[1366px] mx-auto px-[5%] md:px-[71px] flex flex-col">

    {{-- formulario y redes --}}
    <div class="flex flex-col md:flex-row w-full gap-[40px] my-[80px]">
        {{-- redes --}}
        <div class="flex flex-col items-center md:items-start w-full md:w-1/3 gap-[10px] ">
           {{-- Redes Sociales --}}
            <div class="h-full flex flex-col items-center md:items-start">

                {{-- Dirección --}}
                @if($contacto_contenido->direccion)
                    <a href="https://maps.google.com/?q={{ urlencode($contacto_contenido->direccion) }}" target="_blank"
                    class="flex items-start gap-3 mb-[10px]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M20 10C20 16 12 22 12 22C12 22 4 16 4 10C4 7.87827 4.84285 5.84344 6.34315 4.34315C7.84344 2.84285 9.87827 2 12 2C14.1217 2 16.1566 2.84285 17.6569 4.34315C19.1571 5.84344 20 7.87827 20 10Z" stroke="#FF9000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 13C13.6569 13 15 11.6569 15 10C15 8.34315 13.6569 7 12 7C10.3431 7 9 8.34315 9 10C9 11.6569 10.3431 13 12 13Z" stroke="#FF9000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span class="max-w-[300px]">{{ $contacto_contenido->direccion }}</span>
                    </a>
                @endif

                {{-- Teléfono 1 --}}
                @if ($contacto_contenido->telefono)
                <h3 class="flex items-center gap-3 whitespace-nowrap mb-[10px]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M21.9999 16.92V19.92C22.0011 20.1985 21.944 20.4742 21.8324 20.7293C21.7209 20.9845 21.5572 21.2136 21.352 21.4019C21.1468 21.5901 20.9045 21.7335 20.6407 21.8227C20.3769 21.9119 20.0973 21.9451 19.8199 21.92C16.7428 21.5856 13.7869 20.5341 11.1899 18.85C8.77376 17.3147 6.72527 15.2662 5.18993 12.85C3.49991 10.2412 2.44818 7.271 2.11993 4.18C2.09494 3.90347 2.12781 3.62476 2.21643 3.36163C2.30506 3.09849 2.4475 2.85669 2.6347 2.65162C2.82189 2.44655 3.04974 2.28271 3.30372 2.17052C3.55771 2.05834 3.83227 2.00026 4.10993 2H7.10993C7.59524 1.99523 8.06572 2.16708 8.43369 2.48353C8.80166 2.79999 9.04201 3.23945 9.10993 3.72C9.23656 4.68007 9.47138 5.62273 9.80993 6.53C9.94448 6.88793 9.9736 7.27692 9.89384 7.65088C9.81408 8.02485 9.6288 8.36811 9.35993 8.64L8.08993 9.91C9.51349 12.4135 11.5864 14.4864 14.0899 15.91L15.3599 14.64C15.6318 14.3711 15.9751 14.1859 16.3491 14.1061C16.723 14.0263 17.112 14.0555 17.4699 14.19C18.3772 14.5286 19.3199 14.7634 20.2799 14.89C20.7657 14.9585 21.2093 15.2032 21.5265 15.5775C21.8436 15.9518 22.0121 16.4296 21.9999 16.92Z" stroke="#FF9000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <div class="">
                    <span>{{ $contacto_contenido->telefono }}</span> | <span>{{ $contacto_contenido->telefono2 }}</span>
                    </div>
                </h3>
                @endif
                
                {{-- Mail --}}
                @if ($contacto_contenido->mail)
                    <div
                    class="flex  gap-3 whitespace-nowrap mb-[10px]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M20 4H4C2.89543 4 2 4.89543 2 6V18C2 19.1046 2.89543 20 4 20H20C21.1046 20 22 19.1046 22 18V6C22 4.89543 21.1046 4 20 4Z" stroke="#FF9000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M22 7L13.03 12.7C12.7213 12.8934 12.3643 12.996 12 12.996C11.6357 12.996 11.2787 12.8934 10.97 12.7L2 7" stroke="#FF9000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <div class="flex flex-col">
                        <a href="mailto:{{ $contacto_contenido->mail }}">{{ $contacto_contenido->mail }}</a>
                        <a href="mailto:{{ $contacto_contenido->mail2 }}">{{ $contacto_contenido->mail2 }}</a>
                        <a href="mailto:{{ $contacto_contenido->mail3 }}">{{ $contacto_contenido->mail3 }}</a>
                        </div>
                    </div>
                @endif

                {{-- WhatsApp --}}
                @if ($contacto_contenido->whatssap)
                    <a href="https://wa.me/{{ preg_replace('/\D+/', '', $contacto_contenido->whatssap) }}" target="_blank"
                    class="flex items-center gap-2 whitespace-nowrap mb-[10px]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M17.0076 2.90097C13.1316 -0.970609 6.85625 -0.965179 2.9857 2.9064C-0.179142 6.07751 -0.830567 10.9808 1.40599 14.8632L0 20L5.25483 18.6154C6.70968 19.4081 8.33824 19.8208 9.99394 19.8208C15.4333 19.9077 19.9119 15.5691 19.9987 10.1283C20.0422 7.40786 18.9565 4.79061 17.0076 2.90097ZM9.99937 18.1538C8.52281 18.1538 7.07339 17.7574 5.80311 17.0081L5.50454 16.8289L2.38856 17.6488L3.21912 14.608L3.0237 14.2931C0.656853 10.3944 1.89999 5.31732 5.79225 2.94984C9.68995 0.582368 14.7656 1.82583 17.1325 5.71913C17.8979 6.98432 18.3159 8.4287 18.3322 9.91109C18.2833 14.4777 14.5702 18.1538 9.99937 18.1538ZM14.5159 11.9799C14.2716 11.855 13.0502 11.2577 12.8222 11.1763C12.5942 11.0948 12.4314 11.0514 12.2631 11.3012C12.0948 11.5509 11.6225 12.1048 11.4759 12.2731C11.3294 12.4415 11.1882 12.4632 10.9385 12.3383C9.48366 11.6107 8.52824 11.0405 7.56739 9.38981C7.31224 8.94998 7.82253 8.98256 8.29481 8.03774C8.35995 7.89656 8.35452 7.73366 8.2731 7.60334C8.21338 7.47845 7.71396 6.2567 7.50767 5.76258C7.30682 5.27931 7.10053 5.3499 6.94853 5.33904C6.80739 5.32818 6.6391 5.32818 6.47625 5.32818C6.22111 5.33361 5.98225 5.44763 5.81397 5.63768C5.2494 6.17525 4.93454 6.92459 4.9454 7.70651C5.02683 8.64047 5.37968 9.53099 5.95511 10.2695C6.07997 10.4324 7.7031 12.9356 10.1894 14.0107C11.7636 14.6895 12.3771 14.7492 13.1642 14.6297C13.6419 14.5592 14.6299 14.0324 14.8362 13.4514C14.9991 13.0822 15.0479 12.6695 14.9773 12.2731C14.9176 12.1645 14.7548 12.0994 14.5105 11.9799H14.5159Z" fill="#29176D"/>
                        </svg>
                        <span>{{ $contacto_contenido->whatssap }}</span>
                    </a>
                @endif

            </div>
        </div>

        {{-- formulario --}}
        <div class="w-full md:w-2/3">
            <form id="formContacto" action="{{ route('form.contacto') }}" method="POST" enctype="multipart/form-data" class="w-full flex flex-col gap-[20px]">
            @csrf
            
            <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">
            
            <!-- Fila 1 -->
            <div class="flex flex-col md:flex-row gap-6">
                <div class="w-full md:w-1/2 flex flex-col gap-2">
                    <label for="nombre" class="text-[16px] font-normal text-[#000]">Nombre y Apellido*</label>
                    <input type="text" id="nombre" name="nombre" class="h-[45px] border border-[#DCDCDC] rounded-[4px] px-4 focus:outline-none" required>
                </div>
                <div class="w-full md:w-1/2 flex flex-col gap-2">
                    <label for="email" class="text-[16px] font-normal text-[#000]">Email*</label>
                    <input type="email" id="email" name="email" class="h-[45px] border border-[#DCDCDC] rounded-[4px] px-4 focus:outline-none" required>
                </div>
            </div>

            <!-- Fila 2 -->
            <div class="flex flex-col md:flex-row gap-6">
                <div class="w-full md:w-1/2 flex flex-col gap-2">
                    <label for="celular" class="text-[16px] font-normal text-[#000]">Celular*</label>
                    <input type="tel" id="celular" name="celular" 
                        pattern="[0-9]*" 
                        inputmode="numeric"
                        class="h-[45px] border border-[#DCDCDC] rounded-[4px] px-4 focus:outline-none" required>
                </div>
                <div class="w-full md:w-1/2 flex flex-col gap-2">
                    <label for="empresa" class="text-[16px] font-normal text-[#000]">Empresa</label>
                    <input type="empresa" id="empresa" name="empresa" class="h-[45px] border border-[#DCDCDC] rounded-[4px] px-4 focus:outline-none">
                </div>
            </div>

            <!-- Fila 3 (Mensaje y datos obligatorios) -->
            <div class="flex flex-col md:flex-row gap-6">
                <div class="w-full md:w-1/2 flex flex-col gap-2">
                    <label for="mensaje" class="text-[16px] font-normal text-[#000]">Mensaje</label>
                    <textarea id="mensaje" name="mensaje" rows="6" class="border border-[#DCDCDC] rounded-[4px] px-4 py-3 focus:outline-none resize-none"></textarea>
                </div>
                <div class="w-full md:w-1/2 flex flex-col justify-end items-start gap-4">
                    <h2 class="text-[16px] text-[#000]">*Campos obligatorios</h2>
                    <button type="submit" class="w-full  h-[40px] bg-[#FF9000] text-white rounded-[6px] text-[15px]">
                       Enviar consulta
                    </button>
                </div>
            </div>
        </form>
        </div>
    </div>
</div>

    {{-- mapa --}}
    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] h-[520px] mb-[180px] relative overflow-hidden">
        <iframe 
            src="{!! $contacto_contenido->ubicacion !!}" 
            width="100%" 
            height="100%" 
            style="border:0;" 
            allowfullscreen 
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </div>

{{-- mapa API--}}
<div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] h-[500px] mb-[70px]">
    <div id="mapaContacto" class="relative z-0 w-full h-full"></div>
</div>

<script src="https://www.google.com/recaptcha/api.js?render={{ env('RECAPTCHA_SITE_KEY') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const formContacto = document.getElementById('formContacto');
    const celularInput = document.getElementById('celular');
    const recaptchaSiteKey = "{{ env('RECAPTCHA_SITE_KEY') }}";

    // Permitir solo números en celular
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

{{-- Mapa: Leaflet + OpenStreetMap (gratuito, sin API key) --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lat = parseFloat("{{ $contacto_contenido->latitud ?? '' }}");
    const lng = parseFloat("{{ $contacto_contenido->longitud ?? '' }}");
    const contenedor = document.getElementById('mapaContacto');

    if (!contenedor || isNaN(lat) || isNaN(lng)) return;

    // Filtro CSS aplicado a las baldosas (gris claro). Usar 'none' para color normal.
    const FILTRO_MAPA = 'grayscale(1) contrast(.9) brightness(1.1)';
    const ZOOM = 15;

    const iconoPin = L.icon({
        iconUrl: 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(`
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="30" viewBox="0 0 50 50" fill="none">
            <path d="M41.6666 20.8334C41.6666 33.3334 24.9999 45.8334 24.9999 45.8334C24.9999 45.8334 8.33325 33.3334 8.33325 20.8334C8.33325 16.4131 10.0892 12.1738 13.2148 9.04824C16.3404 5.92263 20.5796 4.16669 24.9999 4.16669C29.4202 4.16669 33.6594 5.92263 36.785 9.04824C39.9106 12.1738 41.6666 16.4131 41.6666 20.8334Z" fill="#FF9000" stroke="#FF9000" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M25 27.0833C28.4518 27.0833 31.25 24.2851 31.25 20.8333C31.25 17.3815 28.4518 14.5833 25 14.5833C21.5482 14.5833 18.75 17.3815 18.75 20.8333C18.75 24.2851 21.5482 27.0833 25 27.0833Z" fill="white" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        `),
        iconSize: [70, 70],
        iconAnchor: [35, 70]
    });

    const mapa = L.map(contenedor, { zoomControl: true, attributionControl: true }).setView([lat, lng], ZOOM);
    mapa.attributionControl.setPrefix(false);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a>'
    }).addTo(mapa);

    mapa.getPane('tilePane').style.filter = FILTRO_MAPA;

    L.marker([lat, lng], { icon: iconoPin }).addTo(mapa);
});
</script>

@endsection

