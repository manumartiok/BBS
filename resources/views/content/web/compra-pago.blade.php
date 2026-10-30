@extends('layouts.web')

@section('title', 'Forma de pago')

@section('content')
@php
    // [valor, texto]
    $opciones = [
        ['mercado_pago',  'Mercado pago'],
        ['transferencia', 'Transferencia bancaria'],
        ['efectivo',      'Efectivo (únicamente retiro en local)'],
    ];
@endphp

<div class="w-full text-black" style="font-family: 'Montserrat', sans-serif;">

    {{-- breadcrumb --}}
    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pt-[15px]">
        <p class="text-[12px] leading-[15px]"><a class="font-semibold" href="{{ route('home') }}">Inicio /</a> Carrito</p>
    </div>

    {{-- pasos de la compra --}}
    <div class="mt-[22px]">
        @include('content.web.partials.compra-pasos', ['activo' => 3])
    </div>

    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pt-[39px] pb-[120px]">

        <h1 class="mb-[12px] text-[24px] leading-[29px] font-semibold">Seleccione la forma de pago</h1>

        <div class="grid grid-cols-1 lg:grid-cols-[808fr_392fr] gap-x-[24px] gap-y-[24px] items-start">

            {{-- forma de pago --}}
            <div>
                <section class="border border-[#D9D9D9] rounded-[12px] pt-[27px] pb-[13px] pl-[18px] pr-[18px]">
                    @foreach($opciones as [$valor, $texto])
                        @php
                            $deshabilitada = $valor === 'efectivo' && !$retiroLocal;
                        @endphp
                        <label class="flex items-center gap-[11px] h-[22px] {{ $loop->first ? '' : 'mt-[15px]' }} text-[20px] leading-[22px] text-[#222] {{ $deshabilitada ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer' }}"
                               @if($deshabilitada) title="Disponible solo con retiro en local" @endif>
                            <input form="form-compra-pago" type="radio" name="pago" value="{{ $valor }}" class="sr-only peer"
                                   @checked($pagoActual === $valor) @disabled($deshabilitada)>
                            <img src="{{ asset('assets/img/canje/radio.svg') }}" alt="" class="w-[14px] h-[14px] max-w-none peer-checked:hidden">
                            <span class="relative hidden peer-checked:block w-[14px] h-[14px]">
                                <img src="{{ asset('assets/img/canje/radio.svg') }}" alt="" class="absolute inset-0 w-[14px] h-[14px] max-w-none">
                                <img src="{{ asset('assets/img/canje/radio-punto.svg') }}" alt="" class="absolute left-[2px] top-[2px] w-[10px] h-[10px] max-w-none">
                            </span>
                            <span>{{ $texto }}</span>
                        </label>
                    @endforeach

                    <div class="mt-[8px] ml-[43px] text-[13px] leading-[22px] text-[#222] whitespace-pre-line">{{ \App\Models\Contacto::textoRetiroLocal() }}</div>
                </section>

                <a href="{{ route('compra.envio') }}"
                   class="inline-flex items-center justify-center mt-[30px] p-[16px] border border-black rounded-[22px] text-[16px] leading-[19px] font-semibold uppercase transition-colors hover:bg-black hover:text-white">
                    Volver
                </a>
            </div>

            {{-- pedido y envío --}}
            <div>
                @include('content.web.partials.compra-pedido')

                <section class="mt-[24px] border border-[#D9D9D9] rounded-[12px] px-[13px]">
                    <h2 class="h-[61px] pt-[27px] border-b border-[#D9D9D9] text-[24px] leading-[29px] font-semibold">Forma de envío</h2>
                    <div class="flex items-center justify-between gap-[8px] pt-[20px] pb-[19px] text-[20px] leading-[22px] font-medium">
                        <span>{{ $envioTexto }}</span>
                        <a href="{{ route('compra.envio') }}" aria-label="Cambiar la forma de envío" class="flex-shrink-0 hover:opacity-60 transition-opacity">
                            <img src="{{ asset('assets/img/canje/editar.svg') }}" alt="" class="w-[18px] h-[18px] max-w-none">
                        </a>
                    </div>
                </section>

                <form id="form-compra-pago" class="flex justify-end mt-[24px]">
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
    // Guarda la forma de pago elegida y sigue con la confirmación del pedido
    document.getElementById('form-compra-pago').addEventListener('submit', function (e) {
        e.preventDefault();

        const pago = document.querySelector('input[name="pago"]:checked')?.value;
        if (!pago) {
            Swal.fire({ icon: 'warning', title: 'Falta la forma de pago', text: 'Elegí una forma de pago para seguir.', confirmButtonColor: '#0D1A35' });
            return;
        }

        fetch(@json(route('compra.pago.guardar')), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({ pago }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                window.location.href = @json(route('compra.finalizar'));
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'No se pudo guardar la forma de pago.', confirmButtonColor: '#0D1A35' });
            }
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'No se pudo conectar con el servidor.', confirmButtonColor: '#0D1A35' }));
    });
</script>
@endsection
