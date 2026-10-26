<?php

namespace App\Http\Controllers;

use App\Models\UsuarioPublico;
use Illuminate\Http\Request;

// Usuarios registrados en la zona pública (los de la zona privada son WebUsuario)
class UsuarioPublicoAdminController extends Controller
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->get('q', ''));

        $usuarios = UsuarioPublico::withCount('pedidos')
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($sub) use ($buscar) {
                    $sub->where('nombre', 'like', "%{$buscar}%")
                        ->orWhere('email', 'like', "%{$buscar}%")
                        ->orWhere('cuit', 'like', "%{$buscar}%")
                        ->orWhere('celular', 'like', "%{$buscar}%")
                        ->orWhere('localidad', 'like', "%{$buscar}%")
                        ->orWhere('provincia', 'like', "%{$buscar}%")
                        ->orWhere('codigo_postal', 'like', "%{$buscar}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        return view('content.admin.usuarios-publicos', compact('usuarios'));
    }
}
