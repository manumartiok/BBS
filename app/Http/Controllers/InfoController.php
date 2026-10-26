<?php

namespace App\Http\Controllers;

use App\Models\Info;
use Illuminate\Http\Request;

class InfoController extends Controller
{
    /**
     * Mostrar la información (si no existe, crea una instancia vacía)
     */
    public function show($info_id = null)
    {
        $info = Info::first() ?? new Info();

        return view('content.admin.info', [
            'info' => $info
        ]);
    }

    /**
     * Crear o actualizar la información
     */
    public function update(Request $request)
    {
        // Validación solo de texto
        $request->validate([
            'texto1' => 'nullable|string',
        ]);

        // Obtener el primer registro o crear uno nuevo
        $info = Info::first() ?? new Info();

        // Asignar campos
        $info->texto1 = $request->input('texto1');

        // Guardar
        $info->save();

        return redirect()
            ->route('adm.info')
            ->with('success', 'Información actualizada correctamente.');
    }
}
