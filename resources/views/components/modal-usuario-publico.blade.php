{{-- Cuenta del usuario público (el de la zona pública, no el cliente de la zona privada) --}}
@auth('usuario_publico')
    <div id="modal-usuario-publico" class="hidden fixed inset-0 bg-black/60 z-[999] flex items-center justify-center">
        <div class="bg-white rounded-[12px] border border-[#D9D9D9] w-full max-w-[400px] mx-4 p-8 relative shadow-[0px_4px_15px_0px_rgba(0,0,0,0.1)]" style="font-family: 'Montserrat', sans-serif;">
            <button type="button" id="close-modal-usuario-publico" aria-label="Cerrar" class="absolute top-4 right-5 text-black hover:opacity-60 text-2xl leading-none transition-opacity">&times;</button>

            <h2 class="text-[24px] leading-[29px] font-semibold text-black mb-2">Mi cuenta</h2>
            <p class="text-[16px] text-black truncate">{{ auth('usuario_publico')->user()->nombre }}</p>
            <p class="text-[16px] text-[#5C5C5C] mb-6 truncate">{{ auth('usuario_publico')->user()->email }}</p>

            <form action="{{ route('acceso.logout') }}" method="POST">
                @csrf
                <button type="submit"
                        class="w-full h-[50px] flex justify-center items-center rounded-[25px] bg-[#0D1A35] text-white text-[16px] font-semibold hover:bg-black transition">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('modal-usuario-publico');
        const abrir = () => { modal.classList.remove('hidden'); document.body.style.overflow = 'hidden'; };
        const cerrar = () => { modal.classList.add('hidden'); document.body.style.overflow = 'auto'; };

        document.querySelectorAll('[data-abrir-modal-publico]').forEach(el => el.addEventListener('click', () => {
            document.getElementById('mobile-menu')?.classList.add('hidden');
            abrir();
        }));
        document.getElementById('close-modal-usuario-publico').addEventListener('click', cerrar);
        modal.addEventListener('click', e => { if (e.target === modal) cerrar(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrar(); });
    });
    </script>
@endauth
