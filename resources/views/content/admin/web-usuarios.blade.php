@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')

{{-- cabezal  --}}
<div class="flex items-center justify-between px-4 pt-4 pb-2">
    <div>
        <h3 class="text-[20px] font-bold text-gray-500">Usuarios</h3>
    </div>
</div>
<hr class="mx-6 mb-2">

{{-- Tabla --}}
<div class="p-4">
    <div class="overflow-x-auto rounded-2xl shadow">

        {{-- Buscador --}}
        <div class="flex items-center px-6 py-3 border rounded gap-4 bg-white">
            <form method="GET" action="{{ route('adm.web-usuarios') }}" class="flex items-center gap-2 flex-1">
                <input
                    type="text"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Buscar por usuario, email, razón social, localidad, provincia, CUIT, país, CP..."
                    class="w-full max-w-xl border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                >
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-1.5 rounded-lg transition">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                @if(request('q'))
                    <a href="{{ route('adm.web-usuarios') }}"
                       class="text-sm text-gray-500 hover:text-red-500 transition">
                        <i class="fa-solid fa-xmark"></i> Limpiar
                    </a>
                @endif
            </form>
        </div>

        <table class="min-w-full border border-gray-200 bg-white">
            <thead class="bg-gray-100">
                <tr class="text-gray-700 text-sm uppercase">
                    <th class="px-4 py-3 text-left">Usuario</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Razón social</th>
                    <th class="px-4 py-3 text-left">Localidad</th>
                    <th class="px-4 py-3 text-left">Provincia</th>
                    <th class="px-4 py-3 text-left">Rol</th>
                    <th class="px-4 py-3 text-right">Puntos</th>
                    <th class="px-4 py-3 text-left">Descuento</th> 
                    <th class="px-4 py-3 text-center">Activo</th>
                    <th class="px-4 py-3 text-center">Acciones</th>
                </tr>
            </thead>

            <tbody class="text-gray-600">
                @foreach($usuarios as $item)
                <tr class="border-t hover:bg-gray-50">
                    <td class="px-4 py-3">{{ $item->usuario }}</td>
                    <td class="px-4 py-3">{{ $item->email }}</td>
                    <td class="px-4 py-3">{{ $item->razon_social ?: '—' }}</td>
                    <td class="px-4 py-3">{{ $item->localidad ?? '—' }}</td>
                    <td class="px-4 py-3">{{ $item->provincia ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @if($item->rol === 'distribuidor')
                            <span class="badge bg-blue-400 text-white px-3 py-1 rounded text-xs">Distribuidor</span>
                        @elseif($item->rol === 'gremio')
                            <span class="badge bg-purple-400 text-white px-3 py-1 rounded text-xs">Gremio</span>
                        @else
                            <span class="badge bg-gray-400 text-white px-3 py-1 rounded text-xs">Sin asignar</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">{{ number_format($item->puntos ?? 0, 0, ',', '.') }}</td>

                    <td class="px-4 py-3">
                        @php
                            $descuentos = [];
                            for ($i = 1; $i <= 5; $i++) {
                                if (!is_null($item->{'descuento'.$i}) && $item->{'descuento'.$i} !== '') {
                                    $descuentos[] = "D{$i} ({$item->{'descuento'.$i}}%)";
                                }
                            }
                        @endphp
                        @if(count($descuentos))
                            <div class="flex flex-wrap gap-1">
                                @foreach($descuentos as $desc)
                                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-semibold">{{ $desc }}</span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td> 
                    <td class="px-4 py-3 text-center">
                        @if(Auth::user()->rol === 'Administrador')
                            <a href="{{ route('adm.web-usuarios-switch', $item->id) }}"
                               class="badge js-toggle-active {{ $item->active ? 'bg-green-400' : 'bg-red-400' }} text-white px-4 py-2 rounded"
                               data-user-id="{{ $item->id }}">
                                {{ $item->active ? 'Activo' : 'Inactivo' }}
                            </a>
                        @else
                            <span class="badge {{ $item->active ? 'bg-green-400' : 'bg-red-400' }} text-white px-4 py-2 rounded">
                                {{ $item->active ? 'Activo' : 'Inactivo' }}
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center space-x-2 font-bold">
                        @if(Auth::user()->rol === 'Administrador')
                            <a href="{{ route('adm.web-usuarios-editor', $item->id) }}" class="text-blue-600 hover:underline">Editar</a>
                            <span>|</span>
                            <a href="{{ route('adm.web-usuarios-destroy', $item->id) }}" class="text-red-600 hover:underline js-delete-user" data-user-id="{{ $item->id }}">Borrar</a>
                        @else
                            <span class="text-gray-400">Sin permisos</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Paginado --}}
        @if($usuarios->hasPages())
            <div class="flex items-center justify-between px-6 py-3 border-t bg-white text-sm text-gray-600">
                <span>
                    Mostrando {{ $usuarios->firstItem() }}–{{ $usuarios->lastItem() }}
                    de {{ $usuarios->total() }} usuarios
                </span>
                <div class="flex gap-1">
                    @if($usuarios->onFirstPage())
                        <span class="px-3 py-1 rounded border border-gray-200 text-gray-300">‹</span>
                    @else
                        <a href="{{ $usuarios->previousPageUrl() }}"
                           class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition">‹</a>
                    @endif

                    @foreach($usuarios->getUrlRange(max(1, $usuarios->currentPage()-2), min($usuarios->lastPage(), $usuarios->currentPage()+2)) as $page => $url)
                        @if($page == $usuarios->currentPage())
                            <span class="px-3 py-1 rounded bg-blue-600 text-white border border-blue-600">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}"
                               class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if($usuarios->hasMorePages())
                        <a href="{{ $usuarios->nextPageUrl() }}"
                           class="px-3 py-1 rounded border border-gray-300 hover:bg-gray-100 transition">›</a>
                    @else
                        <span class="px-3 py-1 rounded border border-gray-200 text-gray-300">›</span>
                    @endif
                </div>
            </div>
        @endif

    </div>
</div>

@endsection

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const csrf = '{{ csrf_token() }}';

        document.querySelectorAll('.js-toggle-active').forEach(function(el){
            el.addEventListener('click', async function(e){
                e.preventDefault();
                const url = el.href;
                el.classList.add('opacity-50', 'pointer-events-none');
                try{
                    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf } });
                    if(!res.ok) throw new Error('Network response was not ok');

                    // Optimistically toggle UI (backend toggle assumed successful)
                    const current = el.textContent.trim();
                    const nowActive = current !== 'Activo';
                    el.textContent = nowActive ? 'Activo' : 'Inactivo';
                    el.classList.toggle('bg-green-400', nowActive);
                    el.classList.toggle('bg-red-400', !nowActive);
                }catch(err){
                    console.error(err);
                    alert('Error al cambiar el estado.');
                }finally{
                    el.classList.remove('opacity-50', 'pointer-events-none');
                }
            });
        });

        document.querySelectorAll('.js-delete-user').forEach(function(el){
            el.addEventListener('click', async function(e){
                e.preventDefault();
                if(!confirm('¿Confirma que desea borrar este usuario?')) return;
                const url = el.href;
                const row = el.closest('tr');
                el.classList.add('opacity-50', 'pointer-events-none');
                try{
                    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf } });
                    if(!res.ok) throw new Error('Network response was not ok');
                    // Remove row from table
                    if(row) row.remove();
                }catch(err){
                    console.error(err);
                    alert('Error al borrar el usuario.');
                }
            });
        });
    });
    </script>
    @endpush