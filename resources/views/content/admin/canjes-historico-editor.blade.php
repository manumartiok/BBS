@extends('layouts.master')

@section('title', 'Editar canje')

@section('content')

<div>
    <h3 class="text-[20px] font-bold text-gray-500">Editar Canje</h3>
    <hr class="mx-6">
</div>

<div class="mx-20 pt-6" id="app">
    <div class="border rounded p-4 bg-white">
        @php($envio = \App\Models\Canje::ENVIOS[$canje->envio] ?? $canje->envio)
        <div>
            <h3 class="text-[18px] font-semibold text-gray-600">Canje #{{ str_pad($canje->id, 8, '0', STR_PAD_LEFT) }}</h3>
            <p class="text-sm text-gray-500 mt-1">
                {{ $canje->nombre }} - {{ $canje->email }}
                @if($canje->usuario?->usuario) (usuario: {{ $canje->usuario->usuario }}) @endif
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

        <form method="POST" action="{{ route('adm.canjes-historico-update') }}">
            @csrf
            <input type="hidden" name="canje_id" value="{{ $canje->id }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2" for="estado">Estado</label>
                    <select
                        id="estado"
                        name="estado"
                        class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        @foreach(\App\Models\Canje::ESTADOS as $valor => [$texto])
                            <option value="{{ $valor }}" @selected(old('estado', $canje->estado) === $valor)>{{ $texto }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-gray-700 font-medium mb-2" for="guia_transporte">N° guía de transporte</label>
                    <input
                        type="text"
                        id="guia_transporte"
                        name="guia_transporte"
                        maxlength="100"
                        value="{{ old('guia_transporte', $canje->guia_transporte) }}"
                        class="w-full border border-gray-300 rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 text-sm text-gray-600">
                <div class="border rounded p-4">
                    <span class="text-xs text-gray-400 uppercase">Datos de facturación</span>
                    <p class="mt-1"><strong>{{ $canje->nombre }}</strong></p>
                    <p>CUIT / CUIL: {{ $canje->cuit }}</p>
                    <p>{{ $canje->email }} - {{ $canje->celular }}</p>
                    <p>{{ $canje->direccion }}, {{ $canje->localidad }}, {{ $canje->provincia }} ({{ $canje->codigo_postal }})</p>
                </div>
                <div class="border rounded p-4">
                    <span class="text-xs text-gray-400 uppercase">Envío</span>
                    <p class="mt-1"><strong>{{ $envio }}</strong>@if($canje->envio_codigo_postal) (CP {{ $canje->envio_codigo_postal }})@endif</p>
                    @if($canje->entrega_otra)
                        <p class="mt-1">Entregar en: {{ $canje->entrega_otra['direccion'] ?? '' }}, {{ $canje->entrega_otra['localidad'] ?? '' }}, {{ $canje->entrega_otra['provincia'] ?? '' }} ({{ $canje->entrega_otra['codigo_postal'] ?? '' }})</p>
                    @else
                        <p class="mt-1">Misma dirección de facturación.</p>
                    @endif
                </div>
            </div>

            <div class="mt-6">
                <h4 class="font-semibold text-gray-700 mb-3">Productos canjeados</h4>
                <div class="overflow-x-auto border rounded">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-100 text-gray-700 text-sm uppercase">
                            <tr>
                                <th class="px-3 py-2 text-left">Codigo</th>
                                <th class="px-3 py-2 text-left">Producto</th>
                                <th class="px-3 py-2 text-right">Puntos</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600">
                            @foreach($canje->items as $item)
                                <tr class="border-t">
                                    <td class="px-3 py-2">{{ $item->codigo ?: '-' }}</td>
                                    <td class="px-3 py-2">{{ $item->nombre ?: '-' }}</td>
                                    <td class="px-3 py-2 text-right">{{ number_format($item->puntos, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                            <tr class="border-t bg-gray-50 font-semibold">
                                <td class="px-3 py-2" colspan="2">Total</td>
                                <td class="px-3 py-2 text-right">{{ number_format($canje->puntos, 0, ',', '.') }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            @if($canje->mensaje)
                <div class="mt-6">
                    <h4 class="font-semibold text-gray-700 mb-2">Mensaje del cliente</h4>
                    <p class="border rounded p-3 text-sm text-gray-600">{{ $canje->mensaje }}</p>
                </div>
            @endif

            <div class="mt-6">
                <button type="submit" class="border rounded px-4 py-2 bg-white hover:bg-gray-100">Actualizar</button>
                <a href="{{ route('adm.canjes-historico') }}" class="border rounded px-4 py-2 bg-gray-200 hover:bg-gray-300 ml-2 inline-block">Volver</a>
            </div>
        </form>
    </div>
</div>

@endsection
