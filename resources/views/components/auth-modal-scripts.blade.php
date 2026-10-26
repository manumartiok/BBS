@auth('web_usuario')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('modal-usuario-logueado');
        const closeBtn = document.getElementById('close-modal-usuario');
        const btnMobile = document.getElementById('btn-usuario-mobile');
        const btnDesktop = document.getElementById('btn-usuario');
        const mobileMenu = document.getElementById('mobile-menu');

        const openModal = () => {
            if (!modal) return;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        };
        const closeModal = () => {
            if (!modal) return;
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        };

        if (btnMobile) {
            btnMobile.addEventListener('click', () => {
                openModal();
                if (mobileMenu) mobileMenu.classList.add('hidden');
            });
        }
        if (btnDesktop) btnDesktop.addEventListener('click', openModal);
        document.querySelectorAll('[data-abrir-modal-usuario]').forEach(el => el.addEventListener('click', openModal));
        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (modal) modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });
    });
    </script>
@else
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('modal-login');
        const btnAbrir = document.getElementById('btn-usuario');
        const btnAbrirMobile = document.getElementById('btn-usuario-mobile');
        const btnCerrar = document.getElementById('close-modal');
        const mobileMenu = document.getElementById('mobile-menu');

        const open = () => {
            if (!modal) return;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        };
        const close = () => {
            if (!modal) return;
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        };

        if (btnAbrir) btnAbrir.addEventListener('click', open);
        if (btnAbrirMobile) {
            btnAbrirMobile.addEventListener('click', () => {
                if (mobileMenu) mobileMenu.classList.add('hidden');
                open();
            });
        }
        document.querySelectorAll('[data-abrir-login]').forEach(el => el.addEventListener('click', e => {
            e.preventDefault();
            open();
        }));
        if (btnCerrar) btnCerrar.addEventListener('click', close);
        if (modal) modal.addEventListener('click', e => { if (e.target === modal) close(); });
        document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });

        document.getElementById('btn-crear-cuenta')?.addEventListener('click', () => {
            document.getElementById('login-view')?.classList.add('hidden');
            document.getElementById('register-view')?.classList.remove('hidden');
        });
        document.getElementById('btn-volver-login')?.addEventListener('click', () => {
            document.getElementById('register-view')?.classList.add('hidden');
            document.getElementById('login-view')?.classList.remove('hidden');
        });

        document.getElementById('login-form')?.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const btn = this.querySelector('button[type="submit"]');
            const orig = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

            fetch('{{ route("web.login") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Login exitoso', showConfirmButton: false, timer: 1500 })
                        .then(() => window.location.href = data.redirect);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#0D1A35' });
                    btn.disabled = false;
                    btn.innerHTML = orig;
                }
            })
            .catch(() => {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Ocurrio un error. Intenta nuevamente.', confirmButtonColor: '#0D1A35' });
                btn.disabled = false;
                btn.innerHTML = orig;
            });
        });

        document.getElementById('register-form')?.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const btn = this.querySelector('button[type="submit"]');
            const orig = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

            fetch('{{ route("web.register") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Registro exitoso', text: data.message, showConfirmButton: false, timer: 2000 })
                        .then(() => window.location.href = data.redirect);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#DE2222' });
                    btn.disabled = false;
                    btn.innerHTML = orig;
                }
            })
            .catch(() => {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Ocurrio un error. Intenta nuevamente.', confirmButtonColor: '#DE2222' });
                btn.disabled = false;
                btn.innerHTML = orig;
            });
        });

        @if(session('open_login_modal'))
            open();
            Swal.fire({
                icon: 'info',
                title: 'Acceso restringido',
                text: 'Debes iniciar sesion para acceder a esta seccion',
                confirmButtonColor: '#DE2222'
            });
        @endif
    });
    </script>
@endauth

<style>
    #modal-login,
    #modal-usuario-logueado { animation: fadeInRG 0.2s ease-in-out; }
    @keyframes fadeInRG { from { opacity: 0; } to { opacity: 1; } }
    #modal-login > div,
    #modal-usuario-logueado > div { animation: slideInRG 0.25s ease-out; }
    @keyframes slideInRG { from { transform: translateY(-16px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>
