@php
    $marcas = $marcas ?? App\Models\Marca::where('active', true)->orderBy('orden')->get();
@endphp

@extends('layouts.web')

@section('title', $marca->nombre ?? 'Productos')

@section('content')

@include('content.web.partials.productos-listado', ['marcas' => $marcas, 'productos' => $productos, 'marca' => $marca, 'dolar' => $dolar ?? null])

@endsection
