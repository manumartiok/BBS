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
     <!-- Titulo -->
    <title>@yield('title')</title>

    {{-- Metadatos dinámicos --}}
    @php
        // Obtener metadatos según la sección actual
        $currentRoute = request()->route() ? request()->route()->getName() : null;
        $metadato = \App\Models\Metadato::where('seccion', $currentRoute)->first();
    @endphp

    @if($metadato)
        <meta name="description" content="{{ $metadato->descripcion }}">
        <meta name="keywords" content="{{ $metadato->keywords }}">
    @else
        {{-- Metadatos por defecto --}}
        <meta name="description" content="Resortes Generales">
        <meta name="keywords" content="Resortes Generales">
    @endif

    {{-- favicon  --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png')}}">
    <!-- Cargar jQuery primero -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- CSS  -->
    <link rel="stylesheet" href="{{asset('assets/css/style.css')}}">
     {{-- font awesome  --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    {{-- google font  --}}
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap" rel="stylesheet">
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
        $nombreUsuario = $usuarioWeb ? ($usuarioWeb->usuario ?? $usuarioWeb->email) : '';

        // Productos distintos en el carrito pendiente (globito del carrito)
        $cantidadCarrito = $usuarioWeb
            ? count(\App\Models\CarritoPendiente::where('usuario_id', $usuarioWeb->id)->whereNull('cliente_id')->value('items') ?? [])
            : 0;

        // [texto, url, patrón de la ruta activa]
        $menu = [
            ['Productos',          route('web-productos'), 'clientes/productos'],
            ['Mis pedidos',        route('web-pedidos'),   'clientes/pedidos*'],
            ['Mis presupuestos',   route('web-presupuestos'), 'clientes/presupuestos*'],
            ['Cuenta corriente',   route('web-cuenta-corriente'), 'clientes/cuenta-corriente*'],
            ['Info de pagos',      route('web-info'),      'clientes/informacion-pago'],
            ['Canje de puntos',    route('web-canje'),     'clientes/canje-puntos*'],
        ];
    @endphp
    <header class="top-0 left-0 w-full z-50 shadow-[0px_4px_15px_0px_rgba(0,0,0,0.1)] bg-white relative" style="font-family: 'Montserrat', sans-serif;">

        <!-- Barra superior: contacto y usuario -->
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

                <button id="btn-usuario" type="button" class="flex items-center gap-[10px] font-semibold whitespace-nowrap max-w-[220px]">
                    <img src="{{ asset('assets/img/nav/lock.svg') }}" alt="" class="w-[13px] h-[18px] flex-shrink-0">
                    <span class="truncate">{{ $nombreUsuario }}</span>
                </button>
            </div>
        </div>

        <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px]">

            <!-- Fila principal: logo, buscador y carrito -->
            <div id="navbar" class="flex items-center gap-[24px] pt-[14px] pb-[14px] lg:pb-0">

                <!-- Logo -->
                <a href="{{ route('home') }}" class="block w-[140px] sm:w-[184px] h-[51.739px] flex-shrink-0">
                    <img src="{{ $logos->foto_nav }}" alt="Logo" class="w-full h-full object-contain object-left">
                </a>

                <!-- Buscador (desktop) -->
                <form action="{{ route('web-productos') }}" method="GET" class="hidden lg:flex flex-1 h-[50px] items-center gap-[18px] pl-[19px] pr-[24px] bg-white border border-[#D9D9D9] rounded-[25px]">
                    <button type="submit" aria-label="Buscar" class="flex-shrink-0">
                        <img src="{{ asset('assets/img/nav/search.svg') }}" alt="" class="w-[25px] h-[25px]">
                    </button>
                    <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar"
                           class="flex-1 min-w-0 h-full bg-transparent text-[16px] text-black placeholder:text-black focus:outline-none">
                </form>

                <!-- Carrito (desktop) -->
                <a href="{{ route('web-carrito') }}" aria-label="Carrito" class="relative hidden lg:block w-[50px] h-[50px] flex-shrink-0">
                    <img src="{{ asset(Request::is('clientes/carrito') ? 'assets/img/nav/cart-activo.svg' : 'assets/img/nav/cart.svg') }}" alt="" class="w-[50px] h-[50px]">
                    <span id="cart-count" class="absolute left-[37px] top-0 flex items-center justify-center w-[15px] h-[15px] text-[12px] leading-none text-white {{ $cantidadCarrito > 0 ? '' : 'hidden' }}">
                        <img src="{{ asset('assets/img/nav/cart-badge.svg') }}" alt="" class="absolute inset-0 w-[15px] h-[15px] max-w-none">
                        <span class="relative cart-count-num">{{ $cantidadCarrito }}</span>
                    </span>
                </a>

                <!-- Botón Hamburguesa (solo en móvil) -->
                <button id="menu-toggle" type="button" aria-label="Menú" class="lg:hidden text-2xl ml-auto">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>

            <!-- Menú (desktop) -->
            <nav class="hidden lg:flex justify-between lg:ml-[208px] pt-[19px] pb-[10px] text-[16px] leading-[19px] uppercase">
                @foreach ($menu as [$texto, $url, $patron])
                    <a href="{{ $url }}" class="{{ $patron && Request::is($patron) ? 'font-semibold' : '' }}">{{ $texto }}</a>
                @endforeach
            </nav>
        </div>

        <!-- Menú mobile -->
        <div id="mobile-menu" class="hidden lg:hidden absolute top-full left-0 w-full z-40 bg-white text-black shadow-md">
            <div class="flex flex-col items-center gap-4 py-6 px-[5%]">
                <form action="{{ route('web-productos') }}" method="GET" class="w-full max-w-[420px] h-[50px] flex items-center gap-[18px] pl-[19px] pr-[24px] border border-[#D9D9D9] rounded-[25px]">
                    <button type="submit" aria-label="Buscar" class="flex-shrink-0">
                        <img src="{{ asset('assets/img/nav/search.svg') }}" alt="" class="w-[25px] h-[25px]">
                    </button>
                    <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar"
                           class="flex-1 min-w-0 h-full bg-transparent text-[16px] text-black placeholder:text-black focus:outline-none">
                </form>

                @foreach ($menu as [$texto, $url, $patron])
                    <a href="{{ $url }}" class="text-[16px] uppercase {{ $patron && Request::is($patron) ? 'font-semibold' : '' }}">{{ $texto }}</a>
                @endforeach

                <a href="{{ route('web-carrito') }}" class="text-[16px] uppercase">Carrito @if($cantidadCarrito > 0)({{ $cantidadCarrito }})@endif</a>

                <button id="btn-usuario-mobile" type="button" class="text-[16px] font-semibold">{{ $nombreUsuario }}</button>
            </div>
        </div>
    </header>

    @include('components.auth-modals')

    <!-- contenido -->
    <div class="flex-grow">
        @yield('content')
    </div>

    @include('layouts.partials.web-footer')

    <!-- Sweet Alert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- CKEditor 5 FULL BUILD -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/super-build/ckeditor.js"></script>

    @yield('scripts')

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggle = document.getElementById('menu-toggle');
        const mobileMenu = document.getElementById('mobile-menu');

        toggle.addEventListener('click', () => {
            // Mostrar / ocultar el menú móvil
            mobileMenu.classList.toggle('hidden');
        });
    });
    </script>

    @include('components.auth-modal-scripts')
</body>
</html>
