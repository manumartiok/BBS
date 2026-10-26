{{-- Panel "Mi Carrito" del usuario público. Se abre desde el botón del carrito del layout web
     y desde "agregar al carrito" de los productos (window.CarritoPublico). --}}
@auth('usuario_publico')
    <div id="carrito-publico-fondo" class="hidden fixed inset-0 bg-black/50 z-[998]"></div>

    <aside id="carrito-publico" aria-label="Mi carrito"
           class="fixed top-0 right-0 z-[999] h-full w-[484px] max-w-full bg-white shadow-[-4px_0_15px_0px_rgba(0,0,0,0.1)] overflow-y-auto translate-x-full transition-transform duration-300"
           style="font-family: 'Montserrat', sans-serif;">
        <div class="px-[21px] pt-[48px] pb-[48px]">

            <div class="flex items-start justify-between gap-[16px]">
                <h2 class="text-[32px] leading-[39px] font-bold text-[#111010]">Mi Carrito</h2>
                <button type="button" id="carrito-publico-cerrar" aria-label="Cerrar carrito" class="text-black hover:opacity-60 text-[32px] leading-none transition-opacity">&times;</button>
            </div>

            {{-- productos --}}
            <div id="carrito-publico-items" class="flex flex-col gap-[24px] mt-[43px]"></div>
            <p id="carrito-publico-vacio" class="hidden mt-[43px] text-[16px] leading-[22px] text-[#5C5C5C]">Tu carrito está vacío.</p>

            {{-- subtotal --}}
            <div class="mt-[45px] border-y border-[#D9D9D9] h-[83px] flex items-center justify-between text-[24px] leading-[22px] font-medium text-[#222]">
                <span>Subtotal</span>
                <span id="carrito-publico-subtotal">$0</span>
            </div>

            <div class="flex justify-end mt-[33px]">
                <button type="button" id="carrito-publico-finalizar"
                        class="p-[16px] bg-black border border-black rounded-[22px] text-[16px] leading-[19px] font-semibold uppercase text-white transition-opacity hover:opacity-80 disabled:opacity-40 disabled:cursor-not-allowed">
                    Finalizar
                </button>
            </div>
        </div>
    </aside>

    <script>
    (function () {
        const URLS = {
            items: @json(route('carrito-publico')),
            agregar: @json(route('carrito-publico.agregar')),
            actualizar: @json(route('carrito-publico.actualizar')),
            eliminar: @json(route('carrito-publico.eliminar')),
        };
        const ICONOS = {
            papelera: @json(asset('assets/img/carrito/papelera.svg')),
            menos: @json(asset('assets/img/productos/menos.svg')),
            mas: @json(asset('assets/img/productos/mas.svg')),
            separador: @json(asset('assets/img/productos/separador-fila.svg')),
        };

        const panel = document.getElementById('carrito-publico');
        const fondo = document.getElementById('carrito-publico-fondo');
        const lista = document.getElementById('carrito-publico-items');
        const vacio = document.getElementById('carrito-publico-vacio');
        const subtotal = document.getElementById('carrito-publico-subtotal');
        const finalizar = document.getElementById('carrito-publico-finalizar');
        const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

        const pesos = n => '$' + Math.round(n).toLocaleString('es-AR');
        const escapar = t => String(t ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

        function pedir(url, datos) {
            return fetch(url, {
                method: datos ? 'POST' : 'GET',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf() },
                body: datos ? JSON.stringify(datos) : undefined,
            }).then(async r => {
                const data = await r.json().catch(() => ({}));
                if (r.status === 401 && data.redirect) { window.location.href = data.redirect; }
                return data;
            });
        }

        function dibujar(estado) {
            if (!estado || !estado.success) return;

            lista.innerHTML = estado.items.map(item => `
                <div class="flex gap-[18px]" data-producto="${item.producto_id}">
                    <div class="flex items-center justify-center flex-shrink-0 w-[145px] h-[145px] p-[6px] border border-[#D9D9D9] rounded-[12px] overflow-hidden">
                        <img src="${escapar(item.foto)}" alt="${escapar(item.nombre)}" class="w-full h-full object-contain">
                    </div>
                    <div class="flex flex-col flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-[8px]">
                            <p class="text-[16px] leading-[22px] font-medium text-black">${escapar(item.nombre)}</p>
                            <button type="button" class="carrito-quitar flex-shrink-0 hover:opacity-60 transition-opacity" aria-label="Quitar del carrito">
                                <img src="${ICONOS.papelera}" alt="" class="w-[24px] h-[24px] max-w-none">
                            </button>
                        </div>
                        <div class="flex items-end justify-between gap-[8px] mt-auto">
                            <div class="relative grid grid-cols-[40px_41px_40px] w-[121px] h-[34px] border border-[#D9D9D9] rounded-[4px]">
                                <button type="button" class="carrito-menos flex items-center justify-center" aria-label="Quitar uno">
                                    <img src="${ICONOS.menos}" alt="" class="w-[11px] h-[1px] max-w-none">
                                </button>
                                <span class="flex items-center justify-center font-medium">${item.cantidad}</span>
                                <button type="button" class="carrito-mas flex items-center justify-center" aria-label="Agregar uno">
                                    <img src="${ICONOS.mas}" alt="" class="w-[11px] h-[11px] max-w-none">
                                </button>
                                <img src="${ICONOS.separador}" alt="" class="absolute left-[40px] top-[-1px] w-[1px] h-[34px] max-w-none pointer-events-none">
                                <img src="${ICONOS.separador}" alt="" class="absolute left-[81px] top-[-1px] w-[1px] h-[34px] max-w-none pointer-events-none">
                            </div>
                            <p class="text-[16px] leading-[22px] font-medium text-[#222] whitespace-nowrap">${pesos(item.subtotal)}</p>
                        </div>
                    </div>
                </div>`).join('');

            vacio.classList.toggle('hidden', estado.items.length > 0);
            subtotal.textContent = pesos(estado.subtotal);
            finalizar.disabled = estado.items.length === 0;

            lista.querySelectorAll('[data-producto]').forEach(fila => {
                const id = fila.dataset.producto;
                const item = estado.items.find(i => String(i.producto_id) === id);
                fila.querySelector('.carrito-quitar').addEventListener('click', () => pedir(URLS.eliminar, { producto_id: id }).then(dibujar));
                fila.querySelector('.carrito-mas').addEventListener('click', () => pedir(URLS.actualizar, { producto_id: id, cantidad: item.cantidad + 1 }).then(dibujar));
                fila.querySelector('.carrito-menos').addEventListener('click', () => {
                    if (item.cantidad > 1) pedir(URLS.actualizar, { producto_id: id, cantidad: item.cantidad - 1 }).then(dibujar);
                });
            });
        }

        function abrir() {
            document.getElementById('mobile-menu')?.classList.add('hidden');
            fondo.classList.remove('hidden');
            panel.classList.remove('translate-x-full');
            document.body.style.overflow = 'hidden';
            pedir(URLS.items).then(dibujar);
        }

        function cerrar() {
            fondo.classList.add('hidden');
            panel.classList.add('translate-x-full');
            document.body.style.overflow = 'auto';
        }

        document.querySelectorAll('[data-abrir-carrito-publico]').forEach(b => b.addEventListener('click', abrir));
        document.getElementById('carrito-publico-cerrar').addEventListener('click', cerrar);
        fondo.addEventListener('click', cerrar);
        document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrar(); });

        // FINALIZAR sigue con la compra en la vista del carrito
        finalizar.addEventListener('click', () => { window.location.href = @json(route('compra.carrito')); });

        // usado por los botones "agregar al carrito" de los productos
        window.CarritoPublico = {
            abrir,
            agregar(productoId, cantidad) {
                return pedir(URLS.agregar, { producto_id: productoId, cantidad }).then(data => {
                    if (data.success) { dibujar(data); abrir(); }
                    return data;
                });
            },
        };
    })();
    </script>
@endauth
