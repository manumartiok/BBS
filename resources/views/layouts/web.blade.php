@php
    $contacto_contenido = App\Models\Contacto::first();
    $logos = App\Models\Logo::first(); 
@endphp

<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>

    @php
        $currentRoute = request()->route() ? request()->route()->getName() : null;
        $metadato = \App\Models\Metadato::where('seccion', $currentRoute)->first();
    @endphp

    @if($metadato)
        <meta name="description" content="{{ $metadato->descripcion }}">
        <meta name="keywords" content="{{ $metadato->keywords }}">
    @else
        <meta name="description" content="Resortes Generales">
        <meta name="keywords" content="Resortes Generales">
    @endif

    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png')}}">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="h-full flex flex-col">

    @if($contacto_contenido->whatssap)
    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contacto_contenido->whatsspp) }}" target="_blank" class="fixed bottom-[40px] right-[25px] z-[900]">
        <img src="/assets/img/whatsapp.png" alt="Chat" class=" cursor-pointer">
    </a>
    @endif

    <!-- navbar -->
    @php
        $usuarioWeb = auth('web_usuario')->user();
        $tituloUsuario = $usuarioWeb ? ($usuarioWeb->usuario ?? $usuarioWeb->email) : 'Iniciar sesión';

        // [texto, url, patrón de la ruta activa]
        $menu = [
            ['Productos', route('productos'), 'productos*'],
            ['Novedades', route('novedades'), 'novedades*'],
            ['Catálogo',  Route::has('catalogo') ? route('catalogo') : '#', 'catalogo*'],
            ['Nosotros',  route('nosotros'), 'nosotros'],
            ['Contacto',  route('contacto'), 'contacto'],
        ];

        // Home: la barra va transparente sobre el hero, con textos e íconos blancos
        $navbarHome = $navbarHome ?? false;

        // Estás en el acceso de usuarios públicos: el ícono de usuario va en azul
        $accesoActivo = Request::is('acceso');

        // Estás en el proceso de compra: el ícono del carrito va en azul
        $carritoActivo = Request::is('compra/*');
        $usuarioPublico = auth('usuario_publico')->user();
    @endphp
    <header class="top-0 left-0 w-full z-50 shadow-[0px_4px_15px_0px_rgba(0,0,0,0.1)] {{ $navbarHome ? 'absolute text-white' : ($navbarClass ?? 'bg-white relative') }}" style="font-family: 'Montserrat', sans-serif;">

        <!-- Barra superior: contacto y zona privada -->
        <div class="bg-[#0D1A35] text-white text-[14px] md:text-[16px] h-[40px]">
            <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] h-full flex items-center justify-between">
                <div class="flex items-center">
                    @if($contacto_contenido->whatssap)
                    <a href="https://wa.me/{{ preg_replace('/\D+/', '', $contacto_contenido->whatssap) }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-[9px] whitespace-nowrap mr-[24px] lg:mr-0 lg:w-[208px]">
                        <img src="{{ asset('assets/img/nav/whatsapp.svg') }}" alt="" class="w-[19.9042px] h-[20px] flex-shrink-0">
                        <span>{{ $contacto_contenido->whatssap }}</span>
                    </a>
                    @endif
                    @if($contacto_contenido->mail)
                    <a href="mailto:{{ $contacto_contenido->mail }}" class="hidden md:flex items-center gap-[9px] whitespace-nowrap">
                        <img src="{{ asset('assets/img/nav/mail.svg') }}" alt="" class="w-[20px] h-[14px] flex-shrink-0">
                        <span>{{ $contacto_contenido->mail }}</span>
                    </a>
                    @endif
                </div>

                @if($usuarioWeb)
                    {{-- con sesión muestra el nombre y abre el modal de cuenta --}}
                    <button type="button" data-abrir-modal-usuario class="flex items-center gap-[10px] font-semibold whitespace-nowrap max-w-[220px]">
                        <img src="{{ asset('assets/img/nav/lock.svg') }}" alt="" class="w-[13px] h-[18px] flex-shrink-0">
                        <span class="truncate">{{ $tituloUsuario }}</span>
                    </button>
                @else
                    {{-- sin sesión abre el login (la redirección a la zona privada la hace el login) --}}
                    <a href="{{ route('web-productos') }}" data-abrir-login class="flex items-center gap-[10px] font-semibold uppercase whitespace-nowrap">
                        <img src="{{ asset('assets/img/nav/lock.svg') }}" alt="" class="w-[13px] h-[18px] flex-shrink-0">
                        Zona privada
                    </a>
                @endif
            </div>
        </div>

        @if($navbarHome)
            <div class="absolute top-[40px] left-0 w-full h-[114px] opacity-70 pointer-events-none" style="background: radial-gradient(683px 57px at 50% 50%, rgba(0,0,0,0.5), #000);"></div>
        @endif

        <div class="relative max-w-[1366px] mx-auto px-[5%] md:px-[71px]">

            <!-- Fila principal: logo, buscador, usuario y carrito -->
            <div id="navbar" class="flex items-center gap-[24px] pt-[14px] pb-[14px] lg:pb-0">

                <!-- Logo -->
                <a href="{{ route('home') }}" class="block w-[140px] sm:w-[184px] h-[51.739px] flex-shrink-0">
                    <img src="{{ $logos->foto_nav }}" alt="Logo" class="w-full h-full object-contain object-left">
                </a>

                <!-- Buscador (desktop) -->
                <form action="{{ route('productos') }}" method="GET" class="hidden lg:flex flex-1 h-[50px] items-center gap-[18px] pl-[19px] pr-[24px] bg-white border border-[#D9D9D9] rounded-[25px]">
                    <button type="submit" aria-label="Buscar" class="flex-shrink-0">
                        <img src="{{ asset('assets/img/nav/search.svg') }}" alt="" class="w-[25px] h-[25px]">
                    </button>
                    <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar"
                           class="flex-1 min-w-0 h-full bg-transparent text-[16px] text-black placeholder:text-black focus:outline-none">
                </form>

                <!-- Usuario y carrito (desktop) -->
                <div class="hidden lg:flex items-center gap-[24px] flex-shrink-0">
                    {{-- usuario público (distinto de la zona privada): sin sesión lleva al login/registro; con sesión abre su cuenta.
                         Va en azul cuando se está en el acceso. --}}
                    @if($usuarioPublico)
                        <button type="button" data-abrir-modal-publico title="{{ $usuarioPublico->nombre }}" aria-label="Mi cuenta" class="relative flex items-center justify-center w-[50px] h-[50px]">
                            <img src="{{ asset('assets/img/nav/user-circle.svg') }}" alt="" class="absolute inset-0 w-[50px] h-[50px]">
                            <img src="{{ asset($navbarHome ? 'assets/img/nav/user-home.svg' : 'assets/img/nav/user.svg') }}" alt="" class="relative w-[17px] h-[24px]">
                        </button>
                    @else
                        <a href="{{ route('web-acceso') }}" title="Iniciar sesión / Registrarse" aria-label="Iniciar sesión o registrarse" class="relative flex items-center justify-center w-[50px] h-[50px]">
                            <img src="{{ asset($accesoActivo ? 'assets/img/nav/user-activo-circle.svg' : 'assets/img/nav/user-circle.svg') }}" alt="" class="absolute inset-0 w-[50px] h-[50px]">
                            <img src="{{ asset($accesoActivo ? 'assets/img/nav/user-activo.svg' : ($navbarHome ? 'assets/img/nav/user-home.svg' : 'assets/img/nav/user.svg')) }}" alt="" class="relative w-[17px] h-[24px]">
                        </a>
                    @endif
                    {{-- carrito público: con sesión abre el panel; sin sesión manda al registro/login público --}}
                    @if($usuarioPublico)
                        <button type="button" data-abrir-carrito-publico aria-label="Carrito" class="block w-[50px] h-[50px]">
                            <img src="{{ asset($carritoActivo ? 'assets/img/nav/cart-activo.svg' : ($navbarHome ? 'assets/img/nav/cart-home.svg' : 'assets/img/nav/cart.svg')) }}" alt="" class="w-[50px] h-[50px]">
                        </button>
                    @else
                        <a href="{{ route('web-acceso') }}" aria-label="Carrito" class="block w-[50px] h-[50px]">
                            <img src="{{ asset($navbarHome ? 'assets/img/nav/cart-home.svg' : 'assets/img/nav/cart.svg') }}" alt="" class="w-[50px] h-[50px]">
                        </a>
                    @endif
                </div>

                <!-- Botón Hamburguesa (solo en móvil) -->
                <button id="menu-toggle" type="button" aria-label="Menú" class="lg:hidden text-2xl ml-auto">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>

            <!-- Menú (desktop) -->
            <nav class="hidden lg:flex justify-between lg:ml-[208px] pt-[19px] pb-[10px] text-[16px] leading-[19px] uppercase">
                @foreach ($menu as [$texto, $url, $patron])
                    <a href="{{ $url }}" class="{{ Request::is($patron) ? 'font-semibold' : '' }}">{{ $texto }}</a>
                @endforeach
            </nav>
        </div>

        <!-- Menú mobile -->
        <div id="mobile-menu" class="hidden lg:hidden absolute top-full left-0 w-full z-40 bg-white text-black shadow-md">
            <div class="flex flex-col items-center gap-4 py-6 px-[5%]">
                <form action="{{ route('productos') }}" method="GET" class="w-full max-w-[420px] h-[50px] flex items-center gap-[18px] pl-[19px] pr-[24px] border border-[#D9D9D9] rounded-[25px]">
                    <button type="submit" aria-label="Buscar" class="flex-shrink-0">
                        <img src="{{ asset('assets/img/nav/search.svg') }}" alt="" class="w-[25px] h-[25px]">
                    </button>
                    <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar"
                           class="flex-1 min-w-0 h-full bg-transparent text-[16px] text-black placeholder:text-black focus:outline-none">
                </form>

                @foreach ($menu as [$texto, $url, $patron])
                    <a href="{{ $url }}" class="text-[16px] uppercase {{ Request::is($patron) ? 'font-semibold' : '' }}">{{ $texto }}</a>
                @endforeach

                <div class="flex items-center gap-[24px]">
                    @if($usuarioPublico)
                        <button type="button" data-abrir-modal-publico title="{{ $usuarioPublico->nombre }}" aria-label="Mi cuenta" class="relative flex items-center justify-center w-[50px] h-[50px]">
                            <img src="{{ asset('assets/img/nav/user-circle.svg') }}" alt="" class="absolute inset-0 w-[50px] h-[50px]">
                            <img src="{{ asset('assets/img/nav/user.svg') }}" alt="" class="relative w-[17px] h-[24px]">
                        </button>
                    @else
                        <a href="{{ route('web-acceso') }}" title="Iniciar sesión / Registrarse" aria-label="Iniciar sesión o registrarse" class="relative flex items-center justify-center w-[50px] h-[50px]">
                            <img src="{{ asset($accesoActivo ? 'assets/img/nav/user-activo-circle.svg' : 'assets/img/nav/user-circle.svg') }}" alt="" class="absolute inset-0 w-[50px] h-[50px]">
                            <img src="{{ asset($accesoActivo ? 'assets/img/nav/user-activo.svg' : 'assets/img/nav/user.svg') }}" alt="" class="relative w-[17px] h-[24px]">
                        </a>
                    @endif
                    @if($usuarioPublico)
                        <button type="button" data-abrir-carrito-publico aria-label="Carrito" class="block w-[50px] h-[50px]">
                            <img src="{{ asset($carritoActivo ? 'assets/img/nav/cart-activo.svg' : 'assets/img/nav/cart.svg') }}" alt="" class="w-[50px] h-[50px]">
                        </button>
                    @else
                        <a href="{{ route('web-acceso') }}" aria-label="Carrito" class="block w-[50px] h-[50px]">
                            <img src="{{ asset('assets/img/nav/cart.svg') }}" alt="" class="w-[50px] h-[50px]">
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </header>

    @include('components.auth-modals')
    @include('components.modal-usuario-publico')
    @include('components.carrito-publico')

    <!-- ===================== CONTENIDO ===================== -->
    <div class="flex-grow">
        @yield('content')
    </div>

    @include('layouts.partials.web-footer')

    <!-- Sweet Alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/super-build/ckeditor.js"></script>

    @yield('scripts')

    {{-- ===================== SCRIPTS ===================== --}}

    {{-- Toggle menú móvil --}}
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggle     = document.getElementById('menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');
        toggle.addEventListener('click', () => mobileMenu.classList.toggle('hidden'));
    });
    </script>

    @if(session('acceso_ok') || session('compra_ok'))
        <script>
            Swal.fire({ icon: 'success', title: @json(session('acceso_ok') ?? session('compra_ok')), showConfirmButton: false, timer: 2600 });
        </script>
    @endif

    @include('components.auth-modal-scripts')

</body>
</html>