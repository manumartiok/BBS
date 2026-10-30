@extends('layouts.master')

@section('title', 'Editar pedido')

@section('content')

<div>
    <h3 class="text-[20px] font-bold text-gray-500">Editar Pedido</h3>
    <hr class="mx-6">
</div>

<div class="mx-20 pt-6" id="app">
    <div class="border rounded p-4 bg-white">
        <div>
            <h3 class="text-[18px] font-semibold text-gray-600">Pedido #{{ str_pad($pedido->id, 8, '0', STR_PAD_LEFT) }}</h3>
            @php($cliente = $pedido->cliente ?: $pedido->usuario)
            <p class="text-sm text-gray-500 mt-1">
                {{ $cliente?->empresa ?: ($cliente?->usuario ?: 'Usuario eliminado') }}
                @if($cliente?->email) - {{ $cliente->email }} @endif
            </p>
        </div>
        <hr class="my-3">

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <strong>Errores:</strong>
                <ul class="mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if($pedido->pago_estado)
            <div class="border rounded p-4 mb-4 text-sm text-gray-600">
                <span class="text-xs text-gray-400 uppercase">Pago con Mercado Pago</span>
                <p class="mt-1">
                    <span class="{{ $pedido->pago_estado_badge }} text-white text-xs px-2 py-1 rounded">{{ $pedido->pago_estado_texto }}</span>
                    @if($pedido->pagado_at) el {{ $pedido->pagado_at->format('d/m/Y H:i') }} @endif
                </p>
                @if($pedido->mp_payment_id)
                    <p class="text-xs text-gray-400 mt-1">Pago de Mercado Pago #{{ $pedido->mp_payment_id }} ({{ $pedido->mp_status_detail }})</p>
                @endif
            </div>
        @endif

        <form method="POST" action="{{ route('adm.pedidos-historico-update') }}">
            @csrf
            <input type="hidden" name="pedido_id" value="{{ $pedido->id }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2" for="fecha_entrega">Fecha de entrega</label>
                    <input
                        type="date"
                        id="fecha_entrega"
                        name="fecha_entrega"
                        value="{{ old('fecha_entrega', $pedido->fecha_entrega?->format('Y-m-d')) }}"
                        class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2" for="estado">Estado</label>
                    <select
                        id="estado"
                        name="estado"
                        class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        @foreach(\App\Models\Pedido::ESTADOS as $valor => [$texto])
                            <option value="{{ $valor }}" @selected(old('estado', $pedido->estado) === $valor)>{{ $texto }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-gray-700 font-medium mb-2" for="guia_transporte">N° guía de transporte</label>
                    <input
                        type="text"
                        id="guia_transporte"
                        name="guia_transporte"
                        maxlength="100"
                        value="{{ old('guia_transporte', $pedido->guia_transporte) }}"
                        class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>
            </div>

            @php($desgloseIva = isset($pedido->totales['iva_21']))
            <div class="grid grid-cols-1 {{ $desgloseIva ? 'md:grid-cols-4' : 'md:grid-cols-3' }} gap-4 mt-6">
                <div class="border rounded p-4">
                    <span class="text-xs text-gray-400 uppercase">Subtotal</span>
                    <p class="text-lg font-semibold">$ {{ number_format($pedido->subtotal, 2, ',', '.') }}</p>
                </div>
                @if($desgloseIva)
                    <div class="border rounded p-4">
                        <span class="text-xs text-gray-400 uppercase">IVA 10,5%</span>
                        <p class="text-lg font-semibold">$ {{ number_format($pedido->totales['iva_105'] ?? 0, 2, ',', '.') }}</p>
                    </div>
                    <div class="border rounded p-4">
                        <span class="text-xs text-gray-400 uppercase">IVA 21%</span>
                        <p class="text-lg font-semibold">$ {{ number_format($pedido->totales['iva_21'], 2, ',', '.') }}</p>
                    </div>
                @else
                    <div class="border rounded p-4">
                        <span class="text-xs text-gray-400 uppercase">IVA</span>
                        <p class="text-lg font-semibold">$ {{ number_format($pedido->iva, 2, ',', '.') }}</p>
                    </div>
                @endif
                <div class="border rounded p-4">
                    <span class="text-xs text-gray-400 uppercase">Total</span>
                    <p class="text-lg font-semibold">$ {{ number_format($pedido->total, 2, ',', '.') }}</p>
                </div>
            </div>

            <div class="mt-6">
                <h4 class="font-semibold text-gray-700 mb-3">Items del pedido</h4>
                <div class="overflow-x-auto border rounded">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-100 text-gray-700 text-sm uppercase">
                            <tr>
                                <th class="px-3 py-2 text-left">Codigo</th>
                                <th class="px-3 py-2 text-left">Producto</th>
                                <th class="px-3 py-2 text-right">Cantidad</th>
                                <th class="px-3 py-2 text-right">Precio</th>
                                <th class="px-3 py-2 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600">
                            @foreach($pedido->items as $item)
                                <tr class="border-t">
                                    <td class="px-3 py-2">{{ $item->codigo ?: '-' }}</td>
                                    <td class="px-3 py-2">{{ $item->nombre ?: '-' }} {{ $item->seccion ?: '' }}</td>
                                    <td class="px-3 py-2 text-right">{{ number_format($item->cantidad, 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-right">$ {{ number_format($item->precio_con_descuento, 2, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-right">$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if($pedido->mensaje)
                <div class="mt-6">
                    <h4 class="font-semibold text-gray-700 mb-2">Mensaje del cliente</h4>
                    <p class="border rounded p-3 text-sm text-gray-600">{{ $pedido->mensaje }}</p>
                </div>
            @endif

            <div class="mt-6">
                <button type="submit" class="border rounded px-4 py-2 bg-white hover:bg-gray-100">Actualizar</button>
                <a href="{{ route('adm.pedidos-historico') }}" class="border rounded px-4 py-2 bg-gray-200 hover:bg-gray-300 ml-2 inline-block">Cancelar</a>
            </div>
        </form>
    </div>
</div>

@endsection
