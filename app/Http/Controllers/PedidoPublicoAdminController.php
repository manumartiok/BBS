<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\PedidoPublico;
use Illuminate\Http\Request;

// Histórico de pedidos de la zona pública (los de la zona privada los maneja PedidoController)
class PedidoPublicoAdminController extends Controller
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->get('q', ''));

        $pedidos = PedidoPublico::with(['usuario', 'items'])
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($sub) use ($buscar) {
                    if (ctype_digit($buscar)) {
                        $sub->orWhere('id', (int) $buscar);
                    }
                    $sub->orWhere('nombre', 'like', "%{$buscar}%")
                        ->orWhere('email', 'like', "%{$buscar}%")
                        ->orWhere('cuit', 'like', "%{$buscar}%")
                        ->orWhereHas('items', fn ($items) => $items
                            ->where('codigo', 'like', "%{$buscar}%")
                            ->orWhere('nombre', 'like', "%{$buscar}%"));
                });
            })
            ->latest()->paginate(15)->withQueryString();

        return view('content.admin.pedidos-publicos', compact('pedidos'));
    }

    public function show(PedidoPublico $pedido)
    {
        $pedido->load(['usuario', 'items']);

        return view('content.admin.pedidos-publicos-editor', compact('pedido'));
    }

    // Solo cambia el estado del pedido
    public function update(Request $request)
    {
        $validated = $request->validate([
            'pedido_id' => 'required|exists:pedidos_publicos,id',
            'estado' => 'required|in:' . implode(',', array_keys(Pedido::ESTADOS)),
        ]);

        PedidoPublico::findOrFail($validated['pedido_id'])->update(['estado' => $validated['estado']]);

        return redirect()->route('adm.pedidos-publicos-editor', $validated['pedido_id'])
            ->with('success', 'Pedido actualizado correctamente.');
    }

    public function destroy(PedidoPublico $pedido)
    {
        $pedido->delete();

        return redirect()->route('adm.pedidos-publicos')->with('success', 'Pedido eliminado correctamente.');
    }

    // Avanza al siguiente estado y, tras el último, vuelve al primero
    public function switch(PedidoPublico $pedido)
    {
        $estados = array_keys(Pedido::ESTADOS);
        $actual = array_search($pedido->estado, $estados, true);
        $pedido->update(['estado' => $estados[$actual === false ? 0 : ($actual + 1) % count($estados)]]);

        return back()->with('success', 'Estado actualizado correctamente.');
    }
}
