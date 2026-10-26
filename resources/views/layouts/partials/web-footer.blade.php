{{-- Footer del sitio (con el script del newsletter). Lo usan layouts.web y layouts.layout-clientes;
     necesita $contacto_contenido y $logos definidos por el layout. --}}
    <!-- ===================== FOOTER ===================== -->
    @php
        // [campo de Contacto, ícono, nombre, tamaño del ícono]
        $redes = [
            ['youtube',   'youtube.svg',   'YouTube',   'w-[18px] h-[13px]'],
            ['instagram', 'instagram.svg', 'Instagram', 'w-[13px] h-[13px]'],
            ['facebook',  'facebook.svg',  'Facebook',  'w-[13px] h-[13px]'],
            ['linkedin',  'linkedin.svg',  'LinkedIn',  'w-[14px] h-[13px]'],
        ];
    @endphp
    <footer class="text-white bg-gradient-to-b from-[#0d1a35] to-[#060912]" style="font-family: 'Montserrat', sans-serif;">
        <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px]">

            <div class="flex flex-col items-center gap-[40px] pt-[38px] pb-[40px] text-center lg:grid lg:grid-cols-[471fr_406fr_347fr] lg:items-start lg:gap-0 lg:pb-[84px] lg:text-left">

                <!-- Logo y redes -->
                <div class="w-[288px] max-w-full mt-[6px]">
                    <img src="{{ $logos->foto_footer }}" alt="Logo" class="block w-[288px] h-[81px] max-w-full object-contain object-center lg:object-left">

                    <div class="flex justify-center gap-[10px] mt-[30px]">
                        @foreach ($redes as [$campo, $icono, $nombre, $medida])
                            @if($contacto_contenido->{$campo})
                            <a href="{{ $contacto_contenido->{$campo} }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $nombre }}" class="relative flex items-center justify-center w-[32px] h-[32px]">
                                <img src="{{ asset('assets/img/footer/social-circle.svg') }}" alt="" class="absolute inset-0 w-[32px] h-[32px] max-w-none">
                                <img src="{{ asset('assets/img/footer/' . $icono) }}" alt="" class="relative max-w-none {{ $medida }}">
                            </a>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Newsletter -->
                <div class="flex flex-col items-center lg:items-start">
                    <h3 class="text-[24px] font-semibold leading-[29px] mb-[28px]">Suscribite al newsletter</h3>
                    <form action="{{route('adm.newsletter-store')}}" id="newsletterForm" method="POST" class="flex items-center h-[50px] w-[288px] max-w-full pl-[24px] pr-[17px] border border-white rounded-[25px]">
                        @csrf
                        <input type="email" id="newsletterEmail" name="email" placeholder="Email" required
                               class="flex-1 min-w-0 h-full bg-transparent text-[16px] text-white placeholder:text-white focus:outline-none">
                        <button type="submit" aria-label="Suscribirse" class="flex-shrink-0 ml-[12px] flex items-center justify-center">
                            <img src="{{ asset('assets/img/footer/arrow.svg') }}" alt="" class="w-[19px] h-[14px] max-w-none">
                        </button>
                    </form>
                </div>

                <!-- Datos de contacto -->
                <div class="flex flex-col items-center lg:items-start">
                    <h3 class="text-[24px] font-semibold leading-[29px] mb-[24px]">Datos de contacto</h3>
                    <div class="flex flex-col items-start gap-[15px] text-[16px] leading-[20px] text-left">
                        @if($contacto_contenido->direccion)
                        <a href="https://maps.google.com/?q={{ urlencode($contacto_contenido->direccion) }}" target="_blank" rel="noopener noreferrer" class="flex items-start gap-[9px]">
                            <span class="relative block w-[20px] h-[20px] flex-shrink-0">
                                <img src="{{ asset('assets/img/footer/pin.svg') }}" alt="" class="absolute left-[-1px] top-[-1px] w-[18px] h-[22px] max-w-none">
                            </span>
                            <span>{{ $contacto_contenido->direccion }}</span>
                        </a>
                        @endif

                        @if($contacto_contenido->whatssap)
                        <a href="https://wa.me/{{ preg_replace('/\D+/', '', $contacto_contenido->whatssap) }}" target="_blank" rel="noopener noreferrer" class="flex items-start gap-[9px] whitespace-nowrap">
                            <span class="relative block w-[20px] h-[20px] flex-shrink-0">
                                <img src="{{ asset('assets/img/footer/whatsapp.svg') }}" alt="" class="absolute left-0 top-0 w-[19.9042px] h-[20px] max-w-none">
                            </span>
                            <span>{{ $contacto_contenido->whatssap }}</span>
                        </a>
                        @endif

                        @if($contacto_contenido->telefono)
                        <div class="flex items-start gap-[9px]">
                            <span class="relative block w-[20px] h-[20px] flex-shrink-0">
                                <img src="{{ asset('assets/img/footer/phone.svg') }}" alt="" class="absolute left-[-1px] top-[-1px] w-[22.0008px] h-[22.0019px] max-w-none">
                            </span>
                            <span>{{ $contacto_contenido->telefono }}</span>
                        </div>
                        @endif

                        @if($contacto_contenido->mail)
                        <a href="mailto:{{ $contacto_contenido->mail }}" class="flex items-start gap-[9px] whitespace-nowrap">
                            <span class="relative block w-[20px] h-[20px] flex-shrink-0">
                                <img src="{{ asset('assets/img/footer/mail.svg') }}" alt="" class="absolute left-0 top-[3px] w-[20px] h-[14px] max-w-none">
                            </span>
                            <span>{{ $contacto_contenido->mail }}</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            <img src="{{ asset('assets/img/footer/divider.svg') }}" alt="" class="block w-[1224px] max-w-full h-[1px]">

            <!-- Copyright -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-[8px] py-[16px] lg:h-[79px] lg:py-0 text-[14px] opacity-80">
                <p><span class="font-medium">© Copyright {{ date('Y') }} </span><span class="font-bold">BBS Motion</span><span class="font-medium">. Todos los derechos reservados</span></p>
                <a href="https://osole.com.ar/" target="_blank" rel="noopener noreferrer"><span class="font-medium">by </span><span class="font-bold">Osole</span></a>
            </div>
        </div>
    </footer>

    {{-- Newsletter --}}
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const form       = document.getElementById('newsletterForm');
        const emailInput = document.getElementById('newsletterEmail');
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData  = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const origHTML  = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
            fetch(this.action, { method: 'POST', body: formData, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({ icon: 'success', title: '¡Suscripción exitosa!', text: data.message, confirmButtonColor: '#DE2222' });
                        emailInput.value = '';
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#DE2222' });
                    }
                })
                .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Ocurrió un error. Intentá nuevamente.', confirmButtonColor: '#DE2222' }))
                .finally(() => { submitBtn.disabled = false; submitBtn.innerHTML = origHTML; });
        });
    });
    </script>
