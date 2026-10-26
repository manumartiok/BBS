@extends('layouts.master')

@section('title', 'Ver presupuesto')

@section('content')

<div>
    <h3 class="text-[20px] font-bold text-gray-500">Ver Presupuesto</h3>
    <hr class="mx-6">
</div>

<div class="mx-20 pt-6" id="app">
    <div class="border rounded p-4 bg-white">
        @php
            $cliente = $presupuesto->usuario;
            $entregas = ['mercado_flex' => 'Mercado Flex', 'transporte_terminal' => 'Transporte a la terminal', 'retiro_local' => 'Retiro en local'];
            $formasPago = ['mercado_pago' => 'Mercado Pago', 'transferencia' => 'Transferencia bancaria', 'efectivo' => 'Efectivo (únicamente en local)'];
        @endphp
        <div>
            <h3 class="text-[18px] font-semibold text-gray-600">Presupuesto #{{ str_pad($presupuesto->id, 8, '0', STR_PAD_LEFT) }}</h3>
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

        <form method="POST" action="{{ route('adm.presupuestos-historico-update') }}">
            @csrf
            <input type="hidden" name="presupuesto_id" value="{{ $presupuesto->id }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2" for="fecha_vencimiento">Fecha de vencimiento</label>
                    <input
                        type="date"
                        id="fecha_vencimiento"
                        name="fecha_vencimiento"
                        required
                        value="{{ old('fecha_vencimiento', $presupuesto->fecha_vencimiento->format('Y-m-d')) }}"
                        class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <div>
                    <span class="block text-gray-700 font-medium mb-2">Estado</span>
                    <span class="badge {{ $presupuesto->estado === 'vencido' ? 'bg-red-500' : 'bg-green-500' }} text-white px-4 py-2 rounded inline-block">
                        {{ $presupuesto->estado_texto }}
                    </span>
                    <p class="text-xs text-gray-400 mt-1">Se calcula según la fecha de vencimiento.</p>
                </div>

                <div>
                    <span class="block text-gray-700 font-medium mb-1">Forma de entrega</span>
                    <p class="text-sm text-gray-600">{{ $entregas[$presupuesto->entrega] ?? ($presupuesto->entrega ?: '-') }}</p>
                </div>

                <div>
                    <span class="block text-gray-700 font-medium mb-1">Forma de pago</span>
                    <p class="text-sm text-gray-600">{{ $formasPago[$presupuesto->forma_pago] ?? ($presupuesto->forma_pago ?: '-') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                <div class="border rounded p-4">
                    <span class="text-xs text-gray-400 uppercase">Subtotal</span>
                    <p class="text-lg font-semibold">$ {{ number_format($presupuesto->subtotal, 2, ',', '.') }}</p>
                </div>
                <div class="border rounded p-4">
                    <span class="text-xs text-gray-400 uppercase">IVA</span>
                    <p class="text-lg font-semibold">$ {{ number_format($presupuesto->iva, 2, ',', '.') }}</p>
                </div>
                <div class="border rounded p-4">
                    <span class="text-xs text-gray-400 uppercase">Total</span>
                    <p class="text-lg font-semibold">$ {{ number_format($presupuesto->total, 2, ',', '.') }}</p>
                </div>
            </div>

            <div class="mt-6">
                <h4 class="font-semibold text-gray-700 mb-3">Items del presupuesto</h4>
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
                            @foreach($presupuesto->items as $item)
                                <tr class="border-t">
                                    <td class="px-3 py-2">{{ $item->codigo ?: '-' }}</td>
                                    <td class="px-3 py-2">{{ $item->nombre ?: '-' }}</td>
                                    <td class="px-3 py-2 text-right">{{ number_format($item->cantidad, 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-right">$ {{ number_format($item->precio_con_descuento, 2, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-right">$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            @if($presupuesto->mensaje)
                <div class="mt-6">
                    <h4 class="font-semibold text-gray-700 mb-2">Mensaje del cliente</h4>
                    <p class="border rounded p-3 text-sm text-gray-600">{{ $presupuesto->mensaje }}</p>
                </div>
            @endif

            <div class="mt-6">
                <button type="submit" class="border rounded px-4 py-2 bg-white hover:bg-gray-100">Actualizar</button>
                <a href="{{ route('adm.presupuestos-historico') }}" class="border rounded px-4 py-2 bg-gray-200 hover:bg-gray-300 ml-2 inline-block">Volver</a>
            </div>
        </form>
    </div>
</div>

@endsection
