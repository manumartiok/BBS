@php
    $novedades = App\Models\Novedade::where('active', true)
        ->orderBy('orden')
        ->get();
@endphp

@extends('layouts.web')

@section('title', 'Novedades')

@section('content')

<div class="w-full text-black" style="font-family: 'Montserrat', sans-serif;">
    <div class="max-w-[1366px] mx-auto px-[5%] md:px-[71px] pt-[15px] pb-[80px]">

        {{-- breadcrumb --}}
        <p class="text-[12px] leading-[15px] mb-[41px]"><a class="font-semibold" href="{{ route('home') }}">Inicio /</a> Novedades</p>

        {{-- novedades --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-[24px]">
            @foreach($novedades as $item)
                @include('content.web.partials.novedade-card', ['item' => $item])
            @endforeach
        </div>

    </div>
</div>

@endsection
