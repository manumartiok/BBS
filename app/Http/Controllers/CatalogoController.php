<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\GestionaArchivos;
use App\Models\Catalogo;
use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    use GestionaArchivos;

    public function index()
    {
        $items = Catalogo::orderBy('orden')->get();

        return view('content.admin.catalogo', [
            'items' => $items,
        ]);
    }

    public function create()
    {
        return view('content.admin.catalogo-creador', [
            'item' => new Catalogo(),
        ]);
    }

    public function store(Request $request)
    {
        $this->validar($request);

        $item = new Catalogo();
        $this->completar($item, $request);
        $item->save();

        return redirect()->route('adm.catalogo')
            ->with('success', 'Registro creado correctamente.');
    }

    public function show($id)
    {
        return view('content.admin.catalogo-editor', [
            'item' => Catalogo::findOrFail($id),
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validar($request);

        $item = Catalogo::findOrFail($id);
        $this->completar($item, $request);
        $item->save();

        return redirect()->route('adm.catalogo')
            ->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy($id)
    {
        $item = Catalogo::findOrFail($id);
        $this->borrarArchivosDe($item, ['foto', 'archivo'], []);
        $item->delete();

        return redirect()->route('adm.catalogo')
            ->with('success', 'Registro eliminado correctamente.');
    }

    public function switch($id)
    {
        $item = Catalogo::findOrFail($id);
        $item->active = !$item->active;
        $item->save();

        return redirect()->route('adm.catalogo')
            ->with('success', 'Estado actualizado correctamente.');
    }

    public function reordenar(Request $request)
    {
        foreach ($request->orden as $fila) {
            Catalogo::where('id', $fila['id'])->update(['orden' => $fila['orden']]);
        }

        return response()->json(['success' => true]);
    }

    private function validar(Request $request): void
    {
        $request->validate([
            'foto' => 'nullable|image|max:2048',
            'archivo' => 'nullable|file|max:10240',
        ]);
    }

    private function completar(Catalogo $item, Request $request): void
    {
        $item->orden = $request->input('orden');
        $item->nombre = $request->input('nombre');
        $item->foto = $this->procesarArchivo($request, 'foto', $item->foto, 'catalogos');
        $item->archivo = $this->procesarArchivo($request, 'archivo', $item->archivo, 'catalogos');
    }
}
