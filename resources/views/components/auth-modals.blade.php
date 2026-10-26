@props([
    'accountUrl' => route('web-productos'),
    'accountLabel' => 'Zona privada',
    'accountVariant' => 'primary',
    'publicUrl' => route('home'),
    'publicLabel' => 'Sección pública',
])

@auth('web_usuario')
    <div id="modal-usuario-logueado" class="hidden fixed inset-0 bg-black/60 z-[999] flex items-center justify-center">
        <div class="bg-white rounded-[12px] border border-[#D9D9D9] w-full max-w-[400px] mx-4 p-8 relative shadow-[0px_4px_15px_0px_rgba(0,0,0,0.1)]" style="font-family: 'Montserrat', sans-serif;">
            <button id="close-modal-usuario" aria-label="Cerrar" class="absolute top-4 right-5 text-black hover:opacity-60 text-2xl leading-none transition-opacity">&times;</button>

            <h2 class="text-[24px] leading-[29px] font-semibold text-black mb-2">Mi cuenta</h2>
            <p class="text-[16px] text-[#5C5C5C] mb-6 truncate">{{ auth('web_usuario')->user()->usuario ?? auth('web_usuario')->user()->email }}</p>

            <div class="flex flex-col gap-[16px]">
                <a href="{{ $accountUrl }}"
                   class="w-full h-[50px] flex justify-center items-center rounded-[25px] text-[16px] font-semibold transition {{ $accountVariant === 'outline' ? 'border border-[#0D1A35] text-[#0D1A35] hover:bg-[#0D1A35] hover:text-white' : 'bg-[#0D1A35] text-white hover:bg-black' }}">
                    {{ $accountLabel }}
                </a>
                <a href="{{ $publicUrl }}"
                   class="w-full h-[50px] flex justify-center items-center rounded-[25px] border border-[#D9D9D9] text-black text-[16px] font-semibold hover:border-black transition">
                    {{ $publicLabel }}
                </a>
                <form action="{{ route('web.logout') }}" method="POST" class="text-center">
                    @csrf
                    <button type="submit" class="text-[16px] font-semibold text-black underline hover:opacity-60 transition-opacity">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
@else
    <div id="modal-login" style="display:none" class="fixed inset-0 bg-black/60 z-[999] flex items-center justify-center">
        <div class="bg-white rounded-[12px] border border-[#D9D9D9] w-full max-w-[400px] mx-4 p-8 relative shadow-[0px_4px_15px_0px_rgba(0,0,0,0.1)] max-h-[90vh] overflow-y-auto">
            <button id="close-modal" aria-label="Cerrar" class="absolute top-4 right-5 text-black hover:opacity-60 text-2xl leading-none transition-opacity">&times;</button>

            <div id="login-view" style="font-family: 'Montserrat', sans-serif;">
                <h2 class="text-[24px] leading-[29px] font-semibold text-black mb-6">Iniciar sesión</h2>
                <form id="login-form" method="POST">
                    @csrf
                    <div class="flex flex-col gap-[16px] mb-6">
                        <div>
                            <label class="block text-[12px] font-semibold text-black mb-[6px] px-[4px]">Usuario</label>
                            <input type="text" name="usuario" required placeholder="Tu usuario o email"
                                class="w-full h-[50px] border border-[#D9D9D9] rounded-[25px] px-[24px] text-[16px] text-black placeholder:text-[#5C5C5C] focus:outline-none focus:border-[#0D1A35] transition">
                        </div>
                        <div>
                            <label class="block text-[12px] font-semibold text-black mb-[6px] px-[4px]">Contraseña</label>
                            <input type="password" name="contraseña" required placeholder="********"
                                class="w-full h-[50px] border border-[#D9D9D9] rounded-[25px] px-[24px] text-[16px] text-black placeholder:text-[#5C5C5C] focus:outline-none focus:border-[#0D1A35] transition">
                        </div>
                    </div>
                    <button type="submit"
                        class="w-full h-[50px] bg-[#0D1A35] text-white rounded-[25px] text-[16px] font-semibold hover:bg-black transition">
                        Iniciar sesión
                    </button>
                </form>
            </div>

            <div id="register-view" class="hidden">
                <h2 class="text-[22px] font-[500] text-black mb-5">Crear cuenta</h2>
                <form id="register-form" method="POST">
                    @csrf
                    <div class="flex flex-col gap-[12px] mb-5 max-h-[400px] overflow-y-auto pr-1">
                        <div>
                            <label class="block text-[13px] text-gray-500 mb-[5px]">Nombre completo</label>
                            <input type="text" name="usuario" required placeholder="Juan Perez"
                                class="w-full h-[42px] border border-gray-200 rounded-[10px] px-3 text-[14px] focus:outline-none focus:border-[#DE2222] transition">
                        </div>
                        <div>
                            <label class="block text-[13px] text-gray-500 mb-[5px]">Email</label>
                            <input type="email" name="email" required placeholder="tu@email.com"
                                class="w-full h-[42px] border border-gray-200 rounded-[10px] px-3 text-[14px] focus:outline-none focus:border-[#DE2222] transition">
                        </div>
                        <div class="grid grid-cols-2 gap-[10px]">
                            <div>
                                <label class="block text-[13px] text-gray-500 mb-[5px]">Contrasena</label>
                                <input type="password" name="contraseña" required placeholder="********"
                                    class="w-full h-[42px] border border-gray-200 rounded-[10px] px-3 text-[14px] focus:outline-none focus:border-[#DE2222] transition">
                            </div>
                            <div>
                                <label class="block text-[13px] text-gray-500 mb-[5px]">Confirmar</label>
                                <input type="password" name="contraseña_confirmation" required placeholder="********"
                                    class="w-full h-[42px] border border-gray-200 rounded-[10px] px-3 text-[14px] focus:outline-none focus:border-[#DE2222] transition">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[13px] text-gray-500 mb-[5px]">Empresa</label>
                            <input type="text" name="empresa" placeholder="Nombre de empresa"
                                class="w-full h-[42px] border border-gray-200 rounded-[10px] px-3 text-[14px] focus:outline-none focus:border-[#DE2222] transition">
                        </div>
                        <div>
                            <label class="block text-[13px] text-gray-500 mb-[5px]">CUIT</label>
                            <input type="text" name="cuit" placeholder="XX-XXXXXXXX-X"
                                class="w-full h-[42px] border border-gray-200 rounded-[10px] px-3 text-[14px] focus:outline-none focus:border-[#DE2222] transition">
                        </div>
                        <div class="grid grid-cols-2 gap-[10px]">
                            <div>
                                <label class="block text-[13px] text-gray-500 mb-[5px]">Telefono</label>
                                <input type="text" name="telefono"
                                    class="w-full h-[42px] border border-gray-200 rounded-[10px] px-3 text-[14px] focus:outline-none focus:border-[#DE2222] transition">
                            </div>
                            <div>
                                <label class="block text-[13px] text-gray-500 mb-[5px]">Codigo postal</label>
                                <input type="text" name="codigo_postal" placeholder="CP"
                                    class="w-full h-[42px] border border-gray-200 rounded-[10px] px-3 text-[14px] focus:outline-none focus:border-[#DE2222] transition">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[13px] text-gray-500 mb-[5px]">Domicilio</label>
                            <input type="text" name="domicilio" placeholder="Calle y numero"
                                class="w-full h-[42px] border border-gray-200 rounded-[10px] px-3 text-[14px] focus:outline-none focus:border-[#DE2222] transition">
                        </div>
                        <div class="grid grid-cols-2 gap-[10px]">
                            <div>
                                <label class="block text-[13px] text-gray-500 mb-[5px]">Localidad</label>
                                <input type="text" name="localidad"
                                    class="w-full h-[42px] border border-gray-200 rounded-[10px] px-3 text-[14px] focus:outline-none focus:border-[#DE2222] transition">
                            </div>
                            <div>
                                <label class="block text-[13px] text-gray-500 mb-[5px]">Provincia</label>
                                <input type="text" name="provincia"
                                    class="w-full h-[42px] border border-gray-200 rounded-[10px] px-3 text-[14px] focus:outline-none focus:border-[#DE2222] transition">
                            </div>
                        </div>
                    </div>
                    <button type="submit"
                        class="w-full h-[46px] bg-[#DE2222] text-white rounded-[14px] text-[15px] font-semibold mb-[10px] hover:bg-[#c01c1c] transition">
                        Registrarse
                    </button>
                </form>
                <button id="btn-volver-login"
                    class="w-full h-[46px] bg-white text-[#DE2222] border-2 border-[#DE2222] rounded-[14px] text-[15px] font-semibold hover:bg-red-50 transition">
                    Volver al login
                </button>
            </div>
        </div>
    </div>
@endauth
