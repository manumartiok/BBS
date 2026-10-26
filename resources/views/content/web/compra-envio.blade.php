@extends('layouts.web')

@section('title', 'Forma de envío')

@section('content')
<div class="w-full text-black" style="font-family: 'Montserrat', sans-serif;">

    {{-- breadcrumb --}}
    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pt-[15px]">
        <p class="text-[12px] leading-[15px]"><a class="font-semibold" href="{{ route('home') }}">Inicio /</a> Carrito</p>
    </div>

    {{-- pasos de la compra --}}
    <div class="mt-[22px]">
        @include('content.web.partials.compra-pasos', ['activo' => 2])
    </div>

    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pt-[39px] pb-[120px]">

        <h1 class="mb-[12px] text-[24px] leading-[29px] font-semibold">Seleccione la forma de envío</h1>

        <div class="grid grid-cols-1 lg:grid-cols-[808fr_392fr] gap-x-[24px] gap-y-[24px] items-start">

            {{-- forma de envío --}}
            <div>
                @include('content.web.partials.forma-envio', [
                    'formId' => 'form-compra-envio',
                    'envioActual' => $envioElegido['envio'] ?? 'mercado_flex',
                    'cpActual' => $envioElegido['codigo_postal'] ?? '',
                    'contacto' => $contacto,
                ])

                <a href="{{ route('compra.carrito') }}"
                   class="inline-flex items-center justify-center mt-[19px] p-[16px] border border-black rounded-[22px] text-[16px] leading-[19px] font-semibold uppercase transition-colors hover:bg-black hover:text-white">
                    Volver
                </a>
            </div>

            {{-- pedido --}}
            <div>
                @include('content.web.partials.compra-pedido')

                <form id="form-compra-envio" class="flex justify-end mt-[24px]">
                    <button type="submit"
                            class="p-[16px] bg-black border border-black rounded-[22px] text-[16px] leading-[19px] font-semibold uppercase text-white transition-opacity hover:opacity-80">
                        Siguiente
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Guarda la forma de envío elegida y sigue con la forma de pago
    document.getElementById('form-compra-envio').addEventListener('submit', function (e) {
        e.preventDefault();

        const envio = document.querySelector('input[name="envio"]:checked')?.value;
        if (!envio) {
            Swal.fire({ icon: 'warning', title: 'Falta la forma de envío', text: 'Elegí una opción de envío para seguir.', confirmButtonColor: '#0D1A35' });
            return;
        }

        fetch(@json(route('compra.envio.guardar')), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({ envio, codigo_postal: document.getElementById('codigo-postal')?.value || '' }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.href = @json(route('compra.pago'));
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'No se pudo guardar la forma de envío.', confirmButtonColor: '#0D1A35' });
            }
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor.', confirmButtonColor: '#0D1A35' }));
    });
</script>
@endsection
