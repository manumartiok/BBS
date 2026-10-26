<?php

namespace App\Http\Controllers;

use App\Models\Canje;
use Illuminate\Http\Request;

class CanjeAdminController extends Controller
{
    public function index(Request $request)
    {
        $buscar = trim((string) $request->get('q', ''));
        $canjes = Canje::with(['usuario', 'items'])
            ->when($buscar !== '', function ($query) use ($buscar) {
                $query->where(function ($query) use ($buscar) {
                    if (ctype_digit($buscar)) {
                        $query->orWhere('id', (int) $buscar);
                    }
                    $query->orWhere('nombre', 'like', "%{$buscar}%")
                        ->orWhere('email', 'like', "%{$buscar}%")
                        ->orWhere('cuit', 'like', "%{$buscar}%")
                        ->orWhere('guia_transporte', 'like', "%{$buscar}%")
                        ->orWhereHas('usuario', fn ($usuario) => $usuario
                            ->where('usuario', 'like', "%{$buscar}%")
                            ->orWhere('empresa', 'like', "%{$buscar}%"))
                        ->orWhereHas('items', fn ($items) => $items
                            ->where('codigo', 'like', "%{$buscar}%")
                            ->orWhere('nombre', 'like', "%{$buscar}%"));
                });
            })
            ->latest()->paginate(15)->withQueryString();

        return view('content.admin.canjes-historico', compact('canjes'));
    }

    public function show(Canje $canje)
    {
        $canje->load(['usuario', 'items']);

        return view('content.admin.canjes-historico-editor', compact('canje'));
    }

    // Solo cambia el seguimiento: estado y guía de transporte
    public function update(Request $request)
    {
        $validated = $request->validate([
            'canje_id' => 'required|exists:canjes,id',
            'estado' => 'required|in:' . implode(',', array_keys(Canje::ESTADOS)),
            'guia_transporte' => 'nullable|string|max:100',
        ]);

        Canje::findOrFail($validated['canje_id'])->update([
            'estado' => $validated['estado'],
            'guia_transporte' => $validated['guia_transporte'] ?? null,
        ]);

        return redirect()->route('adm.canjes-historico-editor', $validated['canje_id'])
            ->with('success', 'Canje actualizado correctamente.');
    }

    public function destroy(Canje $canje)
    {
        $canje->delete();

        return redirect()->route('adm.canjes-historico')->with('success', 'Canje eliminado correctamente.');
    }

    // Avanza al siguiente estado y, tras el último, vuelve al primero
    public function switch(Canje $canje)
    {
        $estados = array_keys(Canje::ESTADOS);
        $actual = array_search($canje->estado, $estados, true);
        $canje->update(['estado' => $estados[$actual === false ? 0 : ($actual + 1) % count($estados)]]);

        return back()->with('success', 'Estado actualizado correctamente.');
    }
}
