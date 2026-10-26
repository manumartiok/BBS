@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')

   
   {{-- cabezal  --}}
    <div>
        <h3 class="text-[20px] font-bold text-gray-500">Home Banners</h3>
        <hr class="mx-6">
    </div>
    {{-- Editor --}}
   <div class="p-4">
        <div class="overflow-x-auto rounded-2xl shadow">
            <div class="flex justify-end px-6 py-3 border rounded">
                <a href="{{route('adm.home-ban-creador')}}" class="hover:text-gray-400 hover:scale-105 transition-transform duration-300"><i class="fa-solid fa-plus"></i></a>
            </div>
            <table class="min-w-full border border-gray-200 bg-white">
            <thead class="bg-gray-100">
                <tr class="text-gray-700 text-sm uppercase">
                <th class="px-4 py-3 text-left">Orden</th>
                <th class="px-4 py-3 text-left">Foto banner</th>
                {{-- <th class="px-4 py-3 text-left">Titulo</th> --}}
                <th class="px-4 py-3 text-center">Activo</th>
                <th class="px-4 py-3 text-center">Acciones</th>
                </tr>
            </thead>

            <tbody id="galeria-fotos" class="text-gray-600">
                @foreach($home_banner as $banner)
                <tr class="border-t hover:bg-gray-50 cursor-move" data-id="{{$banner->id}}">
                <td class="px-4 py-3">{{$banner->orden}}</td>
                <td class="px-4 py-3">
                    @if (!empty($banner->foto))
                            <div class="mb-3">
                                @if (($banner->tipo_medio ?? 'imagen') === 'video')
                                    <video class="w-full max-w-[300px] h-[200px] object-cover" controls>
                                        <source src="{{ asset($banner->foto) }}" type="video/mp4">
                                        Tu navegador no soporta el tag de video.
                                    </video>
                                @else
                                    <img src="{{ asset($banner->foto) }}" alt="Foto" class="w-full max-w-[300px] h-[200px] object-cover">
                                @endif
                            </div>
                        @endif
                </td>
                {{-- <td class="px-4 py-3">{{$banner->titulo}}</td> --}}
                <td class="px-4 py-3 text-center">
                    @if ($banner->active)
                        <a href="{{ route('adm.home-ban-switch', $banner->id) }}" class="badge bg-green-400 text-white px-4 py-2 rounded">
                            Activo
                        </a>
                    @else
                        <a href="{{ route('adm.home-ban-switch', $banner->id) }}" class="badge bg-red-400 text-white px-4 py-2 rounded">
                            Inactivo
                        </a>
                    @endif
                </td>
                <td class="px-4 py-3 text-center space-x-2 font-bold">
                    <a href="{{ route('adm.home-ban-editor', $banner->id) }}" class="text-blue-600 hover:underline">Editar</a>
                    <span>|</span>
                    <a href="{{ route('adm.home-ban-destroy', $banner->id) }}" class="text-red-600 hover:underline">Borrar</a>
                </td>
                </tr>
                @endforeach
            </tbody>
            </table>
        </div>
    </div>

            <script>
        document.addEventListener('DOMContentLoaded', function() {
            activarOrdenDragDrop('#galeria-fotos', '{{ route('adm.home-ban-reordenar') }}');
        });
</script>
@endsection